
## QuizApp
A laravel based QuizApp


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
- Livewire V3 : version=0.4.0, Livewire v2 : version=0.2.0
```html
<script src="https://unpkg.com/@nextapps-be/livewire-sortablejs@0.2.0/dist/livewire-sortable.js"></script>
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
