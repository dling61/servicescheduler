#!/bin/bash

ALLOW_IP_ADDRESS=localhost
REDIS_PORT=6379
# create a new chain
iptables -N redis-protection
# allow your IP
iptables -A redis-protection --src $ALLOW_IP_ADDRESS -j ACCEPT
# allow Redsmin IP if you want to connect from Redsmin
# iptables -A redis-protection --src 62.210.240.77 -j ACCEPT
# drop everyone else
iptables -A redis-protection -j DROP
# use chain xxx for packets coming to TCP port $REDIS_PORT
iptables -I INPUT -m tcp -p tcp --dport $REDIS_PORT -j redis-protection

# password configure & random commands can also be used for production security