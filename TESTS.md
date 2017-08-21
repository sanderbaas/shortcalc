# Tests with phpunit
- install wordpress-develop (See: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- run phpunit with environment variables indicating where the plugin directory is and where wordpress develop is:
WP_PLUGIN_DIR=. WP_DEVELOP_DIR=/var/www/wordpress-develop/ phpunit

or better:
WP_DEVELOP_DIR=/var/www/wordpress-develop/ vendor/phpunit/phpunit/phpunit

it is also possible to use another php version (if you have it):
WP_DEVELOP_DIR=/var/www/wordpress-develop/ php7.0 vendor/phpunit/phpunit/phpunit

Due to WordPress development tests being included, phpunit 5.0.10 is only supported
