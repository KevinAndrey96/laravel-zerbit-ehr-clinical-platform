<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\CivilStatus;
use App\Enums\DeliveryChannel;
use App\Enums\DocumentType;
use App\Enums\FallRisk;
use App\Enums\LabStatus;
use App\Enums\Role;
use App\Enums\SampleType;
use App\Enums\Sex;
use App\Models\Appointment;
use App\Models\ClinicalHistory;
use App\Models\Insurer;
use App\Models\LabSample;
use App\Models\Patient;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo clinic with fictitious staff, patients, treatments, lab samples and
 * appointments. No real person or health record is involved.
 */
class DemoSeeder extends Seeder
{
    private const array PATIENTS = [
        ['CC', '1012345678', 'Laura', 'Valentina', 'Gómez', 'Ruiz', '1991-04-12', 'F', 'Diseñadora gráfica', 'laura.gomez@paciente.test'],
        ['CC', '79456123', 'Jorge', 'Enrique', 'Castro', 'Pardo', '1968-09-30', 'M', 'Conductor', 'jorge.castro@paciente.test'],
        ['CC', '52789456', 'Martha', 'Lucía', 'Herrera', 'Díaz', '1975-01-22', 'F', 'Docente', 'martha.herrera@paciente.test'],
        ['TI', '1098765432', 'Samuel', null, 'Ortiz', 'León', '2011-06-03', 'M', 'Estudiante', null],
        ['CC', '1032456789', 'Andrés', 'Felipe', 'Moreno', 'Silva', '1996-11-17', 'M', 'Futbolista aficionado', 'andres.moreno@paciente.test'],
        ['CE', '845612', 'Gabriela', null, 'Pérez', 'Rangel', '1988-02-08', 'F', 'Chef', 'gabriela.perez@paciente.test'],
        ['CC', '41234567', 'Rosa', 'Elena', 'Martínez', 'Vargas', '1952-07-25', 'F', 'Pensionada', 'rosa.martinez@paciente.test'],
        ['CC', '80123456', 'Camilo', null, 'Rodríguez', 'Mejía', '1983-03-14', 'M', 'Ingeniero de sistemas', 'camilo.rodriguez@paciente.test'],
    ];

    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        if (Patient::query()->exists()) {
            $this->command?->info('Ya hay pacientes: no se cargan datos demo.');

            return;
        }

        $password = config('demo.password');
        if (! is_string($password) || $password === '') {
            $password = Str::password(16, symbols: false);
            $this->command?->warn('DEMO_PASSWORD vacío. Contraseña generada para las cuentas demo: '.$password);
        }

        $admin = $this->staff('recepcion@zerbit.test', 'Diana', 'Salazar Rincón', '52000001', Role::Admin, null, $password);
        $paula = $this->staff('fisioterapia@zerbit.test', 'Paula Andrea', 'Cárdenas Mora', '1020000002', Role::Therapist, 'RETHUS-520314', $password);
        $julian = $this->staff('julian.rojas@zerbit.test', 'Julián', 'Rojas Beltrán', '1020000003', Role::Therapist, 'RETHUS-610982', $password);
        $paula->forceFill(['signature_path' => $this->signature(3)])->save();

        $insurers = Insurer::query()->orderBy('id')->pluck('id')->all();
        $patients = [];
        foreach (self::PATIENTS as $i => [$type, $number, $first, $second, $surname, $secondSurname, $birthdate, $sex, $occupation, $email]) {
            $patients[] = Patient::query()->create([
                'document_type' => DocumentType::from($type), 'document_number' => $number,
                'first_name' => $first, 'second_name' => $second, 'first_surname' => $surname, 'second_surname' => $secondSurname,
                'birthdate' => $birthdate, 'sex' => Sex::from($sex), 'civil_status' => CivilStatus::cases()[$i % 5],
                'insurer_id' => $i === 5 ? null : $insurers[$i % count($insurers)],
                'occupation' => $occupation, 'phone' => '310'.str_pad((string) (4_500_000 + $i * 7_919), 7, '0', STR_PAD_LEFT),
                'email' => $email, 'address' => 'Carrera '.(7 + $i * 3).' # '.(45 + $i).'-'.(10 + $i), 'city' => 'Bogotá',
            ]);
        }

        $today = CarbonImmutable::today();
        $this->history($patients[0], $paula, $today->subDays(18), 'M54.5', 'Lumbago no especificado',
            'Dolor lumbar mecánico que aumenta en sedestación prolongada frente al computador.', 10, 6, 7);
        $this->history($patients[1], $paula, $today->subDays(30), 'M75.1', 'Síndrome del manguito rotador',
            'Dolor en hombro derecho al elevar el brazo, limita su trabajo como conductor.', 12, 9, 8);
        $this->history($patients[4], $julian, $today->subDays(10), 'S83.5', 'Esguince del ligamento cruzado de la rodilla',
            'Posoperatorio de reconstrucción de ligamento cruzado anterior izquierdo, semana 6.', 20, 4, 6);
        $this->history($patients[6], $julian, $today->subDays(12), 'M17.1', 'Gonartrosis primaria bilateral',
            'Dolor y rigidez matutina en ambas rodillas, dificultad para subir escaleras.', 10, 3, 6, fallRisk: FallRisk::Moderate);
        $this->history($patients[2], $paula, $today->subDays(70), 'G56.0', 'Síndrome del túnel carpiano',
            'Parestesias nocturnas en mano derecha y debilidad para la pinza.', 8, 8, 6, closed: true);

        $this->samples($patients, $admin, $today);
        $this->appointments($patients, $paula, $julian, $today);

        $this->command?->info('Demo cargada: recepcion@zerbit.test y fisioterapia@zerbit.test.');
    }

    private function staff(string $email, string $name, string $lastName, string $document, Role $role, ?string $registry, string $password): User
    {
        return User::query()->create([
            'email' => $email, 'name' => $name, 'last_name' => $lastName, 'document' => $document, 'role' => $role,
            'professional_registry' => $registry, 'phone' => '3150000'.substr($document, -3), 'password' => $password, 'active' => true,
        ]);
    }

    private function history(
        Patient $patient, User $therapist, CarbonImmutable $opened, string $code, string $diagnosis, string $reason,
        int $authorized, int $held, int $pain, FallRisk $fallRisk = FallRisk::Low, bool $closed = false,
    ): void {
        $history = ClinicalHistory::query()->create([
            'patient_id' => $patient->id, 'therapist_id' => $therapist->id, 'opened_on' => $opened,
            'reason' => $reason, 'diagnosis_code' => $code, 'diagnosis' => $diagnosis,
            'physio_diagnosis' => 'Limitación funcional por dolor y disminución de la fuerza muscular en el segmento comprometido.',
            'objective' => 'Disminuir el dolor, recuperar arcos de movilidad y reintegrar al paciente a sus actividades diarias.',
            'authorized_sessions' => $authorized, 'authorization_number' => 'AUT-'.(240_000 + $patient->id * 137), 'copayment_cop' => 4_800,
            'background_pathological' => 'Niega', 'background_surgical' => $code === 'S83.5' ? 'Reconstrucción de LCA hace seis semanas.' : 'Niega',
            'background_pharmacological' => 'Acetaminofén 500 mg según dolor.', 'background_allergic' => 'Niega',
            'pain_scale' => $pain, 'pain_notes' => 'Dolor mecánico que cede con el reposo.',
            'joint_mobility' => 'Arcos de movilidad limitados por dolor al final del rango.', 'muscle_strength' => 'Fuerza 4/5 en el segmento comprometido.',
            'posture' => 'Leve anteriorización de cabeza y hombros.', 'gait' => 'Marcha independiente sin ayudas externas.',
            'fall_risk' => $fallRisk,
        ]);

        $notes = [
            'Valoración de control. Compresas calientes 15 minutos, movilización articular grado II y ejercicios isométricos. Tolera bien la sesión.',
            'Electroanalgesia TENS 20 minutos y estiramientos asistidos. Refiere menos dolor al despertar.',
            'Ejercicios de fortalecimiento con banda elástica, tres series de doce repeticiones. Se enseña plan casero.',
            'Progresión a ejercicios en cadena cinética cerrada. Buena adherencia al plan casero.',
            'Reeducación postural y control motor. El paciente reporta mejoría en sus actividades laborales.',
        ];
        $span = $closed ? 40 : (int) $opened->diffInDays(CarbonImmutable::today());
        $step = max(1, intdiv($span, max(1, $held)));
        for ($session = 1; $session <= $held; $session++) {
            $day = $opened->addDays(($session - 1) * $step)->min(CarbonImmutable::today());
            $start = 7 + ($session % 8);
            $evolution = $history->evolutions()->create([
                'therapist_id' => $therapist->id, 'session_number' => $session, 'attended_on' => $day,
                'starts_at' => sprintf('%02d:00', $start), 'ends_at' => sprintf('%02d:45', $start),
                'pain_scale' => max(1, $pain - intdiv($session * $pain, $held + 2)), 'note' => $notes[($session - 1) % count($notes)],
            ]);
            if ($session < $held || $closed) {
                $evolution->forceFill(['patient_signature_path' => $this->signature($patient->id + $session), 'signed_at' => $day->setTime($start, 50)])->save();
            }
        }

        if ($closed) {
            $history->forceFill([
                'closed_on' => $opened->addDays(42),
                'discharge_summary' => 'Paciente sin parestesias nocturnas, fuerza de pinza recuperada. Cumple los objetivos. Continúa con plan casero de estiramientos.',
            ])->save();
        }
    }

    /**
     * @param  list<Patient>  $patients
     */
    private function samples(array $patients, User $admin, CarbonImmutable $today): void
    {
        $rows = [
            [$patients[0], 'Cuadro hemático', SampleType::Blood, 6, LabStatus::Delivered],
            [$patients[0], 'Perfil lipídico', SampleType::Blood, 1, LabStatus::ResultReady],
            [$patients[1], 'Glicemia en ayunas', SampleType::Blood, 0, LabStatus::Registered],
            [$patients[2], 'Uroanálisis', SampleType::Urine, 1, LabStatus::InAnalysis],
            [$patients[6], 'Proteína C reactiva', SampleType::Blood, 2, LabStatus::ResultReady],
            [$patients[7], 'Coprológico', SampleType::Stool, 0, LabStatus::Registered],
        ];
        foreach ($rows as [$patient, $exam, $type, $daysAgo, $status]) {
            $sample = LabSample::query()->create([
                'code' => 'TMP-'.Str::random(8), 'patient_id' => $patient->id, 'registered_by' => $admin->id, 'sample_type' => $type,
                'exam' => $exam, 'taken_on' => $today->subDays($daysAgo), 'status' => $status, 'notify_by_email' => $patient->email !== null,
            ]);
            $sample->forceFill(['code' => sprintf('LAB-%s-%06d', $today->format('Y'), $sample->id)]);
            if ($status->hasResult()) {
                $sample->forceFill(['result_path' => $this->resultPdf($sample), 'result_ready_at' => $today->subDays($daysAgo)->setTime(16, 30)]);
            }
            if ($status === LabStatus::Delivered) {
                $sample->forceFill(['delivered_at' => $today->subDays(max(0, $daysAgo - 2))->setTime(10, 15), 'delivery_channel' => DeliveryChannel::Portal]);
            }
            $sample->save();
        }
    }

    /**
     * @param  list<Patient>  $patients
     */
    private function appointments(array $patients, User $paula, User $julian, CarbonImmutable $today): void
    {
        $workday = $today->isSunday() ? $today->addDay() : $today;
        $rows = [
            [$patients[0], $paula, $workday->setTime(8, 0), 45, AppointmentStatus::Confirmed],
            [$patients[1], $paula, $workday->setTime(9, 0), 45, AppointmentStatus::Scheduled],
            [$patients[7], $paula, $workday->setTime(10, 0), 60, AppointmentStatus::Scheduled],
            [$patients[4], $julian, $workday->setTime(7, 0), 60, AppointmentStatus::Attended],
            [$patients[6], $julian, $workday->setTime(11, 0), 45, AppointmentStatus::Confirmed],
            [$patients[3], $julian, $workday->setTime(15, 30), 30, AppointmentStatus::Scheduled],
            [$patients[5], $paula, $workday->subDay()->setTime(14, 0), 45, AppointmentStatus::NoShow],
            [$patients[0], $paula, $workday->addDays(2)->setTime(8, 0), 45, AppointmentStatus::Scheduled],
            [$patients[1], $paula, $workday->addDays(2)->setTime(9, 0), 45, AppointmentStatus::Scheduled],
        ];
        foreach ($rows as [$patient, $therapist, $start, $minutes, $status]) {
            Appointment::query()->create([
                'patient_id' => $patient->id, 'therapist_id' => $therapist->id, 'starts_at' => $start,
                'ends_at' => $start->addMinutes($minutes), 'status' => $status,
                'notes' => $patient->id === 8 ? 'Primera vez, trae orden médica.' : null,
            ]);
        }
    }

    /**
     * Draws a fictitious handwritten-looking signature.
     */
    private function signature(int $seed): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }
        $image = imagecreatetruecolor(360, 120);
        if ($image === false) {
            return null;
        }
        imagesavealpha($image, true);
        imagefill($image, 0, 0, (int) imagecolorallocatealpha($image, 255, 255, 255, 127));
        $ink = (int) imagecolorallocate($image, 20, 30, 60);
        imagesetthickness($image, 3);
        [$x, $y] = [20.0, 70.0];
        for ($t = 0; $t < 320; $t += 2) {
            $nextX = 20 + $t;
            $nextY = 70 + 28 * sin($t / (9 + $seed % 5)) * cos($t / (31 + $seed % 7)) - $t / 18;
            imageline($image, (int) $x, (int) $y, (int) $nextX, (int) $nextY, $ink);
            [$x, $y] = [$nextX, $nextY];
        }
        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        $path = 'signatures/'.Str::uuid()->toString().'.png';
        Storage::disk('local')->put($path, $png);

        return $path;
    }

    private function resultPdf(LabSample $sample): string
    {
        $path = 'lab-results/'.Str::uuid()->toString().'.pdf';
        $html = '<h1 style="font-family: DejaVu Sans; color: #126478">Resultado de laboratorio (demo)</h1>'
            .'<p style="font-family: DejaVu Sans">Muestra '.e($sample->code).' · '.e($sample->exam).'</p>'
            .'<p style="font-family: DejaVu Sans">Paciente ficticio: '.e($sample->patient->fullName()).'</p>'
            .'<p style="font-family: DejaVu Sans">Todos los valores dentro de los rangos de referencia.</p>';
        Storage::disk('local')->put($path, Pdf::loadHTML($html)->output());

        return $path;
    }
}
