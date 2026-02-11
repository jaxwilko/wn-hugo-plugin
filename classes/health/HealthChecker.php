<?php

namespace JaxWilko\Hugo\Classes\Health;

use Carbon\Carbon;
use JaxWilko\Hugo\Classes\UserAgent;
use JaxWilko\Hugo\Models\SiteDown;
use JaxWilko\Hugo\Models\Site;
use Symfony\Component\HttpFoundation\Response;

class HealthChecker
{
    public static function run(Site $site): ?SiteDown
    {
        // Check once, if fine return
        if (is_null(static::testUrl($site->base_url))) {
            return null;
        }

        // Check again, if fine return
        if (is_null($response = static::testUrl($site->base_url))) {
            return null;
        }

        // If the site has been down for 2 requests, then set it down
        return $site->downs()->save(new SiteDown([
            'status_code' => $response['info']['http_code'],
            'primary_ip' => $response['info']['primary_ip'],
            'http_version' => $response['info']['http_version'],
            'protocol' => $response['info']['protocol'],
            'content_length' => strlen($response['result']),
            'size_download' => $response['info']['size_download'],
            'total_time' => $response['info']['total_time'],
            'ssl_serial_number' => $response['info']['certinfo'][0]['Serial Number'] ?? 'null',
            'ssl_start_date' => Carbon::createFromTimeString($response['info']['certinfo'][0]['Start date'] ?? '1970-01-01 00:00:00'),
            'ssl_expire_date' => Carbon::createFromTimeString($response['info']['certinfo'][0]['Expire date'] ?? '1970-01-01 00:00:00'),
        ]));
    }

    protected static function testUrl(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CERTINFO => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => UserAgent::getRandom()
        ]);

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] === Response::HTTP_OK) {
            return null;
        }

        return [
            'result' => $result,
            'info' => $info
        ];
    }
}
