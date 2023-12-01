#!/bin/bash
cp -R /var/www/html/docker_temp/* /var/www/html/wp-content/plugins/${TEST_PLUGIN_FOLDER} &&
rm -rf /var/www/html/docker_temp &&
wp --allow-root config create \
    --dbhost=${WORDPRESS_DB_HOST} \
    --dbname=${WORDPRESS_DB_NAME} \
    --dbuser=${WORDPRESS_DB_USER} \
    --dbpass=$(cat $WORDPRESS_DB_PASSWORD_FILE) \
    --dbprefix=${WORDPRESS_TABLE_PREFIX} \
    --locale=${WORDPRESS_LOCALE} \
    --force \
    --skip-check &&
wp --allow-root core install \
    --url=${WORDPRESS_WEBSITE_URL_WITHOUT_HTTP} \
    --title="${WORDPRESS_WEBSITE_TITLE}" \
    --admin_user=${WORDPRESS_ADMIN_USER} \
    --admin_password=${WORDPRESS_ADMIN_PASSWORD} \
    --admin_email=${WORDPRESS_ADMIN_EMAIL} \
    --locale=${WORDPRESS_LOCALE} \
    --skip-email &&
wp --allow-root option update siteurl "${WORDPRESS_WEBSITE_URL}" &&
wp --allow-root rewrite structure "${WORDPRESS_WEBSITE_POST_URL_STRUCTURE}" &&
composer install -o -n --prefer-dist --no-progress --no-dev --no-cache --working-dir=/var/www/html/wp-content/plugins/${TEST_PLUGIN_FOLDER}/vendor/spin8/framewrok/