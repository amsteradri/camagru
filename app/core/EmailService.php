<?php

require_once __DIR__ . '/SMTPMailer.php';

class EmailService {
    
    public static function sendEmail($to, $subject, $htmlMessage) {
        if (!Config::isEmailEnabled()) {
            return false;
        }
        
        try {
            // Intentar envío con SMTP de Gmail
            $success = SMTPMailer::sendMail(
                $to,
                $subject, 
                $htmlMessage,
                Config::getFromEmail(),
                Config::getFromName(),
                Config::getSmtpHost(),
                Config::getSmtpPort(),
                Config::getSmtpUsername(),
                Config::getSmtpPassword()
            );
            
            if ($success) {
                error_log("Email sent successfully to {$to}");
                return true;
            } else {
                // Fallback: intentar con función mail() nativa
                error_log("SMTP failed, trying fallback mail() for {$to}");
                return self::sendWithNativeMail($to, $subject, $htmlMessage);
            }
            
        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            return self::sendWithNativeMail($to, $subject, $htmlMessage);
        }
    }
    
    private static function sendWithNativeMail($to, $subject, $htmlMessage) {
        $headers = [
            "From: " . Config::getFromName() . " <" . Config::getFromEmail() . ">",
            "Reply-To: " . Config::getFromEmail(),
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8"
        ];
        
        return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
    }
    
    public static function sendWithFallback($to, $subject, $htmlMessage) {
        // Intentar envío 
        if (self::sendEmail($to, $subject, $htmlMessage)) {
            return true;
        }
        
        // Si falla, log del problema
        error_log("All email methods failed for {$to}. Check SMTP configuration.");
        
        // En desarrollo, mostrar el contenido del email en los logs
        if (Config::isDevMode()) {
            error_log("Email content for {$to}: Subject: {$subject}");
            error_log("Body preview: " . substr(strip_tags($htmlMessage), 0, 200) . "...");
        }
        
        return false;
    }
}