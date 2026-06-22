<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/db.php';

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

function send_lead_notification(array $lead): bool
{
    if (!class_exists(PHPMailer::class)) {
        error_log('PHPMailer is not installed. Run: composer require phpmailer/phpmailer');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = env_value('SMTP_HOST', 'smtp.example.com');
        $mail->SMTPAuth = true;
        $mail->Username = env_value('SMTP_USERNAME', '');
        $mail->Password = env_value('SMTP_PASSWORD', '');
        $mail->SMTPSecure = env_value('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);
        $mail->Port = (int) env_value('SMTP_PORT', '587');

        $fromEmail = env_value('MAIL_FROM_ADDRESS', 'no-reply@example.com');
        $fromName = env_value('MAIL_FROM_NAME', 'TeamSource Scholarship');
        $adminEmail = env_value('ADMIN_NOTIFY_EMAIL', $fromEmail);

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($adminEmail);
        if (!empty($lead['email'])) {
            $mail->addReplyTo($lead['email'], $lead['full_name'] ?? 'Scholarship Applicant');
        }

        $mail->isHTML(true);
        $mail->Subject = 'New TeamSource Scholarship Application';
        $mail->Body = render_lead_email($lead);
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $mail->Body));

        return $mail->send();
    } catch (Exception $exception) {
        error_log('Lead notification failed: ' . $exception->getMessage());
        return false;
    }
}

function render_lead_email(array $lead): string
{
    $rows = [
        'Name' => $lead['full_name'] ?? '',
        'Email' => $lead['email'] ?? '',
        'Phone' => $lead['phone'] ?? '',
        'WhatsApp' => $lead['whatsapp'] ?? '',
        'Preferred Course' => $lead['preferred_course'] ?? '',
        'Message' => nl2br(htmlspecialchars($lead['message'] ?? '', ENT_QUOTES, 'UTF-8')),
        'Source' => $lead['source'] ?? '',
        'IP Address' => $lead['ip_address'] ?? '',
        'Timestamp' => $lead['created_at'] ?? date('Y-m-d H:i:s'),
    ];

    $htmlRows = '';
    foreach ($rows as $label => $value) {
        $safeValue = $label === 'Message' ? $value : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $htmlRows .= '<tr><th align="left" style="padding:10px;border-bottom:1px solid #e5e7eb;background:#f8fafc;width:180px;">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</th><td style="padding:10px;border-bottom:1px solid #e5e7eb;">' . $safeValue . '</td></tr>';
    }

    return '<div style="font-family:Arial,sans-serif;color:#111827;"><h2 style="margin:0 0 12px;">New Scholarship Application</h2><table cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:760px;border:1px solid #e5e7eb;">' . $htmlRows . '</table></div>';
}
