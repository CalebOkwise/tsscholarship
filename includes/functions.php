<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/db.php';

const VALID_STATUSES = ['new', 'contacted', 'qualified', 'converted', 'rejected'];

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function limit_string(string $value, int $max): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function clean_string(?string $value, int $max = 255): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? '';
    return limit_string($value, $max);
}

function clean_text(?string $value, int $max = 5000): string
{
    $value = trim((string) $value);
    return limit_string($value, $max);
}

function redirect_to(string $path): never
{
    header('Location: ' . $path, true, 302);
    exit;
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    start_secure_session();
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function client_ip(): string
{
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        if (empty($_SERVER[$header])) {
            continue;
        }
        $value = explode(',', (string) $_SERVER[$header])[0];
        $ip = trim($value);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

function user_agent(): string
{
    return limit_string((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 1000);
}

function is_valid_status(string $status): bool
{
    return in_array($status, VALID_STATUSES, true);
}

function build_source(array $input): string
{
    $parts = [];
    foreach (['source', 'landing_page', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $key) {
        $value = clean_string($input[$key] ?? '', 120);
        if ($value !== '') {
            $parts[] = $key . ': ' . $value;
        }
    }
    return implode(' | ', $parts);
}

function is_rate_limited(PDO $pdo, string $ip): bool
{
    start_secure_session();
    $now = time();
    $lastSubmit = (int) ($_SESSION['last_lead_submit'] ?? 0);
    if ($lastSubmit > 0 && ($now - $lastSubmit) < 20) {
        return true;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM leads WHERE ip_address = :ip AND created_at >= (NOW() - INTERVAL 1 MINUTE)');
    $stmt->execute(['ip' => $ip]);
    return (int) $stmt->fetchColumn() >= 3;
}

function mark_submission_time(): void
{
    start_secure_session();
    $_SESSION['last_lead_submit'] = time();
}
