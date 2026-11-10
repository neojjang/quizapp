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
- **Ubuntu 서버에 직접 설치해 실행한다(D9).** Docker 이미지(`Dockerfile`)는 운영에 쓰지 않는다. 웹서버는 nginx + php-fpm으로 보이며(README의 nginx 설정 안내) P8-0에서 확인한다. 배포 절차는 5장 P8.
- 목표: **PHP 8.5.x 최신 보안 패치**(2026-11-10 기준 8.5.12), **Laravel 최신(13)**, DB는 MySQL 또는 MariaDB.
- Python 서술형 채점: `/home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py` (`config/services.php`의 `answer_checker` 기본값. 다르면 `.env`의 `ANSWER_CHECKER_*`)

## 3. 결정 필요 사항

| ID | 내용 | 권장 | 상태 |
|---|---|---|---|
| D1 | 최종 목표 버전 | — | **결정**: Ubuntu 최신, PHP 최신 보안 패치(8.5.x), Laravel 최신(13). P6에서 Laravel 13의 PHP 8.5 지원 여부를 composer로 확인 |
| D2 | 작업 트리의 미커밋 변경과 미추적 파일 처리 | — | **결정**: `Dockerfile` 수정(gd 확장 추가 등)은 유지·커밋, `package-lock.json` 이름 변경은 되돌림, 나머지 미추적 파일은 삭제(목록은 로그 참고) |
| D3 | 관리자 계정이 `user` 역할도 갖는지(차트 API 미들웨어가 `role:user`) | — | **해소**: P2에서 차트 API 자체를 없앰. 차트는 각 홈 화면 권한을 따름 |
| D4 | 운영 DB 종류·버전, 백업 방법 | — | **결정**: MySQL 또는 MariaDB. 정확한 버전·백업 방법은 P0-7에서 확인 |
| D6 | 8장 버그(B1~B7) 수정 시점 | — | **결정**: P2 전에 수정 완료(BF 단계). 선택형·단답형 문제는 운영에서 OMR형 시험에서만 사용 |
| D9 | 운영 배포 방식 | — | **결정**: Ubuntu에 직접 설치(PHP 8.5 패키지 + nginx + php-fpm). 절차는 P8 |
| D8 | 운영용 `Dockerfile` 처리 | — | **결정**: PHP 8.5·Laravel 13 기준으로 갱신 |
| D7 | B8(비활성 문제 출제) 수정 여부 | — | **결정**: 수정. 출제·채점 모두 사용 중인 문제만 |
| D5 | Livewire 최신 메이저(3 vs 4) 및 Jetstream 호환 버전 | — | **결정**: Livewire 3.8. Jetstream 5가 `^3.6.4`를 설치하고 4 지원을 선언하지 않음 |

## 4. 상태 요약

| 단계 | 내용 | 상태 |
|---|---|---|
| P0 | 준비: 작업 트리 정리, Docker+SQLite 개발 환경, 운영 정보 수집 | [~] P0-7 운영 정보 남음(P8-0과 같은 작업) |
| P1 | 회귀 테스트 작성 (업그레이드 전 기준선) | [x] |
| BF | 기존 버그 B1~B7 수정 (D6) | [x] |
| P2 | 차트 라이브러리 제거 → Chart.js 직접 사용 (Laravel 8 유지) | [x] |
| P3 | Laravel 8 → 9, Mix → Vite | [x] |
| P4 | Laravel 9 → 10 | [x] |
| P5 | Laravel 10 → 11, Livewire 2 → 3, Jetstream 2 → 5 | [x] |
| P6 | Laravel 11 → 12 → 13, PHP 8.5 | [x] |
| P7 | 정리: 남은 패키지 갱신, 채점 실행 보안 수정, 운영 Dockerfile, 최종 테스트 | [x] |
| P8 | 운영 배포 (Ubuntu 직접 설치, 절차는 5장 P8) | [ ] |

각 단계의 공통 완료 조건: **컨테이너에서 `php artisan test` 전부 통과 + 6장 수동 점검 목록 통과 + 로그 기록 + 커밋.**

---

## 5. 세부 작업

### P0. 준비

- [x] **P0-1** D2 처리: 미추적 파일 삭제 완료(사용자 실행, 2026-11-10). 작업 트리 깨끗함(`git status` 비어 있음), 삭제 후 테스트 66 통과·빈 DB 마이그레이션 성공·주요 화면과 404 페이지 정상
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

- [x] **P4-1** 개발 컨테이너 PHP 8.2(`compose.dev.yml` 기본값 변경, 8.2.35)
- [x] **P4-2** composer(`--no-blocking`): `laravel/framework 10.50.4`, `spatie/laravel-permission 6.25.0`, `phpunit 10.5.66`, `collision ^7`, `spatie/laravel-ignition ^2`, `sanctum ^3.3`
  - `laravel/jetstream`은 2.16.2 유지(Laravel 10 지원). Jetstream 3·4는 Laravel 10 전용이라 건너뛰고 P5에서 5로 올림
  - Spatie 6: 미들웨어 네임스페이스 `Middlewares` → `Middleware`(`app/Http/Kernel.php`)
- [x] **P4-3** 제거된 API 검색(`$dates`, `dispatchNow`, `Redirect::home` 등): 사용처 없음
- [x] **P4-4** `phpunit.xml`을 PHPUnit 10 형식으로 변환(`<coverage>` → `<source>`), 데이터 제공 메서드를 `static`으로, `.phpunit.cache` 무시. 경고(deprecation) 없음
- [x] **P4-5** 테스트 55 통과. Orca 브라우저: 관리자 홈·학생 홈(차트), 채점 화면, 오늘의 채점, 본인 결과(다른 학생 기록은 404), 달력 다음 달 이동(Livewire) 확인, 콘솔 오류 없음

### P5. Laravel 10 → 11, Livewire 3, Jetstream 5

이 단계가 가장 크다. 하위 작업 단위로 커밋한다.

- [x] **P5-1** D5 결정: **Livewire 3**(3.8.11). Jetstream 5.5.3 설치 명령이 `livewire/livewire:^3.6.4`를 요구하고 Livewire 4 지원은 선언되지 않음. Livewire 4는 Jetstream 지원 후 재검토
- [x] **P5-2** composer(`--no-blocking`): `laravel/framework 11.57.1`, `livewire 3.8.11`, `jetstream 5.5.3`, `fortify 1.41`, `sanctum 4.3.3`, `collision ^8`, `doctrine/dbal` 제거(Laravel 11은 컬럼 변경을 자체 지원). 세 패키지가 서로를 요구해 한 번에 올림
- [x] **P5-3** 기존 구조(`app/Http/Kernel.php` 등) 유지. Livewire 클래스 네임스페이스도 `App\Http\Livewire` 유지(`config/livewire.php`의 `class_namespace`)
- [x] **P5-4** Livewire 3 이전(컴포넌트 8개, 화면 확인 포함)
  - 설정: `config/livewire.php`를 v3 형식으로 교체하고 기존 값(네임스페이스, 레이아웃, mp3 100MB 업로드 규칙) 유지. `config/sanctum.php`는 v4 기본값(수정 없던 파일)
  - `wire:model` 의미 보존: 수식어 없음 → `.live`, `.defer` → 없음, `.lazy`(CSV 파일) → 없음, `.debounce` → `.live.debounce`
  - `emit` → `dispatch`(타이머 이벤트 3곳)
  - 값 없는 `<form wire:submit.prevent>` 4곳 → `x-on:submit.prevent`(Livewire 3에서는 빈 `$wire.` 호출 오류)
  - `{{ $this->id }}` → `{{ $this->getId() }}`(Livewire 3에서 제거된 속성. JS 주석 안이어도 Blade가 평가함)
  - 영작나열형 타이머 스크립트(가장 큰 변경):
    - Livewire 3는 갱신으로 나타난 `<script>`를 실행하지 않으므로 Livewire의 script 블록 디렉티브로 감쌈
    - `@this.x = v`(Livewire 3에서는 다음 요청까지 지연) → `$wire.set('x', v)`
    - 이 블록은 새 HTML이 DOM에 반영되기 전에 실행되므로 요소를 사용할 때마다 조회하도록 변경
    - 시험 시작 응답의 `timerRestart` 이벤트가 리스너 등록 전에 처리되므로, 첫 문제 타이머는 블록 안에서 `setTimeout(startTimer)`로 시작
    - 주의: JS 주석 안에 script 디렉티브 이름을 쓰면 Blade가 디렉티브로 컴파일해 출력 버퍼가 어긋나고 응답 JSON이 깨짐
  - Alpine: Livewire 3에 포함되므로 `app.js`에서 제거, 게스트 레이아웃에 `@livewireStyles`/`@livewireScripts` 추가
  - sortable 플러그인 `@nextapps-be/livewire-sortablejs` 0.2.0 → 0.4.1(Livewire 3용)
  - 테스트의 `TestableLivewire` → `Livewire\Features\SupportTesting\Testable`
- [x] **P5-5** Jetstream 5
  - `x-jet-*` 접두사 폐지: 컴포넌트를 Jetstream 5 스텁(`resources/views/components`)으로 교체하고 학원 로고 3종(`application-logo`, `application-mark`, `authentication-card-logo`) 유지. `resources/views/vendor/jetstream` 삭제. 앱 화면 32개의 `<x-jet-` → `<x-`
  - git 이력으로 실제 수정된 화면만 골라 보존: 로그인 화면의 회원가입 링크, 비밀번호 찾기 화면의 로그인 링크를 Jetstream 5 화면에 다시 적용. 수정 없던 인증·프로필·API 화면은 Jetstream 5 스텁으로 교체. 레이아웃·내비게이션 메뉴는 앱 것 유지
  - `config/jetstream.php`에 `auth_session`, `guard` 추가, `DeleteUser` 시그니처 갱신
  - Jetstream 기능 테스트를 Jetstream 5 스텁으로 교체(로그인 후 이동 경로는 앱의 `RouteServiceProvider::HOME`)
- [x] **P5-6** `realrashid/sweet-alert 7.3.2` 그대로 호환
- [x] **P5-7** 테스트 **56 통과** / 0 실패 / 7 건너뜀, deprecation 없음. Orca 브라우저 확인(모두 정상):
  - 일반형 응시(선택형·번역) → 100%
  - 영작나열형: 타이머 카운트다운·10초 미만 빨간색·시간 초과 시 재시도 버튼, 재시도 후 타이머 재시작, 순서 선택 완료 시 타이머 정지, 제출 → `retry=2`·`extra_info` 2회 기록
  - 구문 드래그 정렬(sortable) → 서버 순서 반영
  - 관리자: 사용자 검색, 수동 채점 정오 변경 → 점수 100→50, OMR 답지 출제·저장, 영작나열 문제 출제(단어 분할 → 저장), 듣기평가 출제 화면 렌더링·답지 생성
  - 프로필 이름 수정(Jetstream 5)
  - 미확인: 듣기평가 mp3 업로드(S3), 게스트 화면(로그인 등)의 Alpine 동작은 테스트로만 확인

### P6. Laravel 11 → 12 (→ 13)

- [x] **P6-1** Laravel 12.71(PHP 8.2): collision 8.6+가 PHPUnit 11을 요구해 `phpunit ^11.5`로 함께 올림. 데이터 제공자를 `#[DataProvider]` 속성으로 변경. **이 단계부터 보안 권고 없음**(`--no-blocking` 불필요). 커밋 `7b7dcfa`
- [x] **P6-2** 테스트 56 통과, deprecation 없음
- [x] **P6-3** **Laravel 13.40.0 + PHP 8.5.12**(컨테이너 기본값 8.5), `laravel/tinker 3.0.2`, `php ^8.3`
  - `--no-blocking` 없이 설치·`composer install` 성공, `composer audit` 보안 권고 없음, `composer validate` 통과
  - PHP 8.5: `PDO::MYSQL_ATTR_*` 상수 deprecated → `config/database.php`에서 8.5 이상은 `Pdo\Mysql::ATTR_*` 사용. `pdo_mysql`이 없을 때 MySQL 상수를 참조하던 `else` 분기도 `[]`로 정리
  - 테스트 56 통과, deprecation 없음
  - Orca 브라우저: 관리자 홈(차트 2), 학생 홈(차트 1), 사용자 목록, 프로필, 오늘의 채점, 채점 화면, OMR 출제 화면 렌더링·Livewire 로드, 영작나열형 응시(타이머·순서 선택·제출 → 100점)
  - 최신 메이저가 아닌 패키지(P7-1에서 처리): `guzzlehttp/guzzle 7→8`, `phpunit 11→13`, `realrashid/sweet-alert 7→8`, `spatie/laravel-permission 6→8`, `livewire 3→4`(D5로 보류)

### P7. 정리

- [x] **P7-1** 남은 구버전 갱신(보안 차단 우회 없이 설치, `composer audit` 권고 없음)
  - `guzzlehttp/guzzle 8.3.1`(앱에서 직접 사용 안 함, Laravel HTTP·AWS SDK 경유), `phpunit 13.4.6`
  - `spatie/laravel-permission 8.5.0`: 앱 설정에 수정이 없어 v8 기본 설정으로 교체(팀·이벤트 등 새 키 명시). 테이블 구조 변경 없음, 새 DB 마이그레이션·시드 정상
  - `realrashid/sweet-alert 8.0.0`: 설정 키가 snake_case로 바뀌고 화면이 새로 작성됨. 앱이 v4 시절 게시한 화면(`resources/views/vendor/sweetalert`)과 JS(`public/vendor/sweetalert/sweetalert.all.js`)를 삭제하고 v8 설정·JS로 교체. 유일한 수정 사항이던 알림 닫힘 시간 3초는 유지. SweetAlert2 본체는 v8 기본값대로 CDN(jsdelivr)에서 로드
  - `livewire 3→4`: D5에 따라 보류
- [x] **P7-2** `minimum-stability: dev` → `stable`
- [x] **P7-3** Python 채점(**보안 버그 B10 수정**)
  - 기존 코드는 `escapeshellarg()` 결과를 다시 큰따옴표로 감싸 `shell_exec` → 큰따옴표 안의 `$(...)`·백틱이 실행됨. 학생 답안 `$(echo INJECTED-$((6*7)))`이 `INJECTED-42`로 실행되는 것을 컨테이너에서 확인
  - `Process::run([...])`(셸 없이 인자 배열)로 교체. 스크립트가 받는 인자는 기존과 같은 형태(작은따옴표로 감싼 문자열)를 유지해 채점 결과가 바뀌지 않게 함 — 가짜 채점 스크립트로 기존/새 방식의 인자가 같음을 확인
  - 경로를 `config/services.php`의 `answer_checker`로(기본값은 기존 운영 경로라 운영 `.env` 수정 불필요), 제한 시간 30초, 실패 시 오류 기록 후 오답(0점) 처리
  - `AnswerCheckerTest`: 점수 구간별 정답/보류/오답, 셸 없이 인자 전달, 실패 시 오답, 완전 일치 시 스크립트 미실행
- [x] **P7-4** 운영용 `Dockerfile`을 PHP 8.5 FPM + Laravel 13 기준으로 재작성(D8)
  - 기존 문제: `COPY . /app`이 `.env`(비밀 값)까지 이미지에 넣음, `--ignore-platform-reqs`로 PHP 버전 검사 생략, 쓰지 않는 확장·패키지(imagick, memcached, pgsql 라이브러리, openssh-server 등), `storage` 777 권한, dev 패키지 포함
  - 변경: `.dockerignore` 추가(`.env`, `vendor`, `node_modules`, 로그·캐시, SQLite 파일, 테스트 등 제외), 실행할 PHP 8.5에서 `composer install --no-dev`(의존성 레이어 캐시), 필요한 확장만(pdo_mysql, mysqli, zip, bcmath, gd, intl, exif + 기본 내장), 운영 `php.ini`·OPcache, 업로드 128M, Asia/Seoul, `storage`·`bootstrap/cache`는 `www-data` 소유, `public/build`는 커밋된 결과 사용(Node 불필요). `config:cache`는 빌드 시점 환경을 고정하므로 컨테이너 시작 후 실행
  - 검증: 이미지 빌드 → PHP 8.5.12·확장·ini 확인, `.env`·`vendor/phpunit` 미포함 확인 → php-fpm 컨테이너 + nginx 컨테이너로 실행, SQLite로 `migrate --seed`·`config/route/view:cache` 성공 → `/login` 200, 참조하는 CSS·JS·`livewire.min.js` 모두 200, 로그인 POST 302 → 관리자 홈 200(차트 2개). 운영 모드에서는 Livewire가 `livewire.min.js`를 사용
  - README의 Docker 안내를 운영 이미지 기준으로 교체
- [x] **P7-5** `README.md`에 개발 환경(Docker + SQLite)·채점 스크립트 설정 추가, sortable 버전 갱신
- [x] **P7-6** 최종 테스트
  - 자동: 테스트 **66 통과** / 0 실패 / 7 건너뜀, deprecation·경고 없음, `composer audit`·`validate` 통과, 빈 DB에서 `migrate:fresh --seed` 성공
  - Orca 브라우저(학생 계정으로 로그인): 로그인 화면(Alpine·Livewire 로드, 회원가입 링크), 학생 홈 차트, 관리자 화면 403, OMR 응시 100%, 일반형 응시 50%(선택 정답·번역 오답), 영작나열형 시간 초과→재시도→제출, sweet-alert 토스트(3초 후 닫힘)
  - 이 과정에서 발견해 수정: **R1** OMR·듣기평가 제출 실패(Livewire 3 회귀, 아래), **B11** 시간 초과로 일부만 고른 영작나열 답이 정답 처리
- [x] **P7-7** 배포 전 코드 리뷰(Codex) 반영: B12~B15 수정
  - B12: 체크박스 값은 보기 ID만 보내고, `checkChoiceAnswer`는 이 문제의 보기 ID만 남겨 중복을 없앤 뒤 DB의 정답 ID 집합과 비교. OMR은 체크한 보기 순번으로 ID를 찾음(값 무시). `user_answer` 저장 형식("보기ID,보기ID")은 그대로라 기존 데이터·결과 화면 영향 없음
  - B13: `nextQuestion`·`checkAllAnswers`의 저장을 트랜잭션으로 묶고, 응시 행(`quiz_headers`)을 `lockForUpdate`로 잠근 뒤 이미 저장된 답안이 있으면 무시. OMR은 전 문항 채점(Python 포함)을 먼저 끝낸 뒤 한 번에 저장. **새 마이그레이션 없음**(유니크 제약은 운영 DB에 B3 중복 행이 있으면 실패할 수 있어 넣지 않음)
  - B14: `ProcessTimedOutException`을 잡아 오류 기록 후 0점
  - 테스트: 정답 미노출, 조작 값(옛 형식·다른 문제 ID·중복 ID), 재전송 무시(일반형·OMR), 채점 중 예외 시 저장 없음, 범위 밖 보기 순번, 실제 프로세스 시간 초과, 결과 화면 복수 선택 ✓. 새 테스트는 수정 전 코드에서 실패 확인. **74 통과** / 7 건너뜀
  - Orca 브라우저: OMR 신규 응시(복수 정답 포함) 66.67%·체크박스 값이 보기 ID만, 결과 상세 화면 ✓ 표시, 일반형 응시 100%·HTML에 정답 표시 없음·"결과 보기" 더블클릭 시 기록 2건(중복 없음)

### P8. 운영 배포 (Ubuntu 직접 설치)

**방식 요약**
- 기존 운영 폴더와 기존 PHP는 **그대로 두고**, PHP 8.5와 새 릴리스 폴더를 **옆에 따로 준비**한다.
- 점검 시간에 nginx가 가리키는 폴더와 php-fpm 소켓만 바꿔서 전환한다.
- 문제가 생기면 nginx 설정만 되돌려 몇 분 안에 원래 상태로 돌아간다.
- 이번 업그레이드는 DB 구조를 바꾸지 않는다. `migrate`는 "Nothing to migrate"여야 한다. 그래서 되돌릴 때도 DB 복원은 필요 없다.

**단계별 서비스 영향**

| 단계 | 내용 | 서비스 영향 |
|---|---|---|
| 8-0 | 서버 정보 수집, 변수 확정 | 없음 |
| 8-1 | PHP 8.5 병행 설치·설정 | 없음 |
| 8-2 | Composer 갱신 | 없음 |
| 8-3 | 새 릴리스 폴더 준비 | 없음 |
| 8-4 | DB 백업, 운영 DB로 사전 검증, 임시 포트로 미리 보기 | 없음 |
| 8-5 | 전환(점검 모드) | **5~10분 중단** |
| 8-6 | 배포 후 점검 | — |
| 8-7 | 되돌리기(필요할 때만) | 수 분 |
| 8-8 | 정리(1~2주 후) | 없음 |

각 단계의 명령 결과(버전, 경로, 출력)는 9장 작업 로그에 남긴다. 아래 명령은 `ubuntu` 계정에서 `sudo`로 실행한다고 가정한다.

#### 8-0. 사전 준비 (배포 전날까지)

- [ ] **P8-0-1** 서버 정보를 수집해 아래 변수를 채운다.
  ```sh
  lsb_release -a                                   # Ubuntu 버전
  php -v; ls /etc/php/                             # 현재 PHP 버전(OLD_PHP), 설치된 버전들
  systemctl list-units --type=service | grep -E 'php.*fpm|nginx|apache|mysql|mariadb'
  nginx -v; sudo nginx -T 2>/dev/null | grep -nE 'server_name|root |fastcgi_pass|client_max_body_size'   # 사이트 설정 파일, root, 소켓
  mysql --version                                  # MySQL/MariaDB 버전
  sudo crontab -l; crontab -l; ls /etc/supervisor/conf.d 2>/dev/null   # 스케줄러·큐 워커 사용 여부(코드상으로는 없음)
  df -h /                                          # 새 릴리스 + 백업용 여유 공간(최소 2GB 권장)
  ```
- [ ] **P8-0-2** 현재 운영 폴더의 상태를 기록한다. 되돌리기와 비교 기준으로 쓴다.
  ```sh
  cd <OLD_DIR>
  git remote get-url origin; git branch --show-current; git log -1 --oneline; git status --short
  composer status            # vendor 직접 수정 여부(개발 환경에서는 Validator.php 수정이 있었음)
  grep -E '^(APP_ENV|APP_DEBUG|APP_URL|DB_CONNECTION|SESSION_DRIVER|CACHE_DRIVER|QUEUE_CONNECTION|FILESYSTEM_DRIVER|AWS_BUCKET)=' .env   # 값 확인(비밀번호·키는 출력하지 않음)
  ls -la public/storage storage/app/public 2>/dev/null   # 프로필 사진 등 로컬 저장 파일
  ```
  - `git remote get-url origin`에 토큰(`ghp_…`)이 들어 있으면 **배포 전에** README의 "Git 원격 주소에서 토큰 제거" 절차로 정리한다(토큰 폐기 → 읽기 전용 Deploy key로 교체). P8-3-1은 이 주소로 새 릴리스를 받으므로, 정리하지 않으면 새 폴더에도 토큰이 따라간다.
- [ ] **P8-0-3** 변수를 확정해 기록한다(아래는 예시).

  | 변수 | 의미 | 예시 |
  |---|---|---|
  | `OLD_DIR` | 현재 운영 폴더 | `/home/ubuntu/dongwon/quizapp` |
  | `NEW_DIR` | 새 릴리스 폴더(같은 상위 폴더) | `/home/ubuntu/dongwon/quizapp-20261110` |
  | `OLD_PHP` | 현재 PHP 버전 | `8.0` |
  | `OLD_SOCK` | 현재 php-fpm 소켓(nginx `fastcgi_pass`) | `unix:/run/php/php8.0-fpm.sock` |
  | `NEW_SOCK` | PHP 8.5 php-fpm 소켓 | `unix:/run/php/php8.5-fpm.sock` |
  | `SITE_CONF` | nginx 사이트 설정 파일 | `/etc/nginx/sites-available/quizapp` |
  | `FPM_USER` | 현재 php-fpm pool 실행 계정 | `www-data` |
  | `DB_NAME`, `DB_USER` | 운영 DB 이름·계정 | `.env` 값 |
  | `RELEASE_REF` | 배포할 git 태그 | `v2026.11.10` |

- [ ] **P8-0-4** 배포할 코드를 확정한다.
  - `upgrade/laravel`을 운영이 쓰는 브랜치(P8-0-2의 `git branch --show-current`)에 병합한다. 그 뒤 개발 컨테이너에서 `php artisan test`가 전부 통과하는지 확인한다.
  - 태그 `RELEASE_REF`를 만들어 원격에 올린다.
  - P3~P5 커밋은 보안 권고 우회(`--no-blocking`)가 필요한 중간 단계라 배포 대상이 아니다. P6 이후 커밋만 배포한다.
- [ ] **P8-0-5** 사용자에게 알릴 내용을 정리한다.
  - **점검 공지:** 점검 시간(5~10분)을 공지한다.
  - **관리자 안내:**
    - B5: 채점 화면을 여는 것만으로는 검토 완료가 되지 않는다. "검토 완료" 버튼을 눌러야 오늘의 채점 목록에서 빠진다.
    - B11: 영작나열형에서 시간 초과로 일부 구문만 고른 답은 오답 처리된다.
  - **네트워크 조건:** 알림창(SweetAlert2)은 외부 CDN(jsdelivr)에서 불러온다. 학생 PC와 서버에서 외부 접속이 가능해야 한다.

#### 8-1. PHP 8.5 병행 설치 (서비스 영향 없음)

기존 PHP와 php-fpm은 지우지 않는다. 버전별 패키지(`php8.5-*`)라서 서로 충돌하지 않는다.

- [ ] **P8-1-1** 저장소에 PHP 8.5가 있는지 확인한다.
  ```sh
  sudo apt update
  apt-cache policy php8.5-fpm
  ```
  - 후보(Candidate) 버전이 있으면 Ubuntu 공식 저장소를 쓴다. Ubuntu는 보안 수정을 버전 번호를 올리지 않고 패키지 개정판(`-…ubuntu…`)으로 내보낸다. 그래서 최신 보안 패치 여부는 `apt` 업데이트 기준으로 판단한다.
  - 후보가 없으면 PHP 패키지 관리자(Ondřej Surý)의 PPA를 추가한다. PPA는 PHP 공식 최신 패치 버전(2026-11-10 기준 8.5.12 이상)을 따라간다.
    ```sh
    sudo apt install -y software-properties-common
    sudo add-apt-repository -y ppa:ondrej/php     # nginx도 같은 관리자의 PPA를 쓰고 있다면 ppa:ondrej/nginx 유지
    sudo apt update
    apt-cache policy php8.5-fpm
    ```
- [ ] **P8-1-2** PHP 8.5와 필요한 확장을 설치한다. 개발 컨테이너·운영 이미지와 같은 구성이다.
  ```sh
  sudo apt install -y php8.5-fpm php8.5-cli php8.5-common php8.5-mysql php8.5-mbstring php8.5-xml \
      php8.5-curl php8.5-zip php8.5-gd php8.5-intl php8.5-bcmath php8.5-readline
  php8.5 -v
  php8.5 -m | grep -iE '^(pdo_mysql|mysqli|mbstring|xml|dom|curl|zip|gd|intl|bcmath|exif|fileinfo|tokenizer|ctype|openssl|Zend OPcache)$'
  ```
  - `exif`, `fileinfo`, `tokenizer`, `ctype`는 `php8.5-common`에 들어 있다.
  - OPcache는 PHP 8.5부터 기본 포함이다. 목록에 없으면 `apt-cache search php8.5-opcache`로 패키지를 찾아 설치한다.
  - 기존 PHP에 설치된 확장 중 위 목록에 없는 것이 있는지 비교한다: `diff <(php<OLD_PHP> -m) <(php8.5 -m)`. 앱은 위 확장만 쓴다.
- [ ] **P8-1-3** PHP 설정을 기존 운영 값과 맞춘다.
  ```sh
  # 기존 fpm에서 바꿔 쓰던 값 확인
  grep -vE '^\s*(;|$)' /etc/php/<OLD_PHP>/fpm/php.ini | grep -E 'upload_max_filesize|post_max_size|memory_limit|max_execution_time|date.timezone'
  ls /etc/php/<OLD_PHP>/fpm/conf.d/ | grep -vE '^[0-9]{2}-'      # 직접 추가한 ini 파일

  # 앱 설정 파일 (fpm·cli 공통) — 듣기평가 mp3 100MB 업로드
  printf '%s\n' 'upload_max_filesize = 128M' 'post_max_size = 128M' 'date.timezone = Asia/Seoul' \
      | sudo tee /etc/php/8.5/mods-available/quizapp.ini
  sudo phpenmod -v 8.5 quizapp
  php8.5 -i | grep -E '^(upload_max_filesize|post_max_size|date.timezone|opcache.enable) '
  ```
  - 기존 php.ini에서 바꿔 쓰던 다른 값(`memory_limit` 등)이 있으면 같은 파일에 추가한다.
- [ ] **P8-1-4** php-fpm pool을 기존과 같은 계정·프로세스 설정으로 맞춘다.
  ```sh
  diff <(grep -vE '^\s*(;|$)' /etc/php/<OLD_PHP>/fpm/pool.d/www.conf) <(grep -vE '^\s*(;|$)' /etc/php/8.5/fpm/pool.d/www.conf)
  ```
  - `user`, `group`, `listen.owner`, `listen.group`, `pm.*` 값이 다르면 8.5 쪽을 기존 값으로 고친다. 소켓 경로(`listen`)는 8.5 기본값(`/run/php/php8.5-fpm.sock`)을 그대로 둔다.
  ```sh
  sudo php-fpm8.5 -t
  sudo systemctl enable --now php8.5-fpm
  systemctl status php8.5-fpm --no-pager; ls -la /run/php/php8.5-fpm.sock
  ```
- [ ] **P8-1-5** 기본 `php` 명령은 아직 바꾸지 않는다. 8-5 전환 때 바꾼다. 그 전까지 새 릴리스 작업에는 `php8.5`를 명시해서 쓴다.

#### 8-2. Composer 갱신 (서비스 영향 없음)

- [ ] **P8-2-1** Composer 2.11 이상을 준비한다. 2.11부터 보안 권고가 있는 버전을 설치 단계에서 막는다.
  ```sh
  composer --version
  sudo composer self-update --2          # composer가 /usr/local/bin/composer 등 PHAR로 설치된 경우
  # apt로 설치된 composer라면 버전이 낮을 수 있음 → https://getcomposer.org/download/ 의 설치 스크립트로 /usr/local/bin/composer 설치
  php8.5 $(command -v composer) --version
  ```

#### 8-3. 새 릴리스 폴더 준비 (서비스 영향 없음)

기존 폴더를 고치지 않고 새로 받는다. 기존 폴더에 남아 있을 수 있는 것들이 새 릴리스에 섞이지 않는다.
- 미추적 파일: 개발 환경에서는 Livewire 2 자산 `public/vendor/livewire`가 Livewire 3를 망가뜨렸다.
- `vendor` 직접 수정.

- [ ] **P8-3-1** 코드를 받는다.
  ```sh
  git clone --branch <RELEASE_REF> --depth 1 $(git -C <OLD_DIR> remote get-url origin) <NEW_DIR>
  cd <NEW_DIR> && git log -1 --oneline
  ```
- [ ] **P8-3-2** 환경 설정과 로컬 저장 파일을 옮긴다.
  ```sh
  cp <OLD_DIR>/.env <NEW_DIR>/.env && chmod 640 <NEW_DIR>/.env
  rsync -a <OLD_DIR>/storage/app/ <NEW_DIR>/storage/app/              # 프로필 사진(public 디스크) 등
  # SESSION_DRIVER=file 인 경우에만: 로그인 상태 유지
  # rsync -a <OLD_DIR>/storage/framework/sessions/ <NEW_DIR>/storage/framework/sessions/
  ```
  - **`.env` 키 이름은 바꾸지 않는다.** 앱의 기존 설정 파일(`CACHE_DRIVER`, `FILESYSTEM_DRIVER` 등)을 유지했기 때문이다.
  - `APP_ENV=production`, `APP_DEBUG=false`인지 확인한다.
  - Python 채점 경로가 기본값(`/home/ubuntu/venv/bin/python3`, `/home/ubuntu/dongwon/konlpy/check_answer.py`)과 다를 때만 `ANSWER_CHECKER_PYTHON`, `ANSWER_CHECKER_SCRIPT`를 추가한다.
- [ ] **P8-3-3** 의존성을 설치하고 권한을 설정한다. 프론트엔드 빌드 결과(`public/build`)는 저장소에 들어 있어서 Node·npm이 필요 없다.
  ```sh
  cd <NEW_DIR>
  php8.5 $(command -v composer) install --no-dev --optimize-autoloader --no-interaction
  php8.5 $(command -v composer) audit          # "No security vulnerability advisories found"
  php8.5 artisan storage:link
  sudo chown -R <FPM_USER>:<FPM_USER> storage bootstrap/cache
  sudo chmod -R ug+rwX storage bootstrap/cache
  php8.5 artisan about                          # Laravel 13.x, PHP 8.5.x, 환경·드라이버 값 확인
  ```
- [ ] **P8-3-4** Python 채점 스크립트가 새 호출 방식에서도 같은 점수를 내는지 확인한다.
  - 새 코드도 인자를 기존과 같은 형태(작은따옴표로 감싼 문자열)로 넘긴다.
  ```sh
  /home/ubuntu/venv/bin/python3 /home/ubuntu/dongwon/konlpy/check_answer.py "'나는 학교에 가는 소년이다'" "'나는 학교에 걸어가는 소년입니다'"
  ```
  - 숫자(0~1) 하나가 출력되면 정상이다.
  - 같은 입력으로 기존 운영 화면에서 채점한 결과(정답/보류/오답)가 있으면 비교한다.
  - `FPM_USER` 계정이 이 Python과 스크립트를 실행할 수 있어야 한다: `sudo -u <FPM_USER> /home/ubuntu/venv/bin/python3 -c 'print(1)'`.

#### 8-4. DB 백업과 사전 검증 (서비스 영향 없음)

- [ ] **P8-4-1** 운영 DB를 백업하고 파일을 검증한다.
  ```sh
  mkdir -p ~/backup
  mysqldump --single-transaction --routines --triggers --default-character-set=utf8mb4 \
      -u <DB_USER> -p <DB_NAME> | gzip > ~/backup/quizapp-$(date +%F-%H%M).sql.gz
  # MariaDB는 mariadb-dump 도 같은 옵션
  gzip -t ~/backup/quizapp-*.sql.gz && ls -lh ~/backup/
  ```
  - 처음 한 번은 복원도 연습한다. 별도 DB를 만들어 가져온 뒤 건수를 비교한다.
  ```sh
  mysql -u root -p -e 'CREATE DATABASE quizapp_restore_test CHARACTER SET utf8mb4'
  gunzip -c ~/backup/<파일>.sql.gz | mysql -u root -p quizapp_restore_test
  mysql -u root -p -e 'SELECT COUNT(*) FROM quizapp_restore_test.quizzes; SELECT COUNT(*) FROM <DB_NAME>.quizzes'
  mysql -u root -p -e 'DROP DATABASE quizapp_restore_test'
  ```
- [ ] **P8-4-2** 새 릴리스로 운영 DB를 **읽기만** 해서 검증한다.
  ```sh
  cd <NEW_DIR>
  php8.5 artisan migrate:status | tail -5          # 모든 마이그레이션 Ran
  php8.5 artisan migrate --pretend                 # 기대값: "Nothing to migrate"
  php8.5 artisan tinker --execute='echo App\Models\User::count()," users, tz=",DB::selectOne("select @@session.time_zone as tz")->tz,"\n";'
  ```
  - `migrate --pretend`가 실행할 SQL을 보여주면 **중단하고 검토한다.** 이번 업그레이드에는 새 마이그레이션이 없다.
  - 시간대는 `Asia/Seoul`로 나와야 한다(`config/database.php`의 연결 옵션).
- [ ] **P8-4-3** (권장) 임시 포트로 새 릴리스를 미리 본다. 운영 DB를 쓰므로 **화면 보기·로그인까지만** 하고, 응시나 채점처럼 데이터를 쓰는 동작은 하지 않는다.
  ```nginx
  # /etc/nginx/sites-available/quizapp-preview  (예: 8081 포트, 외부 방화벽에서 막힌 포트 사용)
  server {
      listen 8081;
      server_name _;
      root <NEW_DIR>/public;
      index index.php;
      client_max_body_size 128M;
      location / { try_files $uri $uri/ /index.php?$query_string; }
      location ~ \.php$ {
          include snippets/fastcgi-php.conf;
          fastcgi_pass unix:/run/php/php8.5-fpm.sock;
      }
  }
  ```
  ```sh
  sudo ln -s /etc/nginx/sites-available/quizapp-preview /etc/nginx/sites-enabled/
  sudo nginx -t && sudo systemctl reload nginx
  curl -sI http://127.0.0.1:8081/login | head -1        # 200
  ```
  - SSH 터널(`ssh -L 8081:127.0.0.1:8081 ubuntu@<서버>`)로 브라우저에서 확인한다.
  - 운영 환경에서는 앱이 https로 리다이렉트한다. 임시 포트에서 로그인 후 이동이 https로 바뀌면 주소를 직접 고쳐 확인한다.
  - 확인이 끝나면 미리 보기 사이트를 내린다: `sudo rm /etc/nginx/sites-enabled/quizapp-preview && sudo systemctl reload nginx`.

#### 8-5. 전환 (점검 시간, 5~10분 중단)

- [ ] **P8-5-1** 기존 앱을 점검 모드로 바꾼다. 기존 PHP 버전으로 실행한다.
  ```sh
  cd <OLD_DIR> && php artisan down --retry=60
  ```
- [ ] **P8-5-2** 최종 DB 백업(8-4-1의 `mysqldump`)을 다시 받는다. 점검 직전까지 쌓인 응시 기록을 담기 위해서다.
- [ ] **P8-5-3** 새 릴리스에서 마이그레이션(기대: 변경 없음)과 캐시를 만든다.
  ```sh
  cd <NEW_DIR>
  php8.5 artisan migrate --force
  php8.5 artisan optimize          # config, route, view, event 캐시
  ```
- [ ] **P8-5-4** nginx를 새 릴리스와 PHP 8.5로 바꾼다.
  ```sh
  sudo cp <SITE_CONF> ~/backup/nginx-site-before-upgrade.conf
  sudo sed -i 's#<OLD_DIR>/public#<NEW_DIR>/public#; s#<OLD_SOCK>#<NEW_SOCK>#' <SITE_CONF>
  grep -nE 'root |fastcgi_pass|client_max_body_size' <SITE_CONF>     # 바뀐 값과 128M 확인
  sudo nginx -t && sudo systemctl reload nginx
  sudo systemctl reload php8.5-fpm
  ```
- [ ] **P8-5-5** 기본 `php` 명령을 8.5로 바꾼다. 이후 서버에서 실행하는 `php artisan …`이 8.5로 돈다.
  ```sh
  sudo update-alternatives --set php /usr/bin/php8.5 && php -v | head -1
  ```
  - P8-0-1에서 cron 등으로 `php`를 쓰는 다른 작업이 있었다면 함께 확인한다.
- [ ] **P8-5-6** 전환 직후 바로 확인한다.
  ```sh
  curl -sI https://<도메인>/login | head -1
  sudo tail -n 20 /var/log/nginx/error.log
  tail -n 50 <NEW_DIR>/storage/logs/laravel*.log
  ```
  기존 폴더는 점검 모드인 채로 둔다. nginx가 더 이상 가리키지 않으므로 영향이 없다.

#### 8-6. 배포 후 점검

- [ ] **P8-6-1** 6장 수동 점검 목록 1~9를 운영 화면에서 확인한다. 테스트용 학생 계정으로 응시하고, 끝나면 그 응시 기록을 지운다. 특히 다음을 본다.
  - 학생: OMR형 제출(R1), 일반형, 영작나열형(타이머·재시도·드래그), 듣기평가(mp3 재생)
  - 서술형 번역 답안을 10자 넘게, 정답과 다르게 입력해 Python 채점이 정답/보류/오답 중 하나로 처리되는지(9번)
  - 관리자: 채점 화면 "검토 완료" 버튼과 알림창, 수동 정오 변경 시 점수 재계산, 관리자 홈 차트, 듣기평가 mp3 업로드(S3)
  - 브라우저 개발자 도구 콘솔에 오류가 없고, 스크립트가 `/livewire/livewire.min.js`로 로드되는지
- [ ] **P8-6-2** 하루 동안 로그를 지켜본다: `tail -f <NEW_DIR>/storage/logs/laravel*.log /var/log/nginx/error.log /var/log/php8.5-fpm.log`
- [ ] **P8-6-3** 결과를 9장 작업 로그에 기록하고, 관리자에게 B5·B11 변경을 안내했는지 확인한다.

#### 8-7. 되돌리기 (문제가 있을 때)

DB 구조가 바뀌지 않았으므로 **보통은 DB를 복원하지 않는다.** 새 버전에서 쌓인 응시 기록은 기존 앱에서도 그대로 읽힌다.

- [ ] **P8-7-1** nginx를 기존 폴더와 기존 php-fpm으로 되돌린다.
  ```sh
  sudo cp ~/backup/nginx-site-before-upgrade.conf <SITE_CONF>
  sudo nginx -t && sudo systemctl reload nginx
  sudo update-alternatives --set php /usr/bin/php<OLD_PHP>
  cd <OLD_DIR> && php artisan up
  ```
- [ ] **P8-7-2** DB 복원은 데이터가 잘못 저장된 경우에만 한다. 복원하면 백업 이후의 응시 기록이 사라진다.
  ```sh
  gunzip -c ~/backup/<최종 백업>.sql.gz | mysql -u <DB_USER> -p <DB_NAME>
  ```
- [ ] **P8-7-3** 원인을 9장 작업 로그에 기록하고, 개발 환경에서 재현·수정한 뒤 8-3부터 다시 진행한다.

#### 8-8. 정리 (안정화 1~2주 후)

- [ ] **P8-8-1** 기존 폴더를 보관용으로 압축한 뒤 정리한다: `tar czf ~/backup/quizapp-old-release.tgz -C <OLD_DIR>/.. $(basename <OLD_DIR>)`
- [ ] **P8-8-2** 기존 PHP가 다른 용도로 쓰이지 않는지 확인한 뒤 제거한다.
  ```sh
  sudo systemctl disable --now php<OLD_PHP>-fpm
  sudo apt purge 'php<OLD_PHP>-*' && sudo apt autoremove
  ```
- [ ] **P8-8-3** 이후 보안 패치를 정기적으로 적용한다.
  ```sh
  sudo apt update && apt list --upgradable 2>/dev/null | grep php8.5
  sudo apt upgrade -y 'php8.5-*' && sudo systemctl reload php8.5-fpm && php -v | head -1
  ```
  - Ubuntu 공식 저장소를 쓰면 `unattended-upgrades`가 보안 업데이트를 자동으로 적용한다.
  - PPA를 쓰면 자동 업데이트 대상에 넣거나 월 1회 수동으로 적용한다.
  - 앱 의존성은 개발 환경에서 `composer update`와 테스트를 거친 뒤 새 릴리스로 배포한다(8-3~8-5).

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

| B9 | `make-test-answer-form`, `make-listening-test-form` 뷰 | 선택형 보기 번호 "1"을 클릭하면 2번 보기가 체크됨 | 첫 `<label for>`가 `answer.1`을 가리킴 | 수정 (P5) |

| B10 | `UserQuizlv::checkUserAnswer` | **보안**: 서술형 답안에 `$(명령)`을 넣으면 서버에서 실행됨 | `escapeshellarg` 결과를 큰따옴표로 다시 감싸 셸이 치환 | 수정 (P7-3) |
| B11 | `UserQuizlv::checkCurrentAnswer` | 영작나열형에서 시간 초과로 일부 구문만 고른 답이 정답 처리 | 구문별 정오는 전부 골랐을 때만 판정되는데, 일부만 고르면 모두 "통과" 상태로 남음 | 수정 (P7-6): 모든 구문을 골라야 정답 |
| B12 | `UserQuizlv::checkChoiceAnswer`, `user-quizlv.blade.php` | **보안**: 선택형 보기 값에 정답 여부가 들어 있어 페이지 소스로 정답이 보이고, 값을 `오답ID,1`로 바꿔 보내면 정답 처리 | 채점이 브라우저가 보낸 `is_checked`를 그대로 신뢰 | 수정 (P7-7) |
| B13 | `UserQuizlv::nextQuestion`, `checkAllAnswers` | 동시·재전송 요청은 B3 가드(`quizInProgress`)를 통과해 중복 저장. OMR은 중간 문항 예외 시 앞 문항만 저장 | 가드가 브라우저가 들고 있는 컴포넌트 상태, 트랜잭션 없음 | 수정 (P7-7) |
| B14 | `UserQuizlv::similarityScore` | Python 채점 시간 초과 시 0점 처리 대신 제출 실패(P7-3 의도와 다름) | `Process::run()`은 시간 초과 시 예외를 던져 `failed()` 검사에 도달하지 않음 | 수정 (P7-7) |
| B15 | `appusers/userQuizDetail.blade.php` | 학생 결과 화면에서 복수 선택한 보기에 ✓ 표시가 없음 | `explode(",/", …)`는 ",/"를 하나의 구분자로 취급 | 수정 (P7-7, 브라우저 확인 중 발견) |
| R1 | `UserQuizlv::checkAllAnswers` | **Livewire 3 회귀**: OMR·듣기평가에서 선택형 문항이 있으면 제출이 실패 | Livewire 2는 단일 체크박스에 `value`("보기id,정답여부")를 넣었지만 Livewire 3는 `true`를 넣음. P5 테스트는 옛 형식 값을 직접 넣어서 놓침 | 수정 (P7-6): `true`면 보기 순번으로 값 생성. Livewire 3 형식 테스트 추가 |

**B5 운영 영향:** 이제 관리자가 채점 화면에서 "검토 완료"를 눌러야 오늘의 채점 목록(`ShowTodayTesters`, `reviewed=0`만 표시)에서 빠진다. 배포 시 관리자에게 안내 필요.

## 9. 작업 로그

새 항목을 위에 추가한다. 형식: `날짜 | 작업 ID | 결과 | 메모`

| 날짜 | 작업 | 결과 | 메모 |
|---|---|---|---|
| 2026-11-10 | P7-7 | 완료 | Codex 리뷰 3건 반영(B12 정답 노출·조작, B13 재전송·부분 저장, B14 채점 시간 초과) + 브라우저 확인 중 B15 발견·수정. 새 마이그레이션 없음. 테스트 74 통과, Orca 브라우저로 OMR·일반형 응시 확인 |
| 2026-11-10 | 보안 | 문서화 | 원격 주소에 GitHub 토큰이 포함된 것을 발견(개발 PC). README에 토큰 제거·Deploy key 절차 추가, P8-0-2에 운영 서버 확인 항목 추가. 토큰 폐기는 사용자 조치 |
| 2026-11-10 | 병합 | 완료 | `upgrade/laravel` → `develop` 병합(병합 커밋 `cd76b6a`, `--no-ff`). develop에서 테스트 66 통과. 원격 push는 하지 않음 |
| 2026-11-10 | P0-1 | 완료 | 미추적 파일 삭제 확인. 테스트 66 통과, 빈 DB 마이그레이션·시드 성공, 브라우저 확인 |
| 2026-11-10 | P8 | 절차 작성 | D9(Ubuntu 직접 설치) 반영. 서버 정보(8-0) 수집 후 진행 |
| 2026-11-10 | P7-4 | 완료 | 운영 Dockerfile PHP 8.5 FPM 재작성, `.dockerignore`로 `.env` 유출 차단. php-fpm+nginx로 실제 실행·로그인 확인 |
| 2026-11-10 | P7 | 완료(P7-4 제외) | 남은 패키지 최신화, 보안 버그 B10 수정, 최종 테스트 중 R1(OMR 제출 실패)·B11 발견·수정. 테스트 66 통과 |
| 2026-11-10 | P6 | 완료 | Laravel 13.40, PHP 8.5.12. 보안 권고 없이 설치. 테스트 56 통과, 브라우저 확인. **이 커밋부터 배포 후보**(P7·P8 남음) |
| 2026-11-10 | P5 | 완료 | Laravel 11.57, Livewire 3.8, Jetstream 5.5. 테스트 56 통과, 주요 화면 브라우저 확인. 미추적 `public/vendor/livewire`(Livewire 2 자산)가 Livewire 3 스크립트 대신 로드되는 문제 → 스크래치패드로 이동(P0-1 삭제 대상). 같은 이유로 `resources/views/vendor/livewire`도 이동 |
| 2026-11-10 | P4 | 완료 | Laravel 10.50, PHP 8.2, PHPUnit 10, Permission 6. 테스트 55 통과 |
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
