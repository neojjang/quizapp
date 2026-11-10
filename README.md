
## QuizApp
A laravel based QuizApp

- Laravel 13, PHP 8.3 이상(개발·운영 기준 8.5), Livewire 3, Jetstream 5, Vite + Tailwind 3
- 업그레이드 내역과 남은 작업: `docs/upgrade/PLAN.md`

## 개발 환경 (Docker + SQLite)

로컬에 PHP/Composer를 설치하지 않고 컨테이너에서 실행한다. DB는 SQLite(`database/database.sqlite`)이며 `compose.dev.yml`의 환경 변수로 지정되므로 `.env`의 DB 설정은 바꾸지 않아도 된다.

```shell
docker compose -f compose.dev.yml build app
docker compose -f compose.dev.yml run --rm app composer install
docker compose -f compose.dev.yml run --rm app sh -c 'touch database/database.sqlite && php artisan migrate:fresh --seed'
docker compose -f compose.dev.yml up -d app                    # http://localhost:8000
docker compose -f compose.dev.yml run --rm app php artisan test

# 프론트엔드(빌드 결과 public/build 는 저장소에 커밋한다)
docker compose -f compose.dev.yml run --rm node npm ci
docker compose -f compose.dev.yml run --rm node npm run build
```

서술형(번역) 유사도 채점은 운영 서버의 Python 스크립트를 실행한다. 경로가 다르면 `.env`에 지정한다(기본값은 운영 경로).

```
ANSWER_CHECKER_PYTHON=/home/ubuntu/venv/bin/python3
ANSWER_CHECKER_SCRIPT=/home/ubuntu/dongwon/konlpy/check_answer.py
ANSWER_CHECKER_TIMEOUT=30
```


## Installation


```
git clone repo
cp .env.example .env
#Setup database 

#Seed will create 1 super-admin, 1 admin and initial quotes loaded to database, spatie initial roles and permissions.

php artisan migrate:fresh --seed

php artisan key:generate

```

```
Login with below users and create some Sections->Questions 

Username: superadmin@admin.com / admin@admin.com
Password: adminadmin
```


```
Register a new user and login -> Take a Quiz

```
## License

The QuizApp is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


## docker container 생성
$ docker build -t quizapp:latest . --no-cache

## docker container 실행
$ docker run -d -p 8800:8000 -v $(pwd):/app --name quizapp-dev quizapp:latest  php artisan serve --host=0.0.0.0
$ docker exec -it quizapp-dev /bin/bash

## docker-compose 실행
$ docker-compose up
$ docker-compose down
$ docker-compose stop



### 순서 나열 문제
- drag & drop 구현 : Livewire Sortable 
- 1번과 2번 중에서 2번을 사용
1. https://github.com/livewire/sortable?tab=readme-ov-file
- Livewire v3 : version=v1.x.x, Livewire v2 : version=0.2.2
```html
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.2.2/dist/livewire-sortable.js"></script>
```
2. https://github.com/nextapps-be/livewire-sortablejs
- Livewire V3 : version=0.4.x(현재 0.4.1 사용), Livewire v2 : version=0.2.0
```html
<script src="https://unpkg.com/@nextapps-be/livewire-sortablejs@0.4.1/dist/livewire-sortable.js"></script>
```

### 파일 업로드 관련 설정 
```shell

; nginx 설정에서 http or server or location 에 다음 추가/수정
client_max_body_size 128M;

; php.ini 파일에 다음 내용 수정 
upload_max_filesize = 128M
post_max_size = 128M

```

### S3 업로드시 public 권한 부여 
```php
; config/filesystems.php 에서 's3' 설정에 다음 추가 
's3' => [
...
    'visibility' => 'public',
...
]
    
; S3에 파일 업로드 함수에 ACL 설정 
Storage::disk('s3')->put($path, file_get_contents($file), [
    'ACL' => 'public-read',
    'Visibility' => 'public',
])    

```
