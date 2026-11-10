# Laravel 업그레이드 & 차트 교체 계획

> 이 문서가 작업의 단일 기준입니다. 작업을 시작·완료할 때마다 **상태 표**와 **작업 로그**를 갱신합니다.

- 작성: 2026-11-10
- 작업 브랜치: `upgrade/laravel` (`develop`에서 분기, 단계별로 `develop`에 병합)
- 상태 표기: `[ ]` 대기 · `[~]` 진행 중 · `[x]` 완료 · `[!]` 막힘(사유를 로그에 기록) · `[-]` 취소

---

## 1. 목표

1. 개발이 중단된 차트 라이브러리(`consoletvs/charts` 포크 + Chartisan)를 걷어내고 Chart.js를 직접 사용한다.
2. Laravel 8 → 최신(D1 결정) 및 관련 패키지(Livewire, Jetstream, Sanctum, Spatie Permission, 빌드 도구)를 갱신한다.
3. 기존 기능(응시·자동 채점·재시도·수동 채점·날짜별 응시자 조회)의 동작을 유지한다.

## 2. 환경 규칙

### 개발 환경 (필수 준수)
- **서버는 Docker로 실행해 테스트한다.** 로컬 맥에는 PHP/Composer가 없다. `composer`, `php artisan`, `phpunit`, `npm` 등 모든 명령은 컨테이너 안에서 실행한다.
- **DB는 SQLite를 사용한다.** 개발: `database/database.sqlite`, 테스트: `:memory:`.
- 운영 DB(MySQL 또는 MariaDB — D4)와의 차이로 생길 수 있는 문제는 각 단계의 "호환성 확인" 작업에서 따로 점검한다.

#### 사용법 (`compose.dev.yml`)
```sh
docker compose -f compose.dev.yml build app                          # 이미지 빌드 (PHP_VERSION=8.1 기본)
PHP_VERSION=8.5 docker compose -f compose.dev.yml build app          # 단계별 PHP 버전 변경
docker compose -f compose.dev.yml run --rm app composer install
docker compose -f compose.dev.yml run --rm app php artisan migrate:fresh --seed
docker compose -f compose.dev.yml run --rm app php artisan test
docker compose -f compose.dev.yml up -d app                          # http://localhost:8000
docker compose -f compose.dev.yml run --rm node npm ci               # 프론트 빌드 도구
```
- DB 연결은 compose의 환경 변수(`DB_CONNECTION=sqlite`, `DB_DATABASE=/app/database/database.sqlite`)로 지정한다. `.env`는 건드리지 않는다.
- 시드 관리자 계정: `admin@admin.com`, `superadmin@admin.com` (비밀번호는 `AdminUserSeeder` 참고)
- 서버는 `php -S`로 직접 띄운다. `artisan serve`는 모든 SQL을 `error_log` 채널로 기록하는 설정(`AppServiceProvider`) 때문에 응답이 멈춘다.

### 운영 환경
- Ubuntu 최신 버전 서버에서 직접 실행한다. 실행 방식(php-fpm/nginx 등)은 P0-7에서 확인한다.
- 목표: **PHP 8.5.x 최신 보안 패치**(2026-11-10 기준 8.5.12), **Laravel 최신(13)**, DB는 MySQL 또는 MariaDB.
- Python 서술형 채점: `/home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py` (현재 `UserQuizlv.php`에 하드코딩)

## 3. 결정 필요 사항

| ID | 내용 | 권장 | 상태 |
|---|---|---|---|
| D1 | 최종 목표 버전 | — | **결정**: Ubuntu 최신, PHP 최신 보안 패치(8.5.x), Laravel 최신(13). P6에서 Laravel 13의 PHP 8.5 지원 여부를 composer로 확인 |
| D2 | 작업 트리의 미커밋 변경과 미추적 파일 처리 | — | **결정**: `Dockerfile` 수정(gd 확장 추가 등)은 유지·커밋, `package-lock.json` 이름 변경은 되돌림, 나머지 미추적 파일은 삭제(목록은 로그 참고) |
| D3 | 관리자 계정이 `user` 역할도 갖는지(현재 차트 API 미들웨어가 `role:user`) | 차트 교체 시 라우트 권한으로 대체되므로 확인만 | 미정 |
| D4 | 운영 DB 종류·버전, 백업 방법 | — | **결정**: MySQL 또는 MariaDB. 정확한 버전·백업 방법은 P0-7에서 확인 |
| D5 | Livewire 최신 메이저(3 vs 4) 및 Jetstream 호환 버전 | 해당 단계 시작 시 `composer show -a`로 확인 후 결정 | 미정 |

## 4. 상태 요약

| 단계 | 내용 | 상태 |
|---|---|---|
| P0 | 준비: 작업 트리 정리, Docker+SQLite 개발 환경, 운영 정보 수집 | [~] P0-1 삭제 실행, P0-7 운영 정보 남음 |
| P1 | 회귀 테스트 작성 (업그레이드 전 기준선) | [ ] |
| P2 | 차트 라이브러리 제거 → Chart.js 직접 사용 (Laravel 8 유지) | [ ] |
| P3 | Laravel 8 → 9, Mix → Vite | [ ] |
| P4 | Laravel 9 → 10 | [ ] |
| P5 | Laravel 10 → 11, Livewire 2 → 3, Jetstream 2 → 5 | [ ] |
| P6 | Laravel 11 → 12 (→ 13, D1) | [ ] |
| P7 | 정리: 레거시 패키지 제거, 하드코딩 경로 설정화 | [ ] |
| P8 | 운영 배포 | [ ] |

각 단계의 공통 완료 조건: **컨테이너에서 `php artisan test` 전부 통과 + 6장 수동 점검 목록 통과 + 로그 기록 + 커밋.**

---

## 5. 세부 작업

### P0. 준비

- [~] **P0-1** D2 처리: 확인 완료(로그 참고). 미추적 파일 삭제는 권한 설정상 사용자가 직접 실행해야 함 — 아래 명령
  ```sh
  rm -rf .DS_Store docker/.DS_Store docker/8.0 docker/8.1 docker/8.2 sail composer.lock.bak \
    config/flare.php config/ignition.php config/livewire.office.php config/tinker.php config/trustedproxy.php \
    database/migrations/2020_05_21_*_create_team*.php resources/js/Jetstream resources/js/Pages \
    resources/views/errors routes/jetstream.php public/vendor/livewire \
    resources/views/vendor/livewire resources/views/vendor/mail resources/views/vendor/notifications resources/views/vendor/pagination
  ```
- [x] **P0-2** `upgrade/laravel` 브랜치를 만든다.
- [x] **P0-3** 개발용 Docker 구성 `compose.dev.yml` + `docker/dev/Dockerfile`
  - 빌드 인자 `PHP_VERSION`(P0~P2: 8.1, P3~: 단계별로 올림), 확장: pdo_mysql, zip, gd, bcmath, intl, exif (+ 기본 pdo_sqlite, mbstring)
  - `pdo_mysql`이 필요한 이유: `config/database.php`가 확장 유무와 관계없이 `PDO::MYSQL_ATTR_INIT_COMMAND`를 참조함
  - 서비스: `app`(`php -S`), `node`(node:22, `tools` 프로필 — `run`으로만 사용)
- [x] **P0-4** SQLite 설정: compose 환경 변수로 지정(`.env` 미변경), `phpunit.xml`의 SQLite `:memory:` 활성화
- [x] **P0-5** SQLite에서 `migrate:fresh --seed` 성공
  - `2022_03_09_134316_add_question_type`의 MySQL 전용 접두사 인덱스 `question(64)`를 MySQL/MariaDB일 때만 만들도록 감쌈. 운영에서는 이미 실행된 마이그레이션이라 영향 없음
- [ ] **P0-6** Python 채점 경로 설정화 → P7-3에서 진행(개발 컨테이너에는 konlpy 없음)
- [ ] **P0-7** 운영 서버 정보 수집: Ubuntu 버전, PHP 버전·확장, 웹서버, DB 종류·버전, 배포 방식(git pull? `composer install`?), 큐·크론 사용 여부, 백업 방법
- [x] **P0-8** 기준 테스트 결과: 23 통과 / 1 실패 / 7 건너뜀 / 1 risky
  - 실패: `RegistrationTest::test_new_users_can_register` — `CreateNewUser`가 `assignRole('user')`를 호출하는데 테스트 DB에 역할 시드가 없음 → P1-0에서 수정
  - 건너뜀 7개는 Jetstream 기능 비활성(API, 이메일 인증 등)에 따른 정상 동작

### P1. 회귀 테스트 (업그레이드 전 기준선)

업그레이드 중 깨진 곳을 잡기 위한 최소한의 Feature 테스트. 팩토리(`database/factories`)를 재사용한다.

- [ ] **P1-0** 테스트 공통 설정에서 `RolesPermissionsSeeder` 실행 → 기존 `RegistrationTest` 실패 해소
- [ ] **P1-1** 시험 데이터 팩토리 보강: 대분류→중분류→수업→Section→Question→Answer 체인을 만드는 헬퍼
- [ ] **P1-2** 자동 채점: 객관식·OMR·구문 나열·듣기 유형별로 정답/오답 제출 시 `QuizHeader.score`가 맞는지 (`UserQuizlv` Livewire 테스트)
- [ ] **P1-3** 중복 제출: 같은 문항을 두 번 제출해도 `Quiz` 레코드가 한 번만 생기는지
- [ ] **P1-4** 재시도: 재시도 시 기존 기록 처리 규칙대로 동작하는지
- [ ] **P1-5** 수동 채점: `UserAnswer`에서 정오를 바꾸면 점수가 다시 계산되는지
- [ ] **P1-6** 권한: 학생이 관리자 라우트 접근 시 403, 비로그인 시 로그인 페이지로 이동
- [ ] **P1-7** 결과 조회: 다른 학생의 응시 ID 조회 시 동작 기록 (현재는 null 속성 접근 오류 예상 → 테스트로 현상 고정, 수정은 별도 작업)
- [ ] **P1-8** 서술형(Python) 분기는 `shell_exec`를 테스트에서 대체할 수 없으므로 이번 범위에서 제외하고 수동 점검 목록에 둔다.

### P2. 차트 교체 (Laravel 8 유지)

- [ ] **P2-1** 차트 데이터 계산을 컨트롤러로 옮긴다.
  - 학생 홈(`AppUserController` → `appusers/userQuizHome`): 최근 30건 점수, 라벨은 응시일
  - 관리자 홈(`AdminController` → `admins/adminhome`): 최근 100건 점수, 최근 N일 일별 가입자 수(빈 날짜는 0으로 채움)
- [ ] **P2-2** 기존 버그 수정
  - `orderBy('created_at','asc')->take(n)` → 최근 n건을 시간순으로 표시
  - "Monthly Users" 제목과 실제 기간(1주) 불일치 → 제목·기간 중 하나로 맞춘다
- [ ] **P2-3** 뷰 수정: `<canvas>` + `new Chart(el, { type: 'line', data: { labels: @json($labels), ... } })`
- [ ] **P2-4** `layouts/app.blade.php`의 unpkg `chart.js@2.9.3`, `@chartisan/chartjs` 스크립트 제거 → Chart.js 4를 npm 의존성으로 추가해 `app.js`에서 `window.Chart`로 노출(Mix 빌드)
- [ ] **P2-5** 제거: `app/Charts/*`, `config/charts.php`, `AppServiceProvider::boot`의 차트 등록, `config/app.php`의 `ChartsServiceProvider`, composer의 `bahadorbzd/consoletvs-charts`·`pamitvn/consoletvs-charts`·`pamitvn/chartisan-php`와 `repositories` 항목
- [ ] **P2-6** 차트 데이터 테스트: 컨트롤러가 뷰에 넘기는 `$labels`/`$data` 검증
- [ ] **P2-7** 브라우저에서 학생·관리자 홈 차트 확인(스크린샷을 로그에 첨부)

### P3. Laravel 8 → 9, Mix → Vite

- [ ] **P3-1** 개발 컨테이너 `PHP_VERSION=8.1`
- [ ] **P3-2** composer: `laravel/framework ^9`, `nunomaduro/collision ^6`, `spatie/laravel-ignition`(`facade/ignition` 대체), `laravel/sanctum ^3`, `spatie/laravel-permission ^5`, `fideloper/proxy` 제거(`TrustProxies` 미들웨어를 프레임워크 기본으로), `fruitcake/laravel-cors` 제거(`HandleCors` 프레임워크 기본)
- [ ] **P3-3** 공식 업그레이드 가이드 9.x 항목 적용: Flysystem 3(S3 드라이버 `league/flysystem-aws-s3-v3 ^3`), `Str`/`Storage` 동작 변경, `lang` 디렉터리 이동
- [ ] **P3-4** S3 업로드·다운로드 경로 코드(`SectionFiles` 등) 점검 — Flysystem 3 예외 동작 변경
- [ ] **P3-5** Mix → Vite: `vite.config.js`, `@vite` 디렉티브, `webpack.mix.js` 제거, `package.json` 스크립트 교체. Tailwind는 2 → 3으로 올린다(Jetstream 5 기준과 맞춤), `purge` → `content`
- [ ] **P3-6** 테스트·수동 점검 → 로그 → 커밋

### P4. Laravel 9 → 10

- [ ] **P4-1** 컨테이너 `PHP_VERSION=8.2`
- [ ] **P4-2** composer: `laravel/framework ^10`, `phpunit ^10`, `collision ^7`, `laravel/sanctum ^3.2`, `spatie/laravel-permission ^6`
- [ ] **P4-3** 가이드 10.x 적용: `$dates` 속성 → `$casts`, `Bus::dispatchNow` 등 제거된 API 검색
- [ ] **P4-4** `phpunit.xml` PHPUnit 10 형식으로 마이그레이션
- [ ] **P4-5** 테스트·수동 점검 → 로그 → 커밋

### P5. Laravel 10 → 11, Livewire 3, Jetstream 5

이 단계가 가장 크다. 하위 작업 단위로 커밋한다.

- [ ] **P5-1** D5 결정(Livewire·Jetstream 호환 버전 확인)
- [ ] **P5-2** composer: `laravel/framework ^11`, `livewire/livewire`(D5), `laravel/jetstream ^5`, `laravel/sanctum ^4`, `doctrine/dbal` 제거(Laravel 11은 네이티브 컬럼 변경 지원 — P0-5 결과로 재확인)
- [ ] **P5-3** 애플리케이션 구조: 기존 구조(`Kernel` 등)는 유지 가능 — 슬림 스켈레톤으로 옮기지 않는다(변경 최소화)
- [ ] **P5-4** Livewire 컴포넌트 8개 이전 (컴포넌트 하나 = 커밋 하나)
  - [ ] `UserQuizlv` (가장 큼: 유형 분기·채점·저장·타이머)
  - [ ] `UserAnswer`
  - [ ] `TestCalendar`
  - [ ] `ShowTodayTesters`
  - [ ] `UserDatatable`
  - [ ] `MakeAnswerSheetForm`
  - [ ] `MakeListeningTestForm`
  - [ ] `MakeTestAnswerForm`
  - 각 컴포넌트 공통: 네임스페이스 `App\Livewire`, `emit`/`dispatchBrowserEvent` → `dispatch`, `$listeners` → `#[On]`, `wire:model`(수식어 없는 35곳) 실시간 반영 필요 여부 판단해 `.live` 지정, `@livewireScripts`/Alpine 중복 로딩 제거(Livewire 3이 Alpine 포함)
- [ ] **P5-5** Jetstream 5: 인증·프로필·API 토큰 뷰 재게시 후 기존 커스터마이즈 반영, Fortify 액션 비교
- [ ] **P5-6** `realrashid/sweet-alert` 호환 버전 확인 및 갱신
- [ ] **P5-7** 테스트·수동 점검 → 로그 → 커밋

### P6. Laravel 11 → 12 (→ 13)

- [ ] **P6-1** composer: `laravel/framework ^12`, 관련 패키지 호환 버전
- [ ] **P6-2** 가이드 12.x 적용, 테스트·수동 점검 → 커밋
- [ ] **P6-3** 컨테이너 `PHP_VERSION=8.5`, `laravel/framework ^13` 적용, 가이드 13.x, 테스트·수동 점검 → 커밋

### P7. 정리

- [ ] **P7-1** `composer outdated -D`, `npm outdated`로 남은 구버전 확인·갱신
- [ ] **P7-2** `minimum-stability: dev` → `stable` 검토(포크 패키지 제거 후 가능)
- [ ] **P7-3** Python 채점 경로를 `config/services.php` + `.env`로 분리, 인자에 `escapeshellarg` 적용, 실행 실패 시 처리
- [ ] **P7-4** 운영용 `Dockerfile`(현재 `php:8.0-fpm`)을 유지할지 결정 — 운영이 Ubuntu 직접 실행이라면 삭제 또는 갱신
- [ ] **P7-5** `README`에 개발 환경 실행 방법(Docker + SQLite) 기록

### P8. 운영 배포 (Ubuntu)

- [ ] **P8-1** 운영 DB 백업 및 복원 절차 확인
- [ ] **P8-2** 운영 PHP 업그레이드(D1 버전) 및 확장 설치 — 기존 PHP와 병행 설치 후 전환해 되돌릴 수 있게 한다
- [ ] **P8-3** 스테이징(가능하면) 또는 운영 DB 사본으로 `php artisan migrate --pretend` 확인
- [ ] **P8-4** 배포: `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate`, 캐시(`config/route/view`) 재생성
- [ ] **P8-5** 배포 후 6장 수동 점검, Python 서술형 채점 포함
- [ ] **P8-6** 되돌리기 절차: 이전 릴리스 디렉터리 + 이전 PHP로 전환, DB 백업 복원

---

## 6. 수동 점검 목록 (각 단계 공통)

Docker 컨테이너로 서버를 띄우고 SQLite 시드 데이터로 확인한다.

1. 로그인 / 로그아웃 / 회원가입
2. 관리자: 대분류·중분류·수업·시험·문제 생성, CSV 문제 입력
3. 학생: 일반 시험 응시 → 제출 → 점수 확인
4. OMR 답안지, 구문 나열, 듣기 평가(S3 파일은 개발에서 `local` 디스크로 대체)
5. 문항별 타이머, 재시도
6. 관리자: 수동 채점 → 점수 재계산, 검토 표시
7. 날짜별 응시자(달력) 조회
8. 학생·관리자 홈 차트 표시
9. (운영만) 서술형 Python 채점

## 7. 위험 요소

| 위험 | 대응 |
|---|---|
| SQLite(개발)와 운영 DB의 차이로 운영에서만 나는 오류 | 원시 SQL(`DATE()`, `count(*)`) 사용처 목록 유지: `TestCalendar.php`, (P2 전) `MonthlyUsers.php`. P8-3에서 운영 DB 사본으로 확인 |
| Livewire 3의 `wire:model` 기본 동작 변경으로 입력이 서버에 늦게 반영 | P5-4에서 35곳을 하나씩 판단 |
| Jetstream 뷰 재게시로 기존 수정 사항 유실 | 재게시 전 `git diff`로 커스터마이즈 목록 작성 |
| 자동 채점 회귀 | P1 테스트를 모든 단계에서 실행 |
| 운영 PHP 업그레이드 후 되돌리기 어려움 | P8-2 병행 설치, P8-6 절차 |

## 8. 작업 로그

새 항목을 위에 추가한다. 형식: `날짜 | 작업 ID | 결과 | 메모`

| 날짜 | 작업 | 결과 | 메모 |
|---|---|---|---|
| 2026-11-10 | P0-8 | 완료 | 23 통과 / 1 실패(역할 시드 없음, P1-0) / 7 건너뜀. `/login`, `/register`, `/` 200 확인 |
| 2026-11-10 | 발견 | 수정 | `vendor/laravel/framework/.../Validator.php`에 디버그용 `Log::warning($rule);`이 직접 삽입돼 있었음(`composer status`로 확인) → `composer reinstall laravel/framework`로 원복. 운영 서버 vendor에도 같은 수정이 있는지 P0-7에서 `composer status`로 확인 필요 |
| 2026-11-10 | 발견 | 수정 | 커밋된 `composer.lock`이 `composer.json`과 불일치(`pamitvn/*` 2개 누락) → `composer update pamitvn/chartisan-php pamitvn/consoletvs-charts`로 해당 패키지만 동기화. 이 과정에서 `consoletvs/charts 6.7`, `balping/json-raw-encoder`가 lock에서 빠짐. 둘 다 P2에서 제거 대상 |
| 2026-11-10 | P0-3 | 완료 | `artisan serve`에서 응답 멈춤 → `php -S`로 교체 |
| 2026-11-10 | P0-5 | 완료 | 접두사 인덱스 마이그레이션 1건 DB 종류 조건 추가 |
| 2026-11-10 | P0-1 | 확인 | 유지: `Dockerfile` 수정. 되돌림: `package-lock.json`. 삭제 대상(모두 vendor 기본값과 동일하거나 미사용): `sail`, `docker/8.0~8.2`(Sail 런타임), teams 마이그레이션 3개(teams 기능 비활성), `resources/js/Jetstream`·`Pages`(Inertia용 Vue 스텁, 앱은 Livewire 스택), `resources/views/errors`·`vendor/{livewire,mail,notifications,pagination}`·`public/vendor/livewire`·`routes/jetstream.php`·`config/{flare,ignition,tinker,trustedproxy}.php`(기본값과 동일), `config/livewire.office.php`(livewire.php의 미사용 변형), `composer.lock.bak`(2024-09 백업). 백업: 세션 스크래치패드 `d2-untracked-backup.tgz` |
| 2026-11-10 | D1·D2·D4 | 결정 | PHP 8.5.x / Laravel 13 / MySQL·MariaDB |
| 2026-11-10 | — | 계획 작성 | 코드 분석 기반. 실제 실행 검증은 아직 없음 |
