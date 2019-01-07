<?php

namespace Jyokyoku\AjaxMailer;

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

mb_internal_encoding("UTF-8");

if (!Request::isAjax()) {
    die;
}

session_start();

$mode = strtolower(Request::post('_mode', 'confirm'));
$configType = Request::post('_config', 'default');

$config = Config::getInstance($configType);
$form = Form::getInstance($configType);
$message = Message::getInstance($configType);

try {
    $config->load();
    $form->load();
    $message->setLocale($config->read('locale'));
    $message->load();

} catch (\Exception $e) {
    Response::jsonError('init_config', $e->getMessage());
}

$form->setMessage($message);

switch ($mode) {
    case 'token':
        Response::jsonSuccess(CsrfToken::generate());
        break;

    case 'confirm':
        $form->setData(Request::post());

        if (!$form->validate()) {
            Response::jsonError('validation_error', $form->getErrors());
        }

        Response::jsonSuccess($form->getValidated());
        break;

    case 'send':
        if ($config->read('csrf_token.status')) {
            $tokenField = $config->read('csrf_token.field');

            if ($tokenField && !CsrfToken::validate(Request::post($tokenField))) {
                Response::jsonError('invalid_token', 'Invalid token.');
            }
        }

        if ($config->read('cool_time.status')) {
            $cool_time = (int)$config->read('cool_time.seconds');

            if ($cool_time > 0 && !empty($_SESSION['send']) && $_SESSION['send'] + $cool_time > time()) {
                Response::jsonError('cool_time', 'Cool time now.');
            }
        }

        $form->setData(Request::post());

        if (!$form->validate()) {
            Response::jsonError('validation_error', $form->getErrors());
        }

        $_SESSION['send'] = time();

        $template = new Template($form->getValidated(), '[', ']');

        for ($i = 1; $i <= 2; $i++) {
            if ($config->read("response_{$i}.status")) {
                try {
                    $mailer = new PHPMailer();

                    if ($config->read('smtp.status')) {
                        $mailer->isSMTP();
                        $mailer->Host = $config->read('smtp.host');
                        $mailer->SMTPAuth = (bool)$config->read('smtp.smtp_auth');
                        $mailer->Username = $config->read('smtp.username');
                        $mailer->Password = $config->read('smtp.password');
                        $mailer->Port = $config->read('smtp.port');
                    }

                    $mailer->CharSet = PHPMailer::CHARSET_UTF8;
                    $mailer->Encoding = PHPMailer::ENCODING_BASE64;
                    $mailer->Subject = $template->text($config->read("response_{$i}.subject"));
                    $mailer->setFrom(
                        $template->text($config->read("response_{$i}.from.address")),
                        $template->text($config->read("response_{$i}.from.name"))
                    );
                    $mailer->addReplyTo(
                        $template->text($config->read("response_{$i}.from.address")),
                        $template->text($config->read("response_{$i}.from.name"))
                    );
                    $mailer->addAddress(
                        $template->text($config->read("response_{$i}.to.address")),
                        $template->text($config->read("response_{$i}.to.name"))
                    );

                    $mailer->Body = $template->file($config->read("response_{$i}.template"));

                    $mailer->send();

                } catch (\Exception $e) {
                    Response::jsonError('php_mailer', $e->getMessage());
                }
            }
        }

        Response::jsonSuccess($form->getValidated());
        break;

    default:
        Response::jsonError('invalid_mode', 'Invalid mode.');
}
