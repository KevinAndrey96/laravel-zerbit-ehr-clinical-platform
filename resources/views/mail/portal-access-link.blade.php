<x-mail-layout>
    <p>Hola, {{ $patient->first_name }}.</p>
    <p>Usa este botón para entrar al portal del paciente. El enlace vence en {{ $minutes }} minutos.</p>
    <p style="padding:8px 0"><a href="{{ $url }}" style="display:inline-block;background:#0e7490;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">Entrar al portal</a></p>
    <p>Si no pediste este enlace, ignora el mensaje: nadie podrá entrar sin acceso a tu correo.</p>
</x-mail-layout>
