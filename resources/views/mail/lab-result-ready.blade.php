<x-mail-layout>
    <p>Hola, {{ $sample->patient->first_name }}.</p>
    <p>El resultado de tu examen <strong>{{ $sample->exam }}</strong> (muestra {{ $sample->code }}) ya está disponible.</p>
    <p>Puedes descargarlo en el <a href="{{ route('portal.login') }}" style="color:#0e7490">portal del paciente</a> con tu documento y tu correo, o recogerlo en la clínica.</p>
    <p>Por tu privacidad, este correo no incluye el resultado.</p>
</x-mail-layout>
