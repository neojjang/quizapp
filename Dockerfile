# 운영 배포용 이미지 (PHP 8.5 FPM + Laravel 13). 개발은 compose.dev.yml 사용
# Mac mini 운영은 compose.prod.yml이 두 단계(web, app)를 빌드한다 (docs/deploy/MACMINI.md)
# 단독 빌드: docker build -t quizapp:latest .   (마지막 단계인 app)
# 실행: php-fpm(9000 포트)이므로 nginx 등 웹서버가 앞에 필요하다. 환경 설정은 실행 시 .env 마운트 또는 환경 변수로 넣는다.

# nginx + 정적 파일(public). 프론트엔드 빌드 결과(public/build)는 저장소에 커밋되어 있다
FROM nginx:1.28-alpine AS web
COPY docker/prod/nginx.conf /etc/nginx/conf.d/default.conf
COPY public /app/public

FROM php:8.5-fpm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        unzip \
        python3 \
        python3-venv \
        default-jre-headless \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli zip bcmath gd intl exif \
    && rm -rf /var/lib/apt/lists/*

# 운영 설정: OPcache 사용, 듣기평가 mp3 업로드(최대 100MB)를 위한 업로드 한도 (README의 php.ini 설정과 동일)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && { \
        echo 'upload_max_filesize = 128M'; \
        echo 'post_max_size = 128M'; \
    } > "$PHP_INI_DIR/conf.d/quizapp.ini"

# 서술형 채점(konlpy)용 Python. 스크립트는 실행 시 /opt/konlpy에 마운트한다(services.answer_checker)
ENV JAVA_HOME=/usr/lib/jvm/default-java
COPY docker/prod/requirements.txt /opt/requirements.txt
RUN python3 -m venv /opt/venv && /opt/venv/bin/pip install --no-cache-dir -r /opt/requirements.txt

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV TZ=Asia/Seoul
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo "date.timezone = $TZ" > "$PHP_INI_DIR/conf.d/timezone.ini"

WORKDIR /app

# 의존성 먼저 설치해 소스만 바뀐 빌드는 캐시를 재사용
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# 프론트엔드 빌드 결과(public/build)는 저장소에 커밋되어 있어 Node가 필요 없다
COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

# config:cache는 빌드 시점의 환경 값을 고정하므로 컨테이너 시작 후 실행한다
COPY docker/prod/entrypoint.sh /usr/local/bin/quizapp-entrypoint
ENTRYPOINT ["quizapp-entrypoint"]
CMD ["php-fpm"]
