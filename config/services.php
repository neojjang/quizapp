<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // 서술형(번역) 유사도 채점 Python 스크립트. 기본값은 기존 운영 서버 경로
    'answer_checker' => [
        'python' => env('ANSWER_CHECKER_PYTHON', '/home/ubuntu/venv/bin/python3'),
        'script' => env('ANSWER_CHECKER_SCRIPT', '/home/ubuntu/dongwon/konlpy/check_answer.py'),
        'timeout' => (int) env('ANSWER_CHECKER_TIMEOUT', 30), // 초. konlpy는 JVM 기동으로 첫 실행이 느림
    ],

];
