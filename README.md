
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


## 운영용 Docker 이미지 (PHP 8.5 FPM)
개발에는 위의 `compose.dev.yml`을 쓰고, `Dockerfile`은 운영 배포용이다.

```shell
docker build -t quizapp:latest .
# php-fpm(9000)이므로 nginx 등이 앞에 필요. .env는 이미지에 포함되지 않으므로(.dockerignore) 실행 시 넣는다
docker run -d --name quizapp --env-file .env quizapp:latest
docker exec quizapp sh -c 'php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache'
```
- nginx는 `root`를 `/app/public`으로 하고 `.php` 요청을 `컨테이너:9000`으로 넘긴다(정적 파일은 이미지의 `/app/public`을 공유하거나 복사).
- 업로드 한도(128M), OPcache, 시간대(Asia/Seoul)는 이미지에 설정되어 있다. nginx의 `client_max_body_size 128M`은 별도로 설정한다.
- 서술형 채점용 Python(konlpy)은 이미지에 없다. 컨테이너에서 채점하려면 `ANSWER_CHECKER_*` 경로에 맞게 마운트하거나 설치해야 한다.


## Git 원격 주소에서 토큰 제거

원격 주소에 GitHub 토큰이 들어 있으면 `.git/config`에 평문으로 저장되고, `git remote -v` 출력이나 로그에도 그대로 노출된다. 개발 PC와 운영 서버 모두 아래 순서로 정리한다.

### 1. 토큰이 들어 있는지 확인
```shell
git remote -v
# https://<사용자>:ghp_xxxx@github.com/neojjang/quizapp 처럼 ':'와 '@' 사이에 값이 있으면 토큰이 포함된 것
grep -nE 'ghp_|github_pat_' .git/config
```

### 2. 토큰 폐기 (가장 먼저)
- 주소에서 지워도 이미 노출된 토큰은 계속 유효하다.
- GitHub → Settings → Developer settings → Personal access tokens에서 해당 토큰을 **Revoke**한다.
- 필요하면 권한을 최소로 줄여 새로 발급한다.

### 3. 원격 주소 교체
```shell
# HTTPS (개발 PC)
git remote set-url origin https://github.com/neojjang/quizapp.git
# SSH (운영 서버 권장, 아래 5번의 Deploy key 사용)
git remote set-url origin git@github.com-quizapp:neojjang/quizapp.git

git remote -v                                   # 토큰이 없어졌는지 확인
grep -nE 'ghp_|github_pat_' .git/config || echo "토큰 없음"
```

### 4. 다른 곳에 남은 토큰 정리
```shell
cat ~/.git-credentials 2>/dev/null              # credential.helper store가 토큰을 평문으로 저장한 파일
git config --global --get-all credential.helper
grep -nE 'ghp_|github_pat_' ~/.bash_history ~/.zsh_history 2>/dev/null   # 명령 기록에 남은 토큰
```
- 위에서 찾은 줄은 삭제한다(`~/.git-credentials`는 파일째 삭제해도 된다).
- 같은 저장소를 받은 다른 폴더가 있으면 그 폴더도 1~3번을 반복한다. 예: 운영 서버의 이전 릴리스 폴더.

### 5. 앞으로의 인증 방법
**개발 PC (macOS):** 토큰을 주소에 넣지 않고 키체인이나 GitHub CLI로 인증한다.
```shell
gh auth login                                   # 또는
git config --global credential.helper osxkeychain
```

**운영 서버 (Ubuntu):** 이 저장소만 **읽을 수 있는** Deploy key(SSH 키)를 쓴다.
- 서버에 토큰이 남지 않는다.
- 키가 유출돼도 이 저장소를 읽는 것 외에는 할 수 없다.
```shell
ssh-keygen -t ed25519 -C "quizapp-deploy@$(hostname)" -f ~/.ssh/quizapp_deploy -N ""
cat ~/.ssh/quizapp_deploy.pub
# → GitHub 저장소 Settings → Deploy keys → Add deploy key 에 붙여넣기 ("Allow write access"는 체크하지 않음)

cat >> ~/.ssh/config <<'EOF'
Host github.com-quizapp
    HostName github.com
    User git
    IdentityFile ~/.ssh/quizapp_deploy
    IdentitiesOnly yes
EOF
chmod 600 ~/.ssh/config ~/.ssh/quizapp_deploy

ssh -T git@github.com-quizapp                   # "successfully authenticated" 확인 (셸 접근은 없다는 안내가 정상)
git remote set-url origin git@github.com-quizapp:neojjang/quizapp.git
git fetch origin                                # 토큰 없이 받아지는지 확인
```
**운영 서버 대안 1: Fine-grained 토큰 + `~/.git-credentials`**

Deploy key를 쓸 수 없을 때 사용한다. `~/.git-credentials`는 **암호화되지 않은 평문 파일**이므로, 유출되더라도 피해가 작도록 토큰의 범위를 좁혀서 발급한다.
- 원격 주소에 토큰을 넣는 방식과 달리, `git remote -v`·로그에 찍히지 않는다.
- 새로 받는 폴더의 `.git/config`로도 복사되지 않는다.

1. 토큰 발급: GitHub → Settings → Developer settings → Personal access tokens → **Fine-grained tokens** → Generate new token
   - Repository access: **Only select repositories** → `neojjang/quizapp`
   - Permissions → Repository permissions → **Contents: Read-only** (나머지는 No access)
   - Expiration: 기간을 정한다(예: 1년). 만료일을 배포 문서·일정에 기록한다.
2. 원격 주소에 토큰이 없는지 확인한다(위 3번의 HTTPS 주소).
   ```shell
   git remote set-url origin https://github.com/neojjang/quizapp.git
   ```
3. `~/.git-credentials`를 직접 작성한다. 토큰이 명령 기록(history)에 남지 않도록 화면에 보이지 않게 입력받는다.
   ```shell
   read -rp 'GitHub 사용자명: ' GH_USER
   read -rsp 'GitHub 토큰(입력 내용은 보이지 않음): ' GH_TOKEN; echo
   ( umask 077 && printf 'https://%s:%s@github.com\n' "$GH_USER" "$GH_TOKEN" > ~/.git-credentials )
   unset GH_TOKEN
   chmod 600 ~/.git-credentials
   ls -l ~/.git-credentials          # -rw------- 1 ubuntu ubuntu ...
   ```
   - 파일은 한 줄에 하나씩 `https://사용자명:토큰@github.com` 형식이다.
   - 같은 파일에 다른 호스트를 추가할 때는 `>` 대신 `>>`를 쓴다.
4. git이 이 파일을 쓰도록 설정하고 확인한다.
   ```shell
   git config --global credential.helper store
   git config --show-origin --get credential.helper   # file:/home/ubuntu/.gitconfig  store
   git fetch origin                                    # 사용자명·토큰을 묻지 않고 받아지면 정상
   ```
   - 파일을 직접 쓰지 않고, 4번 설정 후 `git fetch` 때 사용자명과 토큰을 한 번 입력해도 같은 파일이 만들어진다.
   - 이 경우에도 `chmod 600 ~/.git-credentials`를 확인한다.
5. 웹서버 계정이 이 파일을 읽을 수 없는지 확인한다.
   ```shell
   sudo -u www-data cat ~ubuntu/.git-credentials     # Permission denied 여야 함
   ```
6. 토큰 교체(만료 전): 새 토큰을 발급해 3번을 다시 실행하고 `git fetch origin`으로 확인한 뒤, GitHub에서 이전 토큰을 삭제한다.

**운영 서버 대안 2: 메모리에만 잠시 보관 (`cache`)**

배포가 가끔이고 매번 토큰을 입력해도 괜찮다면, 디스크에 아무것도 남기지 않는 방법이다.
```shell
git config --global credential.helper 'cache --timeout=3600'   # 입력한 토큰을 1시간 동안만 메모리에 보관
git fetch origin                                                # 배포할 때마다 사용자명·토큰 입력
git credential-cache exit                                       # 배포가 끝나면 즉시 지우려면
```
토큰은 대안 1과 같은 범위(이 저장소, Contents: Read-only)로 발급한다.

**권장 순서:**
1. Deploy key: 만료 관리가 필요 없고, 권한이 처음부터 이 저장소 읽기로 제한된다.
2. 대안 2(`cache`): 디스크에 남지 않는다.
3. 대안 1(`~/.git-credentials`): 범위를 좁힌 토큰으로만 쓴다.

어느 방법이든 **이미 노출된 토큰은 먼저 폐기**한다.

운영 배포 절차(`docs/upgrade/PLAN.md` P8-3-1)는 기존 운영 폴더의 원격 주소로 새 릴리스를 받는다. 그래서 **배포 전에 운영 폴더의 원격 주소를 먼저 정리**해야 새 폴더에 토큰이 따라가지 않는다.

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
