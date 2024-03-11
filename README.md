## Todo
```
Currently busy with some other important things, will definately would like to imporove the app with
1. Multiple choices selection and mapping to detail results
2. Question difficulty levels
3. Section subscription for registered users
4. User crud functionality in admin pannel
5. Modal implementation for user crud.
6. Plenty of other functionalites ofcourese.



Pull reqeusts are welcome, acceptance will depend on availability of time to review the pull requests.

```

## User Quiz Home
![image](https://user-images.githubusercontent.com/52659978/130816735-6e881068-360d-4930-8d1b-333f9055719a.png)

## Start A Quiz
![image](https://user-images.githubusercontent.com/52659978/130816837-77995e62-a1c3-4f58-8f1a-d43f76fd8f69.png)

## Quiz Screen
![image](https://user-images.githubusercontent.com/52659978/130816969-3025d9bf-3960-4b1e-a404-03971ab62d58.png)


## Quiz Details
![image](https://user-images.githubusercontent.com/52659978/130817166-73e83d99-d2ae-4bcb-8f03-cfa2b7c11491.png)

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
