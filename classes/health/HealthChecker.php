<?php

namespace JaxWilko\Hugo\Classes\Health;

use Carbon\Carbon;
use JaxWilko\Hugo\Classes\UserAgent;
use JaxWilko\Hugo\Models\Site;
use Symfony\Component\HttpFoundation\Response;

class HealthChecker
{
    public static function run(Site $site): ?array
    {
        // Check once, if fine return
        if (is_null(static::testUrl($site->base_url))) {
            return null;
        }

        // Check again, if fine return
        if (is_null($response = static::testUrl($site->base_url))) {
            return null;
        }

        return [
            'status_code' => $response['info']['http_code'],
            'primary_ip' => $response['info']['primary_ip'],
            'response_headers' => $response['header'],
            'response_body' => $response['body'],
            'certinfo' => $response['info']['certinfo'],
            'down_at' => Carbon::now(),
            'up_at' => null,
        ];
    }

    protected static function testUrl(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CERTINFO => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => UserAgent::getRandom()
        ]);

        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $info = curl_getinfo($ch);

        if ($info['http_code'] === Response::HTTP_OK) {
            return null;
        }

        return [
            'header' => $header,
            'body' => $body,
            'info' => $info
        ];
    }
}
