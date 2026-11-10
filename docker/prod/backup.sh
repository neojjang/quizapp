#!/bin/sh
# Mac mini 운영 DB 백업: backups/quizapp-YYYYmmdd-HHMM.sql.gz 를 만들고 14일 지난 파일을 지운다.
# 사용: cd ~/quizapp && ./docker/prod/backup.sh   (cron 예시는 docs/deploy/MACMINI.md)
set -e
cd "$(dirname "$0")/../.."
mkdir -p backups
out="backups/quizapp-$(date +%Y%m%d-%H%M).sql.gz"
docker compose -f compose.prod.yml exec -T db sh -c \
  'dump=$(command -v mariadb-dump || command -v mysqldump); "$dump" --single-transaction --routines --triggers --default-character-set=utf8mb4 -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
  | gzip > "$out.tmp"
# 덤프가 끝까지 쓰였는지 확인한 뒤에만 이름을 바꾼다
gunzip -c "$out.tmp" | tail -n 1 | grep -q 'Dump completed' && mv "$out.tmp" "$out"
find backups -name 'quizapp-*.sql.gz' -mtime +14 -delete
echo "$out"
