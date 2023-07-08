ARG wp_version=6.2.2
ARG php_version=8.2
ARG webserver_version=apache

FROM wordpress:${wp_version}-php${php_version}-${webserver_version}
WORKDIR /var/www/html/
ENV TEST_PLUGIN_PATH=${TEST_PLUGIN_PATH:-wp-content/plugins/spin8-test/}
RUN mkdir ${TEST_PLUGIN_PATH}