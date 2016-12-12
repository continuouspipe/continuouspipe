#!/bin/sh

set -xe

composer run-script update-parameters

chmod 700 /etc/crontab

rsyslogd
cron -L 15

tail -f /var/log/syslog
