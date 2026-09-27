<?php

declare(strict_types=1);

if ($argc < 2 || in_array($argv[1], ['--help', '-h'], true)) {
    fwrite(STDOUT, "Usage: php scripts/benchmark_academic_dashboard.php <local-url> [requests] [concurrency]\n");
    fwrite(STDOUT, "Example: php scripts/benchmark_academic_dashboard.php http://127.0.0.1:8000/academic/dashboard 20 4\n");
    exit($argc < 2 ? 1 : 0);
}

$url = $argv[1];
$requests = max(1, min(200, (int) ($argv[2] ?? 20)));
$concurrency = max(1, min(20, (int) ($argv[3] ?? 4)));
$handles = [];
$startedAt = hrtime(true);
$completed = 0;
$errors = 0;
$statuses = [];
$latencies = [];

$multi = curl_multi_init();
$addHandle = static function () use (&$handles, $multi, $url): void {
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HEADER => false,
    ]);
    $handles[(int) $handle] = ['handle' => $handle, 'started' => hrtime(true)];
    curl_multi_add_handle($multi, $handle);
};

for ($i = 0; $i < min($requests, $concurrency); $i++) {
    $addHandle();
}

do {
    do {
        $result = curl_multi_exec($multi, $running);
    } while ($result === CURLM_CALL_MULTI_PERFORM);

    while ($info = curl_multi_info_read($multi)) {
        $handle = $info['handle'];
        $key = (int) $handle;
        $started = $handles[$key]['started'];
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $latencies[] = (hrtime(true) - $started) / 1_000_000;
        $statuses[$status] = ($statuses[$status] ?? 0) + 1;
        $completed++;
        if ($info['result'] !== CURLE_OK) {
            $errors++;
        }
        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
        unset($handles[$key]);
        if ($completed + count($handles) < $requests) {
            $addHandle();
        }
    }

    if ($running) {
        curl_multi_select($multi, 1.0);
    }
} while ($running || $handles !== []);

curl_multi_close($multi);
sort($latencies);
$elapsedMs = (hrtime(true) - $startedAt) / 1_000_000;
$p95 = $latencies === [] ? null : $latencies[(int) floor((count($latencies) - 1) * 0.95)];

fwrite(STDOUT, json_encode([
    'url' => $url,
    'requests' => $requests,
    'concurrency' => $concurrency,
    'completed' => $completed,
    'errors' => $errors,
    'statuses' => $statuses,
    'wall_ms' => round($elapsedMs, 2),
    'latency_ms' => [
        'min' => $latencies === [] ? null : round($latencies[0], 2),
        'p95' => $p95 === null ? null : round($p95, 2),
        'max' => $latencies === [] ? null : round($latencies[array_key_last($latencies)], 2),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
