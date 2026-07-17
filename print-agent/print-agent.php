<?php

/**
 * Standalone on-site print agent.
 *
 * The booking app is hosted in the cloud; the thermal printer sits on a
 * private, on-site LAN that the cloud cannot reach directly. This script runs
 * on a PC on that same LAN, polls the cloud app for queued print jobs, and
 * delivers the raw ESC/POS bytes straight to the printer over the local
 * network (or a local/shared Windows printer). It has no Composer/vendor
 * dependency — only the PHP CLI itself — so it can be dropped onto a plain
 * Windows PC with minimal setup. See README.md for installation.
 */

declare(strict_types=1);

$configPath = __DIR__ . '/config.ini';
$logPath = __DIR__ . '/agent.log';

function log_line(string $path, string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($path, $line, FILE_APPEND);
    echo $line;
}

if (!file_exists($configPath)) {
    fwrite(STDERR, "Missing config.ini — copy config.example.ini to config.ini and fill it in.\n");
    exit(1);
}

$config = parse_ini_file($configPath);

$baseUrl = rtrim((string) ($config['CLOUD_BASE_URL'] ?? ''), '/');
$token = (string) ($config['API_TOKEN'] ?? '');
$pollInterval = max(2, (int) ($config['POLL_INTERVAL_SECONDS'] ?? 10));
$printMode = strtolower((string) ($config['PRINT_MODE'] ?? 'network'));
$printerHost = (string) ($config['PRINTER_HOST'] ?? '');
$printerPort = (int) ($config['PRINTER_PORT'] ?? 9100);
$printerName = (string) ($config['PRINTER_NAME'] ?? '');
$socketTimeout = max(1, (int) ($config['SOCKET_TIMEOUT_SECONDS'] ?? 5));

if ($baseUrl === '' || $token === '') {
    fwrite(STDERR, "config.ini is missing CLOUD_BASE_URL or API_TOKEN.\n");
    exit(1);
}

if (str_starts_with($baseUrl, 'https://') && !extension_loaded('openssl')) {
    fwrite(STDERR,
        "The \"openssl\" PHP extension is not enabled, so this PHP build cannot make HTTPS requests.\n" .
        "Fix: in the php/ folder, copy php.ini-production to php.ini (if you haven't already), open it,\n" .
        "and remove the leading \";\" from the line \"extension=openssl\". Then run run.bat again.\n"
    );
    exit(1);
}

/**
 * Deliver raw ESC/POS bytes to the printer over whatever local link this
 * on-site machine actually has to it — a plain TCP socket for a networked
 * printer, or a local/shared Windows printer otherwise.
 *
 * @throws RuntimeException on delivery failure
 */
function deliver_to_printer(string $bytes, string $mode, string $host, int $port, string $printerName, int $timeout): void
{
    if ($mode === 'network') {
        if ($host === '') {
            throw new RuntimeException('PRINT_MODE=network but PRINTER_HOST is not set.');
        }

        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($socket === false) {
            throw new RuntimeException("Could not connect to printer at {$host}:{$port} ({$errstr}).");
        }

        $written = fwrite($socket, $bytes);
        fclose($socket);

        if ($written === false || $written < strlen($bytes)) {
            throw new RuntimeException("Failed to write all bytes to {$host}:{$port}.");
        }

        return;
    }

    if ($mode === 'windows') {
        if ($printerName === '') {
            throw new RuntimeException('PRINT_MODE=windows but PRINTER_NAME is not set.');
        }

        // A COMx/LPTx local port can be written to directly.
        if (preg_match('/^(COM\d+|LPT\d+)$/i', $printerName) === 1) {
            if (file_put_contents($printerName, $bytes) === false) {
                throw new RuntimeException("Failed to write to local port {$printerName}.");
            }
            return;
        }

        // Otherwise treat it as a printer name shared on this same machine
        // (or an explicit \\host\printer UNC path) and copy a temp file to
        // it — the standard trick for pushing raw bytes to a Windows print
        // queue without a driver in the loop.
        $device = str_starts_with($printerName, '\\\\')
            ? $printerName
            : '\\\\localhost\\' . $printerName;

        $tmpFile = tempnam(sys_get_temp_dir(), 'escpos');
        if ($tmpFile === false) {
            throw new RuntimeException('Failed to create a temp file for printing.');
        }

        file_put_contents($tmpFile, $bytes);
        $ok = @copy($tmpFile, $device);
        @unlink($tmpFile);

        if (!$ok) {
            throw new RuntimeException("Failed to copy print data to {$device}.");
        }

        return;
    }

    throw new RuntimeException("Unknown PRINT_MODE '{$mode}' — use 'network' or 'windows'.");
}

/**
 * Prefers curl when it's enabled — it's far more reliable and gives useful
 * error messages (e.g. "Connection refused") — and falls back to PHP's
 * built-in http:// stream wrapper otherwise, whose Windows error reporting
 * for this case is frustratingly generic ("HTTP request failed!" with no
 * further detail, even on an outright connection failure).
 */
function http_request(string $method, string $url, string $token, ?array $jsonBody = null): array
{
    $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    $content = $jsonBody !== null ? json_encode($jsonBody) : null;

    if ($jsonBody !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    if (extension_loaded('curl')) {
        return http_request_curl($method, $url, $headers, $content);
    }

    return http_request_stream($method, $url, $headers, $content);
}

function http_request_curl(string $method, string $url, array $headers, ?string $content): array
{
    $ch = curl_init($url);

    $options = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
    ];

    if ($content !== null) {
        $options[CURLOPT_POSTFIELDS] = $content;
    }

    curl_setopt_array($ch, $options);

    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($body === false) {
        throw new RuntimeException("HTTP request to {$url} failed: {$error}");
    }

    return [$status, $body];
}

function http_request_stream(string $method, string $url, array $headers, ?string $content): array
{
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $content,
            'timeout' => 15,
            // Read the response body even on a 4xx/5xx status instead of
            // file_get_contents() just returning false for it.
            'ignore_errors' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    if ($body === false) {
        $error = error_get_last();
        throw new RuntimeException("HTTP request to {$url} failed: " . ($error['message'] ?? 'unknown error'));
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches) === 1) {
            $status = (int) $matches[1];
        }
    }

    return [$status, $body];
}

log_line($logPath, "Print agent started. Polling {$baseUrl} every {$pollInterval}s (mode={$printMode}).");

while (true) {
    try {
        [$status, $body] = http_request('GET', "{$baseUrl}/print-agent/jobs/next", $token);

        if ($status === 204) {
            sleep($pollInterval);
            continue;
        }

        if ($status !== 200) {
            log_line($logPath, "Unexpected status {$status} polling for jobs: {$body}");
            sleep($pollInterval);
            continue;
        }

        $job = json_decode($body, true);
        if (!is_array($job) || !isset($job['id'], $job['payload_base64'])) {
            log_line($logPath, "Malformed job response: {$body}");
            sleep($pollInterval);
            continue;
        }

        $jobId = $job['id'];
        $bytes = base64_decode($job['payload_base64'], true);

        if ($bytes === false) {
            log_line($logPath, "Job {$jobId}: could not decode payload, acking as failed.");
            http_request('POST', "{$baseUrl}/print-agent/jobs/{$jobId}/ack", $token, [
                'status' => 'failed',
                'error' => 'Agent could not base64-decode the payload.',
            ]);
            continue;
        }

        try {
            deliver_to_printer($bytes, $printMode, $printerHost, $printerPort, $printerName, $socketTimeout);

            log_line($logPath, "Job {$jobId}: printed successfully.");
            http_request('POST', "{$baseUrl}/print-agent/jobs/{$jobId}/ack", $token, ['status' => 'printed']);
        } catch (Throwable $e) {
            log_line($logPath, "Job {$jobId}: print failed — {$e->getMessage()}");
            http_request('POST', "{$baseUrl}/print-agent/jobs/{$jobId}/ack", $token, [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }

        // Drain the queue immediately instead of waiting out the poll
        // interval, in case more jobs are pending.
    } catch (Throwable $e) {
        log_line($logPath, 'Poll failed: ' . $e->getMessage());
        sleep($pollInterval);
    }
}
