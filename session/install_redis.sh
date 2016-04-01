#!/bin/bash

# install redis by using ubuntu package manager
# add redis source
sudo add-apt-repository ppa:chris-lea/redis-server
# update source list 
sudo apt-get update
# install the redis server
sudo apt-get install redis-server
# test redis is running correctly
redis-cli ping

# install redis php driver by using package manager
sudo apt-get install php5-redis
# change php setting to use redis as session save handler
# file location: /etc/php5/apache2/php.ini
# changes:
# 	session.save_handler = redis
# add:
# 	extension=redis.so
# 	session.save_path = "tcp://IPADDRESS:PORT?auth=REDISPASSWORD"
