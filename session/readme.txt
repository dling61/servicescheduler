1. Install redis php connecter (has to be phpredis)
=> from package manager
	sudo apt-get install php5-redis
=> from source 
	phpize
	./configure [--enable-redis-igbinary]
	make && make install

2. Configuration
php.ini setting 

"
/etc/php5/conf.d
extension=redis.so
session.save_handler = redis
session.save_path = "tcp://IPADDRESS:PORT?auth=REDISPASSWORD"
"

--- IPADDRESS is localhost
--- PORT is 6379
--- No password for Redis

3. Redis Reference

 http://www.thegeekstuff.com/2014/02/phpredis/