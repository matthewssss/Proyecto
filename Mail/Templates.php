<?php
namespace Mailer;

class Templates {
    public static function getTemplate($type, $params = []) {
        switch ($type) {
            case 'bienvenida':
                $link = "https://gruposcout.online/inicio?modalOpen=true&token={$params['token']}&type=autologin&email={$params['correo']}";
                return [
                    'subject' => '🎉 ¡Bienvenido al Grupo Scout!',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <h1>¡Hola {$params['nombre']}!</h1>
                            <p>¡Qué alegría tenerte con nosotros!</p>
                            <p>Tu cuenta ha sido confirmada con éxito. Ahora puedes comenzar a disfrutar de todo lo que tenemos preparado para ti.</p>
                            <p>Para comenzar tu aventura, solo necesitas iniciar sesión con el siguiente enlace:</p>
                            <p style='text-align: center; margin: 20px 0;'>
                                <a href='{$link}' style='display: inline-block; padding: 12px 24px; background-color: #6a1b9a; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>
                                    🌟 Inicia sesión y comienza la aventura
                                </a>
                            </p>
                        </body>
                        </html>
                    "
                ];                    
            
            case 'doble_factor': //
                return [
                    'subject' => 'Tu código de verificación ',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>Hola {$params['nombre']},</p>
                            <p>Este es tu código para acceder:</p>
                            <div style='
                                text-align: center;
                                background-color: #e8f5e9;
                                padding: 20px;
                                border-radius: 12px;
                                margin: 30px auto;
                                width: fit-content;
                                font-family: monospace;
                            '>
                                <span style='
                                    font-size: 28px;
                                    font-weight: bold;
                                    text-decoration: underline;
                                    letter-spacing: 6px;
                                    color: #2e7d32;
                                '>
                                    {$params['codigo']}
                                </span>
                            </div>
                            <p><strong>Este código es válido solo durante 5 minutos.</strong></p>
                            <p>Si no lo usas en ese tiempo, deberás solicitar uno nuevo.</p>
                            <p>Si tú no pediste este código, puedes ignorar este mensaje sin problemas.</p>
                            <br>
                        </body>
                        </html>
                    "
                ]; 
            case 'inicio_sesion':
                $nombre = $params['nombre'];
                $rol = $params['rol'];
            
                if ($rol == 1) { // Padre/madre
                    $body = "
                        <html lang='es'>
                        <head><meta charset='UTF-8'></head>
                        <body>
                            <p>👋 ¡Hola de nuevo, {$nombre}!</p>
                            <p>Nos alegra verte por aquí 😊. Ya puedes acceder a tu panel familiar desde tu cuenta.</p>
                            <p>Ahí podrás ver registros, actividades y estar al tanto de todo lo que pasa en el grupo.</p>
                            <p>¡Gracias por seguir con nosotros en esta aventura scout!</p>
                            <br>
                        </body>
                        </html>
                    ";
                } elseif ($rol >= 2 && $rol <= 4) { // Monitor
                    $secretario = ($rol > 2) ? ' y preparar las próximas actividades' : '';
                    $body = "
                        <html lang='es'>
                        <head><meta charset='UTF-8'></head>
                        <body>
                            <p>🙌 ¡Hola de nuevo, {$nombre}!</p>
                            <p>Ya puedes entrar para consultar tu unidad, revisar a los chavales{$secretario}.</p>
                            <p>Gracias por tu entrega y por seguir creando magia en cada salida, reunión o campamento. ¡Seguimos sumando momentos inolvidables! 🌲</p>
                            <br>
                        </body>
                        </html>
                    ";
                } elseif ($rol == 5) { // Admin
                    $body = "
                        <html lang='es'>
                        <head><meta charset='UTF-8'></head>
                        <body>
                            <p>🤗 ¡Bienvenido de nuevo, {$nombre}!</p>
                            <p>Todo está listo. Desde tu cuenta puedes gestionar usuarios, unidades y supervisar todo el sistema.</p>
                            <p>Tu labor es clave para que todo funcione como debe. ¡Gracias por estar al pie del cañón! ⚙️</p>
                            <br>
                        </body>
                        </html>
                    ";
                } else {
                    $body = "
                        <html lang='es'>
                        <head><meta charset='UTF-8'></head>
                        <body>
                            <p>👋 ¡Hola {$nombre}!</p>
                            <p>Hemos registrado un nuevo inicio de sesión en tu cuenta.</p>
                            <p>Si no has sido tú, te recomendamos cambiar la contraseña lo antes posible.</p>
                            <br>
                        </body>
                        </html>
                    ";
                }
            
                return [
                    'subject' => 'Nuevo inicio de sesión detectado',
                    'body' => $body
                ];
                
            case 'recuperar_password':
                $link = "https://gruposcout.online/inicio?modalOpen=true&type=recover&token={$params['token']}&email={$params['correo']}";
                return [
                    'subject' => 'Código para restablecer tu contraseña',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <h1>Restablecimiento de contraseña</h1>
                            <p>Hola {$params['nombre']},</p>
                            <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                            <p>Puedes hacerlo usando este enlace:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$link}' style='display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>
                                    🔒 Restablecer contraseña
                                </a>
                            </div>
                            <p>Este enlace es válido solo por unos minutos. Si no has solicitado este cambio, puedes ignorar este mensaje.</p>
                            <br>
                        </body>
                        </html>
                    "
                ];
        
            case 'actualizacion_datos_admin'://
                return [
                    'subject' => 'Actualización de tus datos',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>🔧 ¡Hola {$params['nombre']}!</p>
                            <p>Un administrador ha realizado cambios en tu perfil del sistema.</p>
                        </body>
                        </html>
                    "
                ];
            case 'actualizacion_datos_admin_final'://
                return [
                    'body' => "
                            <p>Si algo no te cuadra o tienes dudas, contacta con nosotros sin problema. ¡Aquí estamos para ayudarte! 🤗</p>
                    "
                ];                
            case 'cambio_rol_rama': //TODO SOLO SI ES ROL > 1 (MONITORES PA LANTE) //Ready
                return [
                    'subject' => 'Tu cuenta Scout ha sido actualizada',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>📢 ¡Hola {$params['nombre']}!</p>
                            <p>Te informamos que un administrador ha actualizado tu cuenta en el sistema del Grupo Scout.</p>
                            <p><strong>Nueva asignación:</strong></p>
                            <ul>
                                <li>🔁 Rol: <strong>{$params['nuevo_rol']}</strong></li>
                                <li>🌿 Rama: <strong>{$params['rama']}</strong></li>
                            </ul>
                            <p>Estos cambios ya están activos. Si tienes alguna duda o algo no te cuadra, contacta con tu coordinador o el equipo de administración. 🤝</p>
                            <br>
                        </body>
                        </html>
                    "
                ];
                 
            case 'verifica_cuenta': //
                $link = "https://gruposcout.online/inicio?modalOpen=true&token={$params['token']}&type=verify&email={$params['correo']}";
                return [
                    'subject' => 'Confirma tu correo para empezar',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>📨 ¡Hola {$params['nombre']}!</p>
                            <p>Gracias por registrarte. Solo te queda un pasito más para empezar la aventura con nosotros.</p>
                            <p>Haz clic en el botón de abajo para verificar tu cuenta y acceder a tu panel:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='{$link}' style='display: inline-block; padding: 12px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>✅ Verificar mi cuenta</a>
                            </div>
                            <p>¡Te esperamos con los brazos abiertos! 🌟</p>
                            <br>
                        </body>
                        </html>
                    "
                ];
            case 'notificar_padre':
                return [
                    'subject' => '¡Nueva actividad disponible! 📣',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>📨 ¡Hola {$params['nombre']}!</p>
                            <p>¡Tenemos una nueva actividad para tu unidad <b>{$params['rama']}</b>!</p>
                            <p><strong>{$params['titulo']}</strong> se realizará desde <strong>{$params['fecha_inicio']}</strong> hasta <strong>{$params['fecha_fin']}</strong>.</p>
                            <p>📍 Ubicación: {$params['ubicacion']}</p>
                            <p>". ($params['pernoctas'] ? "¡Y además hay pernocta! 🎒" : "") ."</p>
                            <div style='text-align: center; margin: 30px 20px;'>
                                <a href='{$params['link']}' style='display: inline-block; padding: 12px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>📄 Ver Circular</a>
                            </div>
                            <p>Además, tienes el documento adjunto en este correo. 📎</p>
                        </body>
                        </html>
                    "
                ];
                
            case 'notificar_admin':
                return [
                    'subject' => 'Nueva circular enviada 📢',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <p>📨 Estimado {$params['nombre']},</p>
                            <p>Se ha subido y notificado una nueva circular para la unidad <b>{$params['rama']}</b>.</p>
                            <p><strong>{$params['titulo']}</strong> programada desde <strong>{$params['fecha_inicio']}</strong> hasta <strong>{$params['fecha_fin']}</strong>.</p>
                            <p>📍 Ubicación: {$params['ubicacion']}</p>
                            <p>Adjunto encontrarás la circular en formato PDF. 📎</p>
                        </body>
                        </html>
                    "
                ];
            
            case 'nueva_contraseña':
                return [
                    'subject' => 'Tu contraseña ha sido actualizada',
                    'body' => "
                        <html lang='es'>
                        <head>
                            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                            <meta http-equiv='Content-Language' content='es'>
                        </head>
                        <body>
                            <h1>Contraseña Actualizada</h1>
                            <p>Hola {$params['nombre']},</p>
                            <p>Queremos informarte que tu contraseña ha sido actualizada correctamente.</p>
                            <p>Si no has sido tú quien realizó este cambio, por favor contacta con nuestro soporte lo antes posible.</p>
                            <div style='
                                text-align: center;
                                background-color: #e8f5e9;
                                padding: 20px;
                                border-radius: 12px;
                                margin: 30px auto;
                                width: fit-content;
                                font-family: monospace;
                            '>
                                <span style='
                                    font-size: 28px;
                                    font-weight: bold;
                                    text-decoration: underline;
                                    letter-spacing: 6px;
                                    color: #2e7d32;
                                '>
                                    ¡Todo listo!
                                </span>
                            </div>
                            <p>Gracias por confiar en nosotros. Si tienes alguna pregunta, no dudes en contactarnos.</p>
                            <br>
                        </body>
                        </html>
                    "
                ];
            
            default:
                return ['subject' => 'Mensaje del Grupo Scout', 'body' => '<p>Contenido no disponible.</p>'];
        }
        
        
    }

    public static function getDefaultFooter() {
        return '
                <div style="font-family: Arial, sans-serif; font-size: 14px; color: #444; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="max-width: 70%;">
                        <p style="margin: 0 0 10px;">¡Un apretón de zurda! 🤝</p>
                        <p style="margin: 0 0 5px;"><strong>El equipo de kraal</strong></p>
                        <p style="margin: 0 0 10px;">Para cualquier duda, ponte en contacto con los coordinadores de tu unidad.</p>
                        <p style="margin: 0;"><a href="https://gruposcout.online/" style="color: #1a73e8;">Visita nuestra página web</a></p>
                    </div>
                    <div style="text-align: right;">
                        <img src="https://gruposcout.online/Public/images/Mails/saludo.png" alt="Logo Scout" width="60" style="margin-left: 20px;" />
                    </div>
                </div>
            </body>
            </html>
        ';
    }

}