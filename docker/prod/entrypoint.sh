#!/bin/sh
# 운영 app 컨테이너 시작 시 실행: 마운트된 .env와 환경 변수로 캐시를 만든 뒤 php-fpm을 띄운다.
# 로그·캐시 파일 소유자가 php-fpm과 같도록 www-data로 실행한다
set -e
su -s /bin/sh www-data -c 'php artisan config:cache && php artisan route:cache && php artisan view:cache'
exec "$@"
