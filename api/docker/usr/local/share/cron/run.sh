#!/bin/sh

set -xe

chmod 700 /etc/crontab

rsyslogd
cron -L 15

tail -f /var/log/syslog
