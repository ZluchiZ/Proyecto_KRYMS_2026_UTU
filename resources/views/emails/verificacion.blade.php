<<<<<<< HEAD
<h2>Verificá tu correo electrónico</h2>

<p>Ingresá este código de 6 dígitos para confirmar tu cuenta:</p>

<p style="font-size: 32px; font-weight: bold; letter-spacing: 8px;">{{ $code }}</p>

<p>El código vence en 5 minutos. Si no solicitaste esta cuenta, podés ignorar este correo.</p>
=======
<h2>Verifica tu cuenta</h2>

<p>Haz clic en el botón para activar tu cuenta:</p>

<a href="{{ url('/verificar/'.$token) }}">
    Verificar cuenta
</a>
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
