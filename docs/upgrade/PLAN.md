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
| D3 | 관리자 계정이 `user` 역할도 갖는지(차트 API 미들웨어가 `role:user`) | — | **해소**: P2에서 차트 API 자체를 없앰. 차트는 각 홈 화면 권한을 따름 |
| D4 | 운영 DB 종류·버전, 백업 방법 | — | **결정**: MySQL 또는 MariaDB. 정확한 버전·백업 방법은 P0-7에서 확인 |
| D6 | 8장 버그(B1~B7) 수정 시점 | — | **결정**: P2 전에 수정 완료(BF 단계). 선택형·단답형 문제는 운영에서 OMR형 시험에서만 사용 |
| D7 | B8(비활성 문제 출제) 수정 여부 | — | **결정**: 수정. 출제·채점 모두 사용 중인 문제만 |
| D5 | Livewire 최신 메이저(3 vs 4) 및 Jetstream 호환 버전 | 해당 단계 시작 시 `composer show -a`로 확인 후 결정 | 미정 |

## 4. 상태 요약

| 단계 | 내용 | 상태 |
|---|---|---|
| P0 | 준비: 작업 트리 정리, Docker+SQLite 개발 환경, 운영 정보 수집 | [~] P0-1 삭제 실행, P0-7 운영 정보 남음 |
| P1 | 회귀 테스트 작성 (업그레이드 전 기준선) | [x] |
| BF | 기존 버그 B1~B7 수정 (D6) | [x] |
| P2 | 차트 라이브러리 제거 → Chart.js 직접 사용 (Laravel 8 유지) | [x] |
| P3 | Laravel 8 → 9, Mix → Vite | [x] |
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

- [x] **P1-0** `tests/TestCase.php`에서 `RolesPermissionsSeeder` 시드 → 기존 `RegistrationTest` 실패 해소
- [x] **P1-1** 테스트 데이터 헬퍼 `tests/Feature/Quiz/BuildsQuizzes.php` (빈 팩토리 대신 트레이트로 구성: 분류→수업→시험→문제→보기, 응시 기록)
- [x] **P1-2** 자동 채점
  - `NormalQuizTest`: 번역(공백 무시 일치), 영작(대소문자·공백), 여러 문제 순서·점수
  - `AnswerSheetQuizTest`: OMR·듣기평가 일괄 채점(선택형·단답형·영작), 오답 보기 함께 선택 시 오답, 모두 답하기 전 제출 비활성
  - `SentenceOrderQuizTest`: 정순서 100점, 오순서 0점, 재시도 기록(`retry`, `extra_info`), 선택 취소 후 재선택
- [x] **P1-3** 중복 제출 → 버그 B3 확인(테스트는 미완료 표시)
- [x] **P1-4** 재시도 → `SentenceOrderQuizTest`에 포함
- [x] **P1-5** 수동 채점 `ManualGradingTest`: 정오 변경 시 점수 재계산, 채점 화면 열람 시 `reviewed=true`(현재 동작 기록, B5)
- [x] **P1-6** 권한 `AccessAndResultsTest`: 비로그인→로그인, 학생→관리자 403, 관리자 홈·학생 홈·응시 시작 화면 렌더링
- [x] **P1-7** 결과 조회: 본인 결과 200, 남의 응시 ID는 500(현재 동작 기록, B4)
- [-] **P1-8** 서술형 Python 분기(10자 초과 + 불일치)는 테스트에서 제외, 수동 점검 목록 유지

결과: 44 통과 / 0 실패 / 5 미완료(B1·B2·B3·B6·B7의 올바른 동작 테스트, 버그 수정 시 `markTestIncomplete` 줄을 지우면 됨) / 7 건너뜀

### BF. 기존 버그 수정 (P2 전, D6)

- [x] **BF-1** B1·B2·B7 `UserQuizlv`: 선택형 판정을 `type_id - 1` 기준으로, 선택 배열을 그대로 전달, 단답형 분기 추가
- [x] **BF-2** B3 `UserQuizlv::nextQuestion`·`checkAllAnswers`: 시험이 끝난 뒤(`quizInProgress=false`) 들어온 제출은 무시
- [x] **BF-3** B4 `AppUserController::userQuizDetails`: `firstOrFail()` → 404
- [x] **BF-4** B5 채점 화면 열람 시 자동 검토 처리 제거, 채점 화면에 **"검토 완료" 버튼** 추가(`POST .../reviewed`, 라우트 `markReviewed`). 검토 후에는 "검토 완료됨" 표시
- [x] **BF-5** B6 `UserAnswer`: `user_answer` 분리를 `mount()`에서 `render()`로 옮김(뷰 변수 `$selectedAnswerIds`)
- [x] **BF-6** 테스트 갱신: 미완료 표시 제거, B4는 404, B5는 버튼 동작·학생 권한(403), OMR 중복 제출 테스트 추가 → **51 통과 / 0 실패 / 0 미완료 / 7 건너뜀**
- [x] **BF-8** B8 `getNextQuestion`·`getAllQuestions`에 `is_active='1'` 조건 추가, 일반형·OMR 테스트 추가(수정 전 실패, 수정 후 통과 확인)
- [x] **BF-7** Orca 브라우저로 채점 화면 확인: "검토 완료" 클릭 → "검토 완료됨"·성공 메시지 표시, DB `reviewed=1`

### P2. 차트 교체 (Laravel 8 유지)

- [x] **P2-1** 차트 데이터를 컨트롤러에서 계산
  - 학생 홈(`AppUserController::userQuizHome`): 본인 완료 응시 최근 30건 점수, 라벨은 응시일(`m-d`)
  - 관리자 홈(`AdminController::adminhome`): 완료 응시 최근 100건 점수, 최근 30일 일별 가입자 수(빈 날짜 0). 날짜 그룹핑은 PHP에서 처리해 SQLite·MySQL 차이 없음
- [x] **P2-2** 기존 버그 수정: 가장 오래된 N건 → 최근 N건(시간순 표시), 미완료 응시(점수 없음) 제외, "Monthly Users"(실제 1주) → "일별 가입자 (최근 30일)"
- [x] **P2-3** 공용 컴포넌트 `<x-line-chart>`(`resources/views/components/line-chart.blade.php`). `app.js`가 `defer` 로드라 `DOMContentLoaded`에서 그림
- [x] **P2-4** unpkg의 Chart.js 2.9.3·Chartisan 스크립트 제거 → `chart.js ^4.6.1` npm 의존성, `app.js`에서 `window.Chart`로 노출. 자산은 `npm run prod`로 재빌드(버전 해시 포함)
  - 이전에 커밋된 CSS(약 8MB)는 Tailwind 클래스를 걸러내지 않은 빌드였음. 설정(`mode: 'jit'`)대로 다시 빌드하면 사용 중인 클래스만 남음(42KB). Blade·PHP에 동적으로 만든 클래스명이 없는 것을 확인
  - Mix의 구버전 webpack이 Node 22에서 실패해 `compose.dev.yml`의 node 서비스에 `NODE_OPTIONS=--openssl-legacy-provider` 추가(P3에서 제거)
- [x] **P2-5** 제거: `app/Charts/*`, `config/charts.php`, `AppServiceProvider::boot` 차트 등록, `config/app.php`의 미사용 import, composer 패키지 3개와 `repositories` → `api/chart/*` 라우트도 사라짐
- [x] **P2-6** `ChartDataTest`: 학생 차트(본인·완료·최근 30건·순서), 관리자 차트(최근 100건, 30일 가입자·빈 날짜 0)
- [x] **P2-7** Orca 브라우저 확인: 관리자 홈 차트 2개·학생 홈 차트 1개 렌더링, 콘솔 오류 없음

결과: 테스트 **53 통과** / 0 실패 / 7 건너뜀

### P3. Laravel 8 → 9, Mix → Vite

- [x] **P3-1** 개발 컨테이너 PHP 8.1 유지(Laravel 9 요구: 8.0.2+)
- [x] **P3-2** composer: `laravel/framework 9.52.23`, `jetstream 2.16.2`, `sanctum 3.3.3`, `livewire 2.12.8`(그대로), `spatie/laravel-permission 5.11.1`, `realrashid/sweet-alert 7.3.2`, `spatie/laravel-ignition`(facade/ignition 대체), `collision ^6`, `fideloper/proxy`·`fruitcake/laravel-cors` 제거
  - `TrustProxies`는 `Illuminate\Http\Middleware\TrustProxies`, CORS는 `Illuminate\Http\Middleware\HandleCors`로 교체
  - **Composer 2.11의 보안 차단**: Laravel 9.x 전 버전이 보안 권고 대상이라 설치가 막힘 → 중간 단계(P3~P5)만 `composer update --no-blocking`으로 진행. 이 단계들의 커밋은 **배포 금지**. 최종(P6) lock은 이 옵션 없이 설치돼야 함
- [x] **P3-3** Flysystem 3: `s3` 디스크에 `'throw' => true` — 기본값(false)이면 업로드 실패 시 `false`가 `section_files.file_url`에 저장됨. `lang` 디렉터리 이동은 불필요(9.x는 기존 위치 지원)
- [x] **P3-4** S3 코드(`MakeListeningTestForm`) 점검: `put()`/`delete()` 사용법 동일, 옵션 `ACL`은 Flysystem 3에서도 전달됨. 실제 S3 업로드는 운영 자격 증명이 필요해 미검증 → 수동 점검 목록 4번
- [x] **P3-5** Mix → Vite 7(`laravel-vite-plugin 2`), Tailwind 2 → 3.4
  - `vite.config.js`, `postcss.config.js` 추가, `webpack.mix.js`·`public/js`·`public/css`·`mix-manifest.json` 제거, 레이아웃 2개에 `@vite`
  - `resources/js` CommonJS → ES 모듈, `.env`의 `MIX_` 접두사 → `VITE_`(Pusher 주석만 해당)
  - 빌드 결과 `public/build`를 커밋(기존처럼 빌드된 자산을 저장소에 둠 — 운영에서 npm 빌드를 할지는 P8에서 결정)
  - `tailwind.config.js`: `purge`→`content`, `mode: 'jit'`·`variants` 제거, **`darkMode: 'class'`**(Tailwind 3는 OS 다크 모드에서 `dark:` 클래스를 적용해 관리자 홈 카드가 검게 나옴 → Tailwind 2와 같게)
  - 달력 칸의 `min-h-24`는 Tailwind 2에 없는 클래스라 무시되다가 이제 적용됨(칸 높이 증가, 작성자 의도대로) — 유지
  - compose node 서비스의 `NODE_OPTIONS` 제거
- [x] **P3-6** 테스트 55 통과. Orca 브라우저: 관리자 홈 차트·Livewire·Alpine 로드, OMR 데모 시험 응시→제출→100% 결과·DB 저장, "검토 완료" 후 sweet-alert 팝업 확인

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

## 8. 발견된 버그

P1 테스트로 확인한 기존 버그. 업그레이드와 별개이며, 수정 여부·시점은 결정이 필요하다(D6). 각 항목의 올바른 동작은 테스트에 미완료(`markTestIncomplete`)로 들어 있다.

| ID | 위치 | 증상 | 원인 | 테스트 |
|---|---|---|---|---|
| B1 | `UserQuizlv::updatedUserAnswered` | **일반형** 시험에서 선택형 보기를 체크하면 오류 | DB `type_id`(1=선택형)를 상수 `SELECTIVE`(0)와 그대로 비교 → 체크박스 배열에 `trim()` | `NormalQuizTest` selective 2개 |
| B2 | `UserQuizlv::checkCurrentAnswer` | B1을 고쳐도 선택형 제출 시 오류 | `[$this->userAnswered]`로 배열을 한 번 더 감싸 `explode()`에 배열 전달 | 위와 동일 |
| B3 | `UserQuizlv::nextQuestion` | 마지막 문제에서 "다음"을 두 번 누르면 기록 중복, 점수 100→50, `quiz_size` 1→2, 이후 오류 | 시험 종료 상태 검사 없음, 트랜잭션 없음 | `SentenceOrderQuizTest::test_submitting_twice…` |
| B4 | `AppUserController::userQuizDetails` | 남의 응시 ID·없는 ID 조회 시 500 | `first()` 결과 null 검사 없음 → 404가 맞음 | `AccessAndResultsTest` (현재 동작 500으로 고정) |
| B5 | `QuestionsController::scoreQuestion` | 채점 화면을 열기만 해도 검토 완료 | 화면 조회 시 `reviewed=true` 저장 | `ManualGradingTest` (현재 동작 고정) |
| B6 | `UserAnswer` + `user-answer.blade.php` | 선택형 문항의 정오(O/X)를 바꾸면 화면 갱신 시 오류. DB 저장은 됨 | `mount()`에서만 `user_answer`를 배열로 바꾸는데 다음 요청에서 모델을 DB에서 다시 읽어 문자열이 됨 → `in_array()` | `ManualGradingTest::test_changing_a_choice…` |
| B7 | `UserQuizlv::checkCurrentAnswer` | **일반형** 시험의 단답형(type 4) 문제 제출 시 오류 | 단답형 분기가 없어 `null` 반환 | `NormalQuizTest::test_short_answer_is_graded` |

**상태: B1~B7 모두 수정 완료(BF 단계).** 운영에서는 선택형·단답형 문제를 OMR형 시험에서만 쓰므로 B1·B2·B7은 실사용 영향이 없었다. B6은 선택형 문항에 정오 변경 UI가 없어 화면에서는 발생하지 않는 경로였다.

| ID | 위치 | 증상 | 원인 | 상태 |
|---|---|---|---|---|
| B8 | `UserQuizlv::getNextQuestion`, `getAllQuestions` | 사용 안 함(`is_active=0`) 문제도 출제됨. 문제 수(`quizSize`)는 사용 중인 문제만 세므로, 일반형은 비활성 문제가 섞이면 활성 문제가 출제되지 않을 수 있고, OMR형은 화면에 비활성 문제까지 나와 제출 조건이 어긋남 | 출제 쿼리에 `is_active` 조건 없음 | 수정 (BF-8) |

**B5 운영 영향:** 이제 관리자가 채점 화면에서 "검토 완료"를 눌러야 오늘의 채점 목록(`ShowTodayTesters`, `reviewed=0`만 표시)에서 빠진다. 배포 시 관리자에게 안내 필요.

## 9. 작업 로그

새 항목을 위에 추가한다. 형식: `날짜 | 작업 ID | 결과 | 메모`

| 날짜 | 작업 | 결과 | 메모 |
|---|---|---|---|
| 2026-11-10 | P3 | 완료 | Laravel 9.52, Vite 7, Tailwind 3.4. 테스트 55 통과, 브라우저 응시 흐름 확인. 중간 단계는 `--no-blocking` 필요(배포 금지) |
| 2026-11-10 | BF-8 | 완료 | B8 수정, 테스트 55 통과 |
| 2026-11-10 | P2 | 완료 | 차트 라이브러리 제거, Chart.js 4. 테스트 53 통과. `composer audit` 결과 17개 패키지에 보안 권고 60건 — P3~P6 업그레이드로 해소 예정, P7-1에서 재확인 |
| 2026-11-10 | BF | 완료 | B1~B7 수정, 테스트 51 통과. B5는 "검토 완료" 버튼 방식으로 변경. B8 신규 발견(D7) |
| 2026-11-10 | P1 | 완료 | 테스트 44 통과 / 5 미완료 / 7 건너뜀. 버그 B1~B7 발견(8장) |
| 2026-11-10 | P0-8 | 완료 | 23 통과 / 1 실패(역할 시드 없음, P1-0) / 7 건너뜀. `/login`, `/register`, `/` 200 확인 |
| 2026-11-10 | 발견 | 수정 | `vendor/laravel/framework/.../Validator.php`에 디버그용 `Log::warning($rule);`이 직접 삽입돼 있었음(`composer status`로 확인) → `composer reinstall laravel/framework`로 원복. 운영 서버 vendor에도 같은 수정이 있는지 P0-7에서 `composer status`로 확인 필요 |
| 2026-11-10 | 발견 | 수정 | 커밋된 `composer.lock`이 `composer.json`과 불일치(`pamitvn/*` 2개 누락) → `composer update pamitvn/chartisan-php pamitvn/consoletvs-charts`로 해당 패키지만 동기화. 이 과정에서 `consoletvs/charts 6.7`, `balping/json-raw-encoder`가 lock에서 빠짐. 둘 다 P2에서 제거 대상 |
| 2026-11-10 | P0-3 | 완료 | `artisan serve`에서 응답 멈춤 → `php -S`로 교체 |
| 2026-11-10 | P0-5 | 완료 | 접두사 인덱스 마이그레이션 1건 DB 종류 조건 추가 |
| 2026-11-10 | P0-1 | 확인 | 유지: `Dockerfile` 수정. 되돌림: `package-lock.json`. 삭제 대상(모두 vendor 기본값과 동일하거나 미사용): `sail`, `docker/8.0~8.2`(Sail 런타임), teams 마이그레이션 3개(teams 기능 비활성), `resources/js/Jetstream`·`Pages`(Inertia용 Vue 스텁, 앱은 Livewire 스택), `resources/views/errors`·`vendor/{livewire,mail,notifications,pagination}`·`public/vendor/livewire`·`routes/jetstream.php`·`config/{flare,ignition,tinker,trustedproxy}.php`(기본값과 동일), `config/livewire.office.php`(livewire.php의 미사용 변형), `composer.lock.bak`(2024-09 백업). 백업: 세션 스크래치패드 `d2-untracked-backup.tgz` |
| 2026-11-10 | D1·D2·D4 | 결정 | PHP 8.5.x / Laravel 13 / MySQL·MariaDB |
| 2026-11-10 | — | 계획 작성 | 코드 분석 기반. 실제 실행 검증은 아직 없음 |
