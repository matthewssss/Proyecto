<?php

require_once "Templates.php";
require_once __DIR__ . '/../Vendor/Backend/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mailer\Templates;

// Usar Dotenv para cargar las variables de entorno
use Dotenv\Dotenv;

// Cargar .env desde el directorio Config
$dotenv = Dotenv::createImmutable(__DIR__ . '/../Config'); 
$dotenv->load();

class MailManager {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Encoding = 'base64';
        // Configuración SMTP
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['MAIL_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['MAIL_USERNAME'];
        $this->mail->Password = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $this->mail->Port = $_ENV['MAIL_PORT'];

        // Remitente
        $this->mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendMail($to, $subject, $body, $isHtml = true, $attachmentPath = null) {
        try {
            // Destinatario
            $this->mail->addBCC($to);
            // Adjuntar si se ha pasado un archivo
            if ($attachmentPath && file_exists($attachmentPath)) {
                $this->mail->addAttachment($attachmentPath);
            }

            // Contenido del correo
            $this->mail->isHTML($isHtml);
            // Generar un subject limpio y variable
            date_default_timezone_set('Europe/Madrid');
            $safeSubject = $subject . ' - ' . date('d/m/Y H:i');


            // Asignarlo
            $this->mail->Subject = $safeSubject;

            $this->mail->Body = $body;
            $this->mail->send();
    
            // Limpiar para próximos envíos
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            return false;
        } catch (Exception $e) {
            return true;
        }
    }
}

/* 
    Asuntos diferentes:
    Si el objetivo es enviar correos individuales y no crear una conversación, 
    asegúrate de que cada correo tenga un asunto único y no se repita con los demás. 
    Encabezados únicos:
    Los encabezados de correo electrónico contienen información como el "Message-ID", 
    que se usa para enlazar correos en una conversación. Si cada correo tiene un "Message-ID" único, 
    no se agruparán en una conversación. 
*/