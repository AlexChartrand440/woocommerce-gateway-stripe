#!/usr/bin/env bash
# usage: travis.sh before|after

if [ $1 == 'before' ]; then

	# composer install fails in PHP 5.2
	[ $TRAVIS_PHP_VERSION == '5.2' ] && exit;

	#composer self-update

	# install php-coveralls to send coverage info
	#composer init --require=satooshi/php-coveralls:0.7.0 -n
	#composer install --no-interaction

	#install npm to update node modules for JS tests
	npm install

elif [ $1 == 'after' ]; then
	# Run at least one command so that script doesn't break
	echo "Nothing to do in 'after' script."

	# no Xdebug and therefore no coverage in PHP 5.2
	#[ $TRAVIS_PHP_VERSION == '5.2' ] && exit;

	# send coverage data to coveralls
	#php vendor/bin/coveralls --verbose --exclude-no-stmt

	# get scrutinizer ocular and run it
	# wget https://scrutinizer-ci.com/ocular.phar
	#ocular.phar code-coverage:upload --format=php-clover ./tmp/clover.xml

fi
