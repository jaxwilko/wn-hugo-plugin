<?php

namespace JaxWilko\Hugo\Classes\Health;

use Carbon\Carbon;
use JaxWilko\Hugo\Classes\UserAgent;
use JaxWilko\Hugo\Models\HealthCheck;
use JaxWilko\Hugo\Models\Site;

class HealthChecker
{
    public static function run(Site $site)
    {
        $ch = curl_init($site->base_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CERTINFO => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => UserAgent::getRandom()
        ]);

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        return $site->healthChecks()->save(new HealthCheck([
            'status_code' => $info['http_code'],
            'primary_ip' => $info['primary_ip'],
            'http_version' => $info['http_version'],
            'protocol' => $info['protocol'],
            'content_length' => strlen($result),
            'size_download' => $info['size_download'],
            'total_time' => $info['total_time'],
            'ssl_serial_number' => $info['certinfo'][0]['Serial Number'] ?? 'null',
            'ssl_start_date' => Carbon::createFromTimeString($info['certinfo'][0]['Start date'] ?? '1970-01-01'),
            'ssl_expire_date' => Carbon::createFromTimeString($info['certinfo'][0]['Expire date'] ?? '1970-01-01'),
        ]));
    }
}
