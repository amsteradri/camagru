<?php
require_once __DIR__ . '/../app/config/Config.php';
require_once __DIR__ . '/../app/core/EmailService.php';

echo "🧪 Probando envío de email...\n\n";

$testEmail = 'amsteradri@gmail.com'; // Tu email para la prueba
$subject = 'Prueba de Camagru - Email de verificación';
$message = '
<html>
<body>
    <h2>¡Prueba exitosa!</h2>
    <p>Este es un email de prueba de tu proyecto Camagru.</p>
    <p>Si recibes este mensaje, la configuración SMTP está funcionando correctamente.</p>
    <p>✅ Configuración de Gmail: OK</p>
    <p>✅ Conexión SMTP: OK</p>
    <p>✅ Autenticación: OK</p>
    <hr>
    <small>Enviado desde el sistema Camagru - ' . date('Y-m-d H:i:s') . '</small>
</body>
</html>
';

echo "📧 Enviando email de prueba a: $testEmail\n";
echo "🔧 SMTP Host: " . Config::getSmtpHost() . ":" . Config::getSmtpPort() . "\n";
echo "👤 Usuario: " . Config::getSmtpUsername() . "\n";
echo "📤 Desde: " . Config::getFromEmail() . "\n\n";

$result = EmailService::sendWithFallback($testEmail, $subject, $message);

if ($result) {
    echo "✅ ¡Email enviado exitosamente!\n";
    echo "📬 Revisa tu bandeja de entrada (y spam por si acaso)\n";
} else {
    echo "❌ Error al enviar email\n";
    echo "🔍 Revisa los logs para más detalles\n";
}

echo "\n🏁 Prueba completada.\n";
?>