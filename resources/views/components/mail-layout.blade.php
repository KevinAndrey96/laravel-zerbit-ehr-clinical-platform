<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>{{ config('app.name') }}</title></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;padding:28px">
                <tr><td style="font-size:18px;font-weight:bold;color:#0e7490;padding-bottom:16px">{{ config('app.name') }}</td></tr>
                <tr><td style="font-size:15px;line-height:1.6">{{ $slot }}</td></tr>
                <tr><td style="font-size:12px;color:#64748b;padding-top:24px">Este mensaje es automático. Si no esperabas recibirlo, ignóralo o comunícate con la clínica.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
