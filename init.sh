
#!/usr/bin/env bash

# Ubuntu 20.04 (LTS) x64
# https://www.hestiacp.com
apt-get update && apt-get dist-upgrade
echo fs.inotify.max_user_watches=582222 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install-ubuntu.sh
bash hst-install-ubuntu.sh --interactive no --email admin@domain.tld --hostname "${hostname}" -f

#echo "*/10 * * * * find /home/admin/tmp -type f -name 'sess_*' -mmin +10 -exec rm {} \;" >>  /var/spool/cron/crontabs/root;service cron restart

export HESTIA=/usr/local/hestia/

if [ “${PATH#/usr/local/hestia/bin}” = “$PATH” ]; then
. /etc/profile.d/hestia.sh
fi


v-add-cron-job admin "*/1" "*" "*" "*" "*" "find /home/admin/tmp -type f -name 'sess_*' -mmin +1 -exec rm {} \;"
v-restart-cron
sed -i "s/'1'/'6'/g" /usr/local/hestia/data/users/admin/user.conf
sed -i "s/'0'/'6'/g" /usr/local/hestia/data/users/admin/user.conf
apt install fish
