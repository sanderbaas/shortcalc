# Tests with phpunit
- install wordpress-develop (See: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- run phpunit with environment variables indicating where the plugin directory is and where wordpress develop is:
WP_PLUGIN_DIR=. WP_DEVELOP_DIR=/var/www/wordpress-develop/ phpunit 
