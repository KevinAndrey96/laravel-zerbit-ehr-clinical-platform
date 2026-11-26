#!/usr/bin/env bash
# Smoke test against a running stack (make up): staff login with CSRF,
# clinical history and PDF, role separation, and the patient portal reached
# through the emailed signed link captured by Mailpit.
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"
MAILPIT_URL="${MAILPIT_URL:-http://localhost:8025}"
DEMO_PASSWORD="${DEMO_PASSWORD:-$(grep ^DEMO_PASSWORD= .env | cut -d= -f2)}"
therapist="$(mktemp)"
front="$(mktemp)"
patient="$(mktemp)"
trap 'rm -f "$therapist" "$front" "$patient"' EXIT

fail() {
    echo "e2e: $1" >&2
    exit 1
}

expect() {
    local body="$1" text="$2"
    grep -qF -- "$text" <<<"$body" || fail "missing \"$text\""
}

token() {
    sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' <<<"$1" | head -n1
}

login() {
    local jar="$1" email="$2" page status
    page="$(curl -fsS -c "$jar" -b "$jar" "$BASE_URL/ingresar")"
    status="$(curl -sS -o /dev/null -w '%{http_code}' -c "$jar" -b "$jar" \
        --data-urlencode "_token=$(token "$page")" \
        --data-urlencode "email=$email" \
        --data-urlencode "password=$DEMO_PASSWORD" \
        "$BASE_URL/ingresar")"
    [ "$status" = "302" ] || fail "login of $email returned $status"
}

curl -fsS --retry 20 --retry-connrefused --retry-delay 2 "$BASE_URL/up" >/dev/null
expect "$(curl -fsS "$BASE_URL/")" "Tu tratamiento y tus resultados"

login "$therapist" "fisioterapia@zerbit.test"
panel="$(curl -fsS -b "$therapist" "$BASE_URL/panel")"
expect "$panel" "Mis tratamientos activos"
history="$(grep -o '/historias/[0-9]*' <<<"$panel" | head -n1)"
[ -n "$history" ] || fail "no open treatment on the therapist dashboard"

page="$(curl -fsS -b "$therapist" "$BASE_URL$history")"
expect "$page" "Evolución por sesión"
expect "$page" "data:image/png;base64,"

content_type="$(curl -fsS -o /dev/null -w '%{content_type}' -b "$therapist" "$BASE_URL$history/pdf")"
[ "$content_type" = "application/pdf" ] || fail "history pdf returned $content_type"

status="$(curl -sS -o /dev/null -w '%{http_code}' -b "$therapist" "$BASE_URL/admin/personal")"
[ "$status" = "403" ] || fail "therapist reached the staff admin ($status)"

login "$front" "recepcion@zerbit.test"
expect "$(curl -fsS -b "$front" "$BASE_URL/panel")" "Resultados por entregar"
status="$(curl -sS -o /dev/null -w '%{http_code}' -b "$front" "$BASE_URL$history")"
[ "$status" = "403" ] || fail "front desk read a clinical history ($status)"

curl -fsS -X DELETE "$MAILPIT_URL/api/v1/messages" >/dev/null
portal="$(curl -fsS -c "$patient" -b "$patient" "$BASE_URL/portal")"
curl -fsS -o /dev/null -c "$patient" -b "$patient" \
    --data-urlencode "_token=$(token "$portal")" \
    --data-urlencode "document_type=CC" \
    --data-urlencode "document_number=1012345678" \
    --data-urlencode "email=laura.gomez@paciente.test" \
    "$BASE_URL/portal/enlace"

link=""
for _ in $(seq 1 10); do
    id="$(curl -fsS "$MAILPIT_URL/api/v1/messages" | grep -o '"ID":"[^"]*"' | head -n1 | cut -d'"' -f4 || true)"
    if [ -n "$id" ]; then
        link="$(curl -fsS "$MAILPIT_URL/view/$id.html" | grep -o "$BASE_URL/portal/entrar/[^\"]*" | head -n1 | sed 's/&amp;/\&/g')"
        break
    fi
    sleep 1
done
[ -n "$link" ] || fail "no portal link was emailed"

home="$(curl -fsS -L -c "$patient" -b "$patient" "$link")"
expect "$home" "Hola, Laura"
expect "$home" "Descargar PDF"

echo "e2e: ok"
