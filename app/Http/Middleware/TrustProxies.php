<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     * 사설망·로컬에서 온 요청만 프록시로 신뢰한다(Mac mini: cloudflared → nginx 컨테이너).
     * 공인 IP에서 직접 들어온 요청의 X-Forwarded-* 헤더는 무시된다.
     *
     * @var array|string|null
     */
    protected $proxies = ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1'];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;
}
