# Mac mini 운영 배포 (Docker + Cloudflare Tunnel)

기존 Ubuntu 운영 서버를 Mac mini로 옮긴다(데이터 이전 포함). 결정 근거는 `docs/upgrade/PLAN.md`의 D10, 진행 상태는 PLAN.md의 P9.
Ubuntu 서버를 그대로 업그레이드하는 절차(P8)와는 별개다.

명령은 macOS(zsh)와 Ubuntu(bash) 양쪽에서 그대로 실행되는 형태로 썼다. `<...>`는 실제 값으로 바꾼다.

## 1. 구성

```
학생·관리자 ─HTTPS─> Cloudflare ─터널─> cloudflared ─HTTP─> web(nginx) ─FastCGI─> app(PHP 8.5 FPM + Python konlpy) ──> db(MySQL 또는 MariaDB)
                                     └──────────────── Mac mini의 Docker (compose 프로젝트 quizapp-prod) ────────────────┘
```

- cloudflared가 Mac mini에서 Cloudflare로 **바깥 방향 연결**을 만든다. 공유기 포트 개방, 공인 IP, 인증서 설정이 필요 없다.
- web 컨테이너는 Mac mini 자신에서만 `http://127.0.0.1:8080`으로 열린다(상태 확인용). production 모드는 모든 링크를 https로 만들기 때문에 이 주소로는 화면이 깨진다. 화면 확인은 터널 주소로 한다.
- DB 포트는 밖으로 열지 않는다.
- 데이터는 Docker 볼륨에 있다. `db-data`는 DB이고, `storage`는 로그·세션·캐시다. 이미지를 다시 빌드해도 유지된다. **`docker compose ... down -v`는 볼륨을 지우므로 쓰지 않는다.**
- 듣기평가 mp3는 지금처럼 S3에 있다.
- 작업 큐와 스케줄러는 쓰지 않는다(`QUEUE_CONNECTION=sync`, 스케줄 없음). 그래서 별도 worker 컨테이너가 없다.

| 파일 | 역할 |
|---|---|
| `compose.prod.yml` | 서비스 4개(app, web, db, cloudflared), 볼륨, 로그 순환 |
| `Dockerfile` | `web` 단계(nginx + `public`), `app` 단계(PHP 8.5 FPM, Python 3 + Java + konlpy) |
| `docker/prod/nginx.conf` | 업로드 128M, PHP를 app:9000으로 전달, 시간 제한 120초 |
| `docker/prod/entrypoint.sh` | app 시작 시 `config/route/view:cache` 생성(www-data) |
| `docker/prod/requirements.txt` | 채점 스크립트용 Python 패키지 |
| `docker/prod/backup.sh` | DB 백업(`backups/`, 14일 보관) |

## 2. 설정 검토 결과

### 2-1. 코드·설정 변경 (이 저장소에 반영됨)

| 항목 | 변경 | 이유 |
|---|---|---|
| `app/Http/Middleware/TrustProxies.php` | 사설망(10/8, 172.16/12, 192.168/16)·127.0.0.1을 프록시로 신뢰 | 요청이 cloudflared → nginx를 거쳐 오므로, 신뢰하지 않으면 모든 접속자의 IP가 컨테이너 IP로 보인다(로그인 시도 제한이 IP 단위로 묶임). 신뢰하면 https 판단과 secure 쿠키도 정상이 된다. 공인 IP에서 바로 들어온 요청의 헤더는 계속 무시한다 |
| `config/logging.php` | production에서 `LOG_SLACK_WEBHOOK_URL`이 없으면 slack 채널을 뺌 | 웹훅이 없으면 요청마다 "Unable to create configured logger" 비상 로그가 남는 것을 확인함 |
| `database/migrations/2022_03_09_134316_add_question_type.php` | 접두사 인덱스를 SQL로 생성. 이름은 기존과 같은 `questions_question(64)_index` | **빈 MySQL에 새로 설치하면 이 마이그레이션에서 실패**(Laravel 10부터 `index()`가 `DB::raw`를 받지 않음). 이미 실행된 기존 운영 DB에는 영향이 없다 |
| `Dockerfile`, `compose.prod.yml`, `docker/prod/*` | 새로 작성(1장) | Mac mini 운영 |
| `.dockerignore`, `.gitignore` | `konlpy/`, `backups/` 제외 | DB 덤프가 이미지나 저장소에 들어가지 않게 |

### 2-2. `.env` 변경 항목

기존 운영 서버의 `.env`를 복사해서 아래 항목만 바꾼다. compose는 같은 파일에서 `${DB_DATABASE}` 같은 값을 읽는다.

| 항목 | 값 | 설명 |
|---|---|---|
| `APP_KEY` | **기존 값 그대로** | 바꾸면 로그인 세션, 2단계 인증 비밀값 등 암호화된 데이터를 읽지 못한다 |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | |
| `APP_URL` | 리허설: `https://quiz-test.<도메인>`, 전환 후: `https://<운영 도메인>` | 바꾼 뒤에는 app을 다시 띄운다(4장) |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | 새 DB 컨테이너에 만들 DB·계정 | DB 볼륨이 **처음 만들어질 때만** 이 값으로 생성된다. `DB_USERNAME=root`는 쓸 수 없으니 전용 계정을 쓴다 |
| `DB_ROOT_PASSWORD` | 새로 추가 | DB 컨테이너의 root 비밀번호. 덤프 가져오기·백업에 쓴다 |
| `DB_IMAGE` | 기존이 MySQL이면 생략(기본 `mysql:8.4`), MariaDB면 `mariadb:11.4` | 3장 M1에서 확인한 종류에 맞춘다 |
| `CLOUDFLARE_TUNNEL_TOKEN` | 새로 추가 | 3장 M2에서 발급한다 |
| `SESSION_SECURE_COOKIE` | `true` | 세션 쿠키를 HTTPS에서만 보내게 한다 |
| `DB_HOST`, `DB_PORT`, `ANSWER_CHECKER_*` | 그대로 둬도 됨 | compose가 `db`, `3306`, 컨테이너 안 경로로 덮어쓴다 |
| `LOG_SLACK_WEBHOOK_URL`, `AWS_*`, `MAIL_*` | 기존 값 그대로 | S3(듣기평가), 메일(비밀번호 재설정), Slack 오류 알림 |

- 비밀번호에는 영문·숫자·`-`·`_`만 쓴다. `$`, 따옴표, `#`, 공백은 compose와 Laravel이 서로 다르게 해석할 수 있다.
- `.env`는 `chmod 600 .env`로 본인만 읽게 한다.

### 2-3. Cloudflare를 쓰면서 생기는 제약
- **업로드 크기**: Free·Pro 요금제는 요청 본문이 최대 100MB다. 앱의 mp3 한도도 100MB라서, 100MB에 가까운 파일은 Cloudflare에서 거부될 수 있다. 관리자에게 90MB 이하로 올리도록 안내한다.
- **응답 시간**: 100초 안에 응답이 없으면 524 오류가 난다. 가장 오래 걸리는 Python 채점의 제한이 30초라 문제없다.
- **도메인**: 터널의 공개 호스트 이름을 쓰려면 도메인의 DNS를 Cloudflare가 관리해야 한다(네임서버가 Cloudflare).

### 2-4. 기존 운영 DB 종류에 따른 이미지

| 기존 운영 DB | `DB_IMAGE` | 비고 |
|---|---|---|
| MySQL 8.0 / 8.4 | `mysql:8.4` (기본값) | 8.0은 2026년 4월 지원 종료. 8.0 덤프는 8.4에서 그대로 가져올 수 있다 |
| MySQL 5.7 | `mysql:8.4` | 리허설(M4)에서 가져오기 오류가 없는지 꼭 확인한다 |
| MariaDB 10.x / 11.x | `mariadb:11.4` | |

`mysql:8.4`(8.4.13)와 `mariadb:11.4`(11.4.13) 모두 개발 PC(arm64)에서 다음을 확인했다(2026-11-10). 빈 DB 마이그레이션, 시간대 `Asia/Seoul`, 백업, DB 재생성, 복원.

## 3. 배포 절차

| 단계 | 내용 | 서비스 영향 |
|---|---|---|
| M0 | Mac mini 준비 | 없음 |
| M1 | 기존 서버 정보 수집(읽기만) | 없음 |
| M2 | Cloudflare Tunnel 생성 | 없음 |
| M3 | 코드·설정 준비, 이미지 빌드 | 없음 |
| M4 | 데이터 이전 리허설 | 없음(기존 서버는 읽기만) |
| M5 | 공지 | 없음 |
| M6 | 전환 | **점검 시간 30분 안팎** |
| M7 | 되돌리기(문제 시) | |
| M8 | 정리 | |

### M0. Mac mini 준비
- [ ] **M0-1** Docker Desktop(또는 OrbStack)을 설치한다.
  - "로그인 시 시작"을 켠다.
  - 메모리는 4GB 이상 할당한다. Python 채점이 Java를 띄운다.
- [ ] **M0-2** Mac mini가 멈추지 않게 설정한다.
  - 시스템 설정 → 에너지: 잠자기 방지를 켜고, **정전 후 자동으로 시작**을 켠다.
  - 사용자 자동 로그인을 켠다. Docker Desktop은 사용자가 로그인해야 시작된다.
    - FileVault를 켜 두면 자동 로그인을 쓸 수 없다. 그 경우 재부팅 후 직접 로그인해야 서비스가 올라온다.
  - 터미널로 설정하려면: `sudo pmset -a sleep 0 disksleep 0 autorestart 1`
- [ ] **M0-3** git과 GitHub 인증을 준비한다. 인증은 README "Git 원격 주소에서 토큰 제거" 5번의 Deploy key 방식을 권장한다.

### M1. 기존 서버 정보 수집 (Ubuntu, 읽기만)
- [ ] **M1-1** DB 종류와 버전을 확인해 `DB_IMAGE`를 정한다(2-4).
  ```sh
  mysql --version
  mysql -u <DB_USER> -p -e 'SELECT VERSION(), @@character_set_server, @@collation_server'
  ```
- [ ] **M1-2** 채점 스크립트에 필요한 Python 패키지를 확인한다.
  ```sh
  /home/ubuntu/venv/bin/pip freeze
  java -version
  head -30 /home/ubuntu/dongwon/konlpy/check_answer.py     # import 목록 확인
  ```
  - `konlpy` 말고도 쓰는 패키지(예: numpy, scikit-learn)가 있으면 `docker/prod/requirements.txt`에 버전을 고정해 추가하고 커밋한다.
- [ ] **M1-3** 로컬 저장 파일이 있는지 확인한다. 보통은 비어 있다. 업로드 파일은 S3에 있다.
  ```sh
  cd <운영 폴더> && du -sh storage/app && find storage/app -type f | head
  ```
- [ ] **M1-4** 운영 도메인의 현재 DNS 레코드(종류, 값, 프록시 여부)를 기록한다. 되돌릴 때 필요하다.

### M2. Cloudflare Tunnel 생성
- [ ] **M2-1** Cloudflare 대시보드 → Zero Trust → Networks → Tunnels → **Create a tunnel** → Cloudflared를 고르고 이름을 `quizapp-macmini`로 한다.
- [ ] **M2-2** 설치 안내 화면에서 Docker를 고른다. 명령의 `--token` 뒤에 있는 값을 `.env`의 `CLOUDFLARE_TUNNEL_TOKEN`에 넣는다. 안내 명령 자체는 실행하지 않는다(compose가 실행한다).
- [ ] **M2-3** 리허설용 공개 호스트 이름을 추가한다.
  - 호스트: `quiz-test.<도메인>`
  - Service: `HTTP`, URL: `web:80`
- [ ] **M2-4** (권장) 리허설 호스트는 Zero Trust → Access로 관리자 이메일만 열리게 막는다.
- 운영 도메인은 M6에서 연결한다.

### M3. 코드·설정 준비 (Mac mini)
- [ ] **M3-1** 코드를 받고 배포할 커밋으로 맞춘다.
  ```sh
  git clone git@github.com-quizapp:neojjang/quizapp.git ~/quizapp
  cd ~/quizapp && git checkout <배포 커밋 또는 태그>
  ```
- [ ] **M3-2** `.env`를 만든다(2-2).
  ```sh
  scp ubuntu@<기존 서버>:<운영 폴더>/.env ~/quizapp/.env
  chmod 600 ~/quizapp/.env
  # 2-2 표대로 수정
  ```
- [ ] **M3-3** 채점 스크립트 폴더를 복사한다. compose가 `./konlpy`를 `/opt/konlpy`로 읽기 전용 마운트한다.
  ```sh
  scp -r ubuntu@<기존 서버>:/home/ubuntu/dongwon/konlpy ~/quizapp/konlpy
  ```
- [ ] **M3-4** 이미지를 빌드하고 DB만 먼저 띄운다. 첫 빌드는 5분 정도 걸린다.
  ```sh
  cd ~/quizapp
  docker compose -f compose.prod.yml build
  docker compose -f compose.prod.yml up -d --wait db
  ```

### M4. 데이터 이전 리허설
기존 서비스는 그대로 두고, 그 시점의 DB 사본으로 Mac mini를 검증한다.

- [ ] **M4-1** 기존 서버에서 덤프를 받는다. 읽기만 하므로 서비스 중에 해도 된다.
  ```sh
  mysqldump --single-transaction --routines --triggers --default-character-set=utf8mb4 \
    -u <DB_USER> -p <DB_NAME> | gzip > ~/quizapp-rehearsal.sql.gz
  ```
  ```sh
  # Mac mini에서
  mkdir -p ~/quizapp/backups
  scp ubuntu@<기존 서버>:~/quizapp-rehearsal.sql.gz ~/quizapp/backups/
  ```
- [ ] **M4-2** 가져온다. 아래의 `DB 가져오기` 명령을 실행한다.
- [ ] **M4-3** 전체를 띄우고 DB를 검증한다.
  ```sh
  docker compose -f compose.prod.yml up -d
  docker compose -f compose.prod.yml exec -u www-data app php artisan migrate:status | tail -5     # 모두 Ran
  docker compose -f compose.prod.yml exec -u www-data app php artisan migrate --pretend           # 기대값: Nothing to migrate
  docker compose -f compose.prod.yml exec -u www-data -e HOME=/tmp app php artisan tinker \
    --execute='echo App\Models\User::count()," users, ",App\Models\Quiz::count()," answers, tz=",DB::selectOne("select @@session.time_zone as tz")->tz,"\n";'
  ```
  - 사용자 수와 답안 수를 기존 서버의 `SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM quizzes;` 결과와 비교한다.
  - 시간대는 `Asia/Seoul`이어야 한다.
  - `migrate --pretend`가 실행할 SQL을 보여주면 **중단하고 검토한다.**
- [ ] **M4-4** Python 채점 결과가 기존 서버와 같은지 확인한다.
  ```sh
  # 기존 서버
  /home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py "'나는 학교에 가는 소년이다'" "'나는 학교에 걸어가는 소년입니다'"
  # Mac mini
  docker compose -f compose.prod.yml exec -u www-data app /opt/venv/bin/python /opt/konlpy/check_answer.py "'나는 학교에 가는 소년이다'" "'나는 학교에 걸어가는 소년입니다'"
  ```
- [ ] **M4-5** 터널이 연결됐는지 확인한다.
  - `docker compose -f compose.prod.yml logs cloudflared | grep -i registered`
  - 대시보드에서 터널 상태가 HEALTHY여야 한다.
- [ ] **M4-6** `https://quiz-test.<도메인>`에서 `docs/upgrade/PLAN.md` 6장 점검 목록 1~9를 확인한다.
  - 리허설 DB는 M6에서 다시 덮어쓰므로 응시나 수동 채점을 해도 된다.
  - **단, 듣기평가 mp3 업로드·삭제는 하지 않는다.** S3 운영 버킷에 그대로 반영된다. 회원가입이나 비밀번호 재설정 메일도 실제로 발송된다.
- [ ] **M4-7** 백업을 한 번 실행해 본다: `./docker/prod/backup.sh`. 정기 실행 설정은 4장에 있다.

**DB 가져오기** (M4-2, M6-3 공통). DB를 비우고 덤프를 넣는다. mysql·mariadb 이미지 모두에서 동작한다.
```sh
cd ~/quizapp
docker compose -f compose.prod.yml exec -T db sh -c 'c=$(command -v mariadb || command -v mysql); $c -uroot -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS \`$MYSQL_DATABASE\`; CREATE DATABASE \`$MYSQL_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"'
gunzip -c backups/<덤프 파일>.sql.gz | docker compose -f compose.prod.yml exec -T db sh -c 'c=$(command -v mariadb || command -v mysql); $c -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```
- 앱 계정(`DB_USERNAME`) 권한은 DB 이름 단위로 걸려 있어서, DB를 다시 만들어도 유지된다.

### M5. 공지
- [ ] **M5-1** 점검 시간(30분 안팎)을 학생·관리자에게 알린다.
- [ ] **M5-2** 함께 알릴 변경 사항이 있다. B5는 채점 화면에서 "검토 완료"를 눌러야 오늘 채점 목록에서 빠진다는 것이고, B11은 영작나열형에서 구문을 모두 골라야 정답 처리된다는 것이다(PLAN.md 8장). 업로드 권장 크기는 90MB 이하다(2-3).

### M6. 전환 (점검 시간)
- [ ] **M6-1** 기존 서버를 점검 모드로 바꾼다: `cd <운영 폴더> && php artisan down`
- [ ] **M6-2** 최종 덤프를 받아 Mac mini로 옮긴다. 명령은 M4-1과 같고 파일 이름만 `quizapp-final.sql.gz`로 바꾼다.
- [ ] **M6-3** Mac mini에서 위의 `DB 가져오기` 명령으로 가져온다.
- [ ] **M6-4** (M1-3에서 파일이 있었던 경우만) `storage/app`을 옮긴다.
  ```sh
  docker compose -f compose.prod.yml cp <복사해 온 storage/app>/. app:/app/storage/app/
  docker compose -f compose.prod.yml exec app chown -R www-data:www-data storage/app
  ```
- [ ] **M6-5** `.env`의 `APP_URL`을 `https://<운영 도메인>`으로 바꾸고 app을 다시 띄운다(설정 캐시 재생성).
  ```sh
  docker compose -f compose.prod.yml up -d --force-recreate app
  ```
- [ ] **M6-6** DNS를 터널로 돌린다.
  1. Cloudflare DNS에서 운영 도메인의 기존 레코드(기존 서버를 가리키는 A/CNAME)를 지운다. 값은 M1-4에서 기록해 두었다.
  2. Tunnels → `quizapp-macmini` → 공개 호스트 이름에 `<운영 도메인>` → `HTTP` `web:80`을 추가한다. 기존 레코드가 남아 있으면 추가되지 않는다.
- [ ] **M6-7** 바로 확인한다.
  - `https://<운영 도메인>/login` 화면이 정상으로 뜨는지, 관리자 로그인이 되는지 본다.
  - 테스트 학생 계정으로 OMR형과 일반형에 한 번씩 응시한다. 확인이 끝나면 그 응시 기록을 지운다.
  - 오류를 확인한다: `docker compose -f compose.prod.yml logs --since 10m app web cloudflared`
- [ ] **M6-8** 리허설 호스트 `quiz-test.<도메인>`을 터널에서 지운다.
- [ ] **M6-9** 기존 서버는 점검 모드로 켜 둔 채 1주일 보관한다(M7 대비).

### M7. 되돌리기 (문제 시)
- [ ] **M7-1** Cloudflare에서 운영 도메인의 공개 호스트 이름을 터널에서 지우고, M1-4에 기록한 DNS 레코드를 다시 만든다.
- [ ] **M7-2** 기존 서버에서 `php artisan up`을 실행한다.
- [ ] **M7-3** 전환 뒤 Mac mini에 쌓인 응시 기록은 기존 서버에 없다. 필요하면 Mac mini에서 `./docker/prod/backup.sh`로 덤프를 받아 기존 서버 DB에 가져온다. 이렇게 하면 기존 서버 DB가 덮어써진다.
- [ ] **M7-4** 원인을 PLAN.md 작업 로그에 남기고, 개발 환경에서 재현·수정한 뒤 M4부터 다시 한다.

### M8. 정리
- [ ] **M8-1** 1주일 동안 문제가 없으면 기존 서버의 마지막 덤프와 운영 폴더를 보관용으로 압축한 뒤 서버를 정리한다.
- [ ] **M8-2** 4장의 정기 작업(백업, 보안 갱신)이 실제로 돌고 있는지 확인한다.

## 4. 운영

모든 명령은 `~/quizapp`에서 실행한다.

**상태·로그**
```sh
docker compose -f compose.prod.yml ps
docker compose -f compose.prod.yml logs --tail=100 app web cloudflared
docker compose -f compose.prod.yml exec app sh -c 'tail -n 100 storage/logs/laravel-$(date +%Y-%m-%d).log'
```

**관리 명령**은 php-fpm과 같은 www-data로 실행한다. root로 실행하면 root 소유 로그 파일이 생겨 앱이 기록하지 못할 수 있다.
```sh
docker compose -f compose.prod.yml exec -u www-data app php artisan <명령>
docker compose -f compose.prod.yml exec -u www-data -e HOME=/tmp app php artisan tinker
```

**코드 업데이트**
```sh
./docker/prod/backup.sh
git pull
docker compose -f compose.prod.yml up -d --build
docker compose -f compose.prod.yml exec -u www-data app php artisan migrate --force    # 새 마이그레이션이 있을 때
```

**보안 갱신** (월 1회)
```sh
./docker/prod/backup.sh
docker compose -f compose.prod.yml build --pull     # PHP·nginx·OS 패키지 최신화
docker compose -f compose.prod.yml pull db           # mysql:8.4 / mariadb:11.4의 최신 패치
docker compose -f compose.prod.yml up -d
```
- cloudflared는 버전이 고정되어 있다(`compose.prod.yml`). 대시보드에 업데이트 안내가 나오면 태그를 올려 커밋한다.

**백업** (매일 새벽 3시, 14일 보관)
```sh
crontab -e
# 아래 한 줄 추가. Docker Desktop의 docker 명령 경로를 PATH에 넣는다
0 3 * * * PATH=/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin $HOME/quizapp/docker/prod/backup.sh >> $HOME/quizapp/backups/backup.log 2>&1
```
- 백업은 Mac mini 안에만 있다. 디스크 고장에 대비해 `backups/`를 외장 디스크나 클라우드 저장소에 주기적으로 복사한다.
- 복원은 3장 M4의 `DB 가져오기`와 같다.

**하지 말 것**
- `docker compose -f compose.prod.yml down -v` (DB와 storage 볼륨 삭제)
- web(8080)이나 db 포트를 `0.0.0.0`으로 여는 것. 외부 접근은 터널로만 한다.
