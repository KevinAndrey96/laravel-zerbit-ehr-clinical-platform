<x-mail-layout>
    <p>Hola, {{ $appointment->patient->first_name }}.</p>
    <p>Te recordamos tu cita de fisioterapia el <strong>{{ $fmt->longDate($appointment->starts_at) }}</strong> a las <strong>{{ $fmt->time($appointment->starts_at) }}</strong> con {{ $appointment->therapist->fullName() }}.</p>
    <p>Llega 10 minutos antes con tu documento y la autorización de tu aseguradora. Si no puedes asistir, avísanos para liberar el espacio.</p>
</x-mail-layout>
