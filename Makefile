hello:
	echo "Hello, World"

csfixer:
	"./vendor/bin/php-cs-fixer" fix .

phpstan:
	"./vendor/bin/phpstan" analyse index.php database.php --level 6