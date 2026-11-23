<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Patient;
use App\Models\ClinicalHistory;
use App\Models\ChRecord;
use App\Models\ChPsychotherapeuticAssessment;
use App\Models\ChEvolution;
use App\Models\LabSample;
use App\Models\PhysicalTherapy;
use App\Models\Appointment;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $therapistRole = Role::firstOrCreate(['name' => 'therapist', 'guard_name' => 'web']);
        $patientRole = Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);

        // 2. Medical Staff & Admin Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@zerbit.local'],
            [
                'document' => '1018456789',
                'name' => 'Dr. Alejandro Restrepo (Director Médico)',
                'role' => 'admin',
                'password' => Hash::make('***REMOVED***'),
                'signature' => $dummySignature,
            ]
        );
        $admin->syncRoles(['admin']);

        $doctor = User::firstOrCreate(
            ['email' => 'doctor@zerbit.local'],
            [
                'document' => '1020456123',
                'name' => 'Dra. Camila Torres (Médico General)',
                'role' => 'doctor',
                'password' => Hash::make('***REMOVED***'),
                'signature' => $dummySignature,
            ]
        );
        $doctor->syncRoles(['doctor']);

        $therapist = User::firstOrCreate(
            ['email' => 'terapeuta@zerbit.local'],
            [
                'document' => '1032789456',
                'name' => 'Lic. Sebastián Ramírez (Fisioterapeuta)',
                'role' => 'therapist',
                'password' => Hash::make('***REMOVED***'),
                'signature' => $dummySignature,
            ]
        );
        $therapist->syncRoles(['therapist']);

        // 3. Patients
        $patient1 = Patient::firstOrCreate(
            ['document' => '79845123'],
            [
                'first_name' => 'Carlos',
                'second_name' => 'Eduardo',
                'first_surname' => 'Mendoza',
                'second_surname' => 'Gómez',
                'birthdate' => '1985-06-14',
                'civil_status' => 'Casado',
                'gender' => 'Masculino',
                'eps' => 'Sura EPS',
                'occupation' => 'Ingeniero de Software',
                'document_type' => 'CC',
                'email' => 'carlos.mendoza@paciente.local',
                'phone' => '3157894561',
                'address' => 'Carrera 15 # 93-45 Apto 502, Bogotá',
                'guardian' => 'Laura Gómez',
                'guardian_phone' => '3164567890',
                'guardian_relationship' => 'Cónyuge',
            ]
        );

        $patient2 = Patient::firstOrCreate(
            ['document' => '1023456789'],
            [
                'first_name' => 'Valentina',
                'second_name' => 'María',
                'first_surname' => 'Ortiz',
                'second_surname' => 'Duque',
                'birthdate' => '1992-11-20',
                'civil_status' => 'Soltera',
                'gender' => 'Femenino',
                'eps' => 'Sanitas EPS',
                'occupation' => 'Diseñadora Gráfica',
                'document_type' => 'CC',
                'email' => 'valentina.ortiz@paciente.local',
                'phone' => '3189876543',
                'address' => 'Calle 116 # 19-24, Bogotá',
                'guardian' => 'Gloria Duque',
                'guardian_phone' => '3101234567',
                'guardian_relationship' => 'Madre',
            ]
        );

        $patient3 = Patient::firstOrCreate(
            ['document' => '52987456'],
            [
                'first_name' => 'Adriana',
                'second_name' => 'Lucía',
                'first_surname' => 'Castro',
                'second_surname' => 'Peña',
                'birthdate' => '1978-03-08',
                'civil_status' => 'Unión Libre',
                'gender' => 'Femenino',
                'eps' => 'Compensar EPS',
                'occupation' => 'Docente Universitaria',
                'document_type' => 'CC',
                'email' => 'adriana.castro@paciente.local',
                'phone' => '3112345678',
                'address' => 'Diagonal 45 # 22-10, Bogotá',
                'guardian' => 'Mauricio Peña',
                'guardian_phone' => '3123456789',
                'guardian_relationship' => 'Hermano',
            ]
        );

        // 4. Clinical Histories (EHR)
        $ch1 = ClinicalHistory::firstOrCreate(
            ['patient_id' => $patient1->id, 'professional_id' => $admin->id],
            [
                'medical_diagnostic' => 'Lumbalgia mecánica subaguda (CIE-10: M54.5) con compromiso postural y tensión en fascia lumbosacra.',
                'physiotherapist_diagnostic' => 'Contractura de paravertebrales lumbares bilaterales, acortamiento de isquiotibiales grado II y limitación en flexión anterior del tronco.',
                'objective' => 'Reducir escala visual análoga (EVA) de dolor de 7/10 a 2/10, mejorar rango de movilidad articular lumbar y fortalecer core abdominal.',
                'sessions_number' => 10,
                'deductible_payment' => true,
                'payment_value' => 180000.0,
            ]
        );

        ChRecord::firstOrCreate(
            ['clinical_history_id' => $ch1->id],
            [
                'medical_pathological' => 'Hipertensión arterial controlada con Losartán 50mg/día.',
                'surgical' => 'Apendicectomía laparoscópica en 2012 sin complicaciones.',
                'traumatic' => 'Esguince de tobillo derecho grado I en 2018.',
                'allergy' => 'Alergia a AINEs (Dipirona / Metamizol).',
                'family' => 'Madre con antecedentes de diabetes mellitus tipo 2; padre con artrosis lumbar.',
                'pharmacological' => 'Losartán 50mg cada 24 horas.',
                'others' => 'Sedentarismo moderado debido a trabajo de oficina prolongado.',
            ]
        );

        ChPsychotherapeuticAssessment::firstOrCreate(
            ['clinical_history_id' => $ch1->id],
            [
                'pain' => 'Dolor tipo punzada en región lumbosacra que irradia levemente a glúteo derecho sin déficit sensitivo distal. EVA 7/10 al estar de pie.',
                'edema' => 'Ausente.',
                'joint_mobility' => 'Flexión de tronco limitada al 60% por espasmo doloroso. Extensión y rotaciones conservadas en un 80%.',
                'sensitivity' => 'Sensibilidad superficial táctil y dolorosa conservada en dermatomas L4, L5 y S1.',
                'integumentary_system' => 'Piel normocrómica, normotérmica, sin cicatrices quirúrgicas en región dorsal ni lumbar.',
                'muscular_strength' => 'Escala Daniels 4/5 en glúteo medio bilateral y musculatura paravertebral profunda.',
                'flexibility' => 'Signo de Wells positivo a -10 cm (retracción isquiotibial severa).',
                'posture' => 'Hiperlordosis lumbar compensatoria con anteversión pélvica.',
                'march' => 'Marcha rítmica e independiente, longitud de paso acortada durante fase de apoyo.',
                'balance' => 'Romberg negativo.',
                'falling_risk' => 'Bajo.',
                'other_valuations' => 'Tolerancia adecuada a termoterapia superficial y tracción suave.',
                'objective2' => 'Reintegro a actividad física recreativa sin exacerbación del cuadro álgico.',
            ]
        );

        ChEvolution::firstOrCreate(
            ['clinical_history_id' => $ch1->id, 'evolution_date' => Carbon::now()->subDays(5)->toDateString()],
            [
                'evolution_text' => 'Sesión 1/10: Se inicia protocolo de termoterapia profunda, liberación miofascial en región lumbar y elongación guiada de isquiotibiales. Paciente tolera adecuadamente el tratamiento.',
                'start_time' => '08:00',
                'end_time' => '08:45',
                'signature' => $dummySignature,
                'observation' => 'Se indican pautas de higiene postural para estación de trabajo.',
            ]
        );

        ChEvolution::firstOrCreate(
            ['clinical_history_id' => $ch1->id, 'evolution_date' => Carbon::now()->subDays(2)->toDateString()],
            [
                'evolution_text' => 'Sesión 2/10: Disminución de dolor a EVA 4/10. Se aplican corrientes TENS analgésicas durante 15 minutos y ejercicios de estabilización de core (método McGill).',
                'start_time' => '08:00',
                'end_time' => '08:45',
                'signature' => $dummySignature,
                'observation' => 'Excelente adherencia a los ejercicios pautados en casa.',
            ]
        );

        // 5. Clinical History 2
        $ch2 = ClinicalHistory::firstOrCreate(
            ['patient_id' => $patient2->id, 'professional_id' => $therapist->id],
            [
                'medical_diagnostic' => 'Tendinopatía del manguito rotador derecho (CIE-10: M75.1) con dolor a la abducción.',
                'physiotherapist_diagnostic' => 'Tendinitis supraespinoso derecho con restricción en rango final de elevación y debilidad en rotadores externos.',
                'objective' => 'Eliminar dolor en arcos de movimiento (EVA 0/10) y recuperar 180° de flexión y abducción activa de hombro.',
                'sessions_number' => 12,
                'deductible_payment' => true,
                'payment_value' => 220000.0,
            ]
        );

        ChRecord::firstOrCreate(
            ['clinical_history_id' => $ch2->id],
            [
                'medical_pathological' => 'Sin antecedentes patológicos de relevancia.',
                'surgical' => 'Ninguno.',
                'traumatic' => 'Contusión en hombro derecho durante práctica de tenis hace 2 meses.',
                'allergy' => 'Negativas.',
                'family' => 'No refiere.',
                'pharmacological' => 'Paracetamol 500mg ocasional para dolor.',
                'others' => 'Práctica deportiva amateur 3 veces por semana.',
            ]
        );

        ChPsychotherapeuticAssessment::firstOrCreate(
            ['clinical_history_id' => $ch2->id],
            [
                'pain' => 'Dolor punzante en cara anterolateral del hombro derecho en arco doloroso de 60° a 120°. EVA 6/10.',
                'edema' => 'No evidente.',
                'joint_mobility' => 'Abducción limitada a 110°, rotación interna dolorosa a partir de L3.',
                'sensitivity' => 'Conservada en dermatoma C5-C6.',
                'integumentary_system' => 'Normal.',
                'muscular_strength' => 'Daniels 3+/5 en supraespinoso e infraespinoso.',
                'flexibility' => 'Retracción de cápsula posterior de hombro derecho.',
                'posture' => 'Protracción escapular derecha con leve cifosis dorsal.',
                'march' => 'Conservada.',
                'balance' => 'Conservado.',
                'falling_risk' => 'Bajo.',
                'other_valuations' => 'Prueba de Neer y Hawkins positivas.',
                'objective2' => 'Reincorporación a entrenamiento deportivo sin molestia.',
            ]
        );

        // 6. Laboratory Samples
        LabSample::firstOrCreate(
            ['patient_id' => $patient1->id, 'sample_type' => 'Perfil Lipídico Completo & Glucemia'],
            [
                'authorized' => true,
                'email_delivery' => true,
                'delivery_date' => Carbon::now()->subDays(7)->toDateString(),
                'signature_date' => Carbon::now()->subDays(7)->toDateString(),
                'signed_by_himself' => true,
                'signature' => $dummySignature,
            ]
        );

        LabSample::firstOrCreate(
            ['patient_id' => $patient2->id, 'sample_type' => 'Hemograma Automatizado & Factor Reumatoideo'],
            [
                'authorized' => true,
                'email_delivery' => true,
                'delivery_date' => Carbon::now()->subDays(3)->toDateString(),
                'signature_date' => Carbon::now()->subDays(3)->toDateString(),
                'signed_by_himself' => true,
                'signature' => $dummySignature,
            ]
        );

        LabSample::firstOrCreate(
            ['patient_id' => $patient3->id, 'sample_type' => 'Uroanálisis Bioquímico & Cultivo'],
            [
                'authorized' => true,
                'email_delivery' => false,
                'delivery_date' => Carbon::now()->addDays(2)->toDateString(),
                'signature_date' => null,
                'signed_by_himself' => true,
                'signature' => null,
            ]
        );

        // 7. Physical Therapy Records
        PhysicalTherapy::firstOrCreate(
            ['patient_id' => $patient1->id, 'therapist_id' => $therapist->id],
            [
                'sessions_number' => 10,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'finish_date' => Carbon::now()->addDays(15)->toDateString(),
                'signed_by_himself' => true,
                'signature' => $dummySignature,
            ]
        );

        PhysicalTherapy::firstOrCreate(
            ['patient_id' => $patient2->id, 'therapist_id' => $therapist->id],
            [
                'sessions_number' => 12,
                'start_date' => Carbon::now()->subDays(4)->toDateString(),
                'finish_date' => Carbon::now()->addDays(20)->toDateString(),
                'signed_by_himself' => true,
                'signature' => $dummySignature,
            ]
        );

        // 8. Appointments
        Appointment::firstOrCreate(
            ['patient_id' => $patient1->id, 'datetime' => Carbon::now()->addDays(2)->setHour(9)->setMinute(0)],
            [
                'status' => 'scheduled',
                'comment' => 'Sesión 3/10 de rehabilitación lumbar y reevaluación biomecánica.',
            ]
        );

        Appointment::firstOrCreate(
            ['patient_id' => $patient2->id, 'datetime' => Carbon::now()->addDays(1)->setHour(14)->setMinute(30)],
            [
                'status' => 'scheduled',
                'comment' => 'Sesión de fisioterapia de hombro derecho y fortalecimiento excéntrico.',
            ]
        );

        Appointment::firstOrCreate(
            ['patient_id' => $patient3->id, 'datetime' => Carbon::now()->addDays(4)->setHour(11)->setMinute(0)],
            [
                'status' => 'scheduled',
                'comment' => 'Consulta de valoración clínica general y entrega de resultados de laboratorio.',
            ]
        );
    }
}
