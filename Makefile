hello:
	echo "Hello, World"

codestyle:
	-"./vendor/bin/phpstan" analyse /src --level 6
	"./vendor/bin/php-cs-fixer" fix src

csfixer:
	"./vendor/bin/php-cs-fixer" fix src

phpstan:
	"./vendor/bin/phpstan" analyse /src --level 6