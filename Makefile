hello:
	echo "Hello, World"

codestyle:
	-"./vendor/bin/phpstan" analyse index.php database.php --level 6
	"./vendor/bin/php-cs-fixer" fix .

csfixer:
	"./vendor/bin/php-cs-fixer" fix .

phpstan:
	"./vendor/bin/phpstan" analyse index.php database.php --level 6