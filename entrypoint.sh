#!/bin/sh

echo "Installing dependencies..."
cd /app && php /usr/bin/composer install

echo "Configuring cron..."
echo "$CRON_PATTERN php /app/cron.php > /dev/stdout" > /app/cron
crontab cron

echo "Starting cron scheduler..."
crond -f -l 8