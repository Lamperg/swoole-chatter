ARG APP_PHP_VERSION

# Base app image
#=============================================================================
FROM php:${APP_PHP_VERSION}-fpm as base

ARG APP_SYS_USER_ID
ARG APP_SYS_GROUP_ID
ARG APP_NODE_JS_VERSION

# Set working directory
WORKDIR /var/www/swoole-chatter

# Install system dependencies
#RUN apt-get update && apt-get install -y --no-install-recommends \
#    curl \
#    zip \
#    unzip

# Cleanup app-list to reduce size of the image
#RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
#RUN docker-php-ext-install pdo_mysql

# Install Composer
#COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Swoole
#RUN pecl install swoole

# Install nodejs/npm
#RUN curl -sL https://deb.nodesource.com/setup_${APP_NODE_JS_VERSION}.x | bash -
#RUN apt-get -y install nodejs

# Add user for application
RUN groupadd -g ${APP_SYS_GROUP_ID} www
RUN useradd -u ${APP_SYS_USER_ID} -ms /bin/bash -g www www

# Change application directory permissions
RUN chown -R www:www /var/www/swoole-chatter

# Change current user to www
USER www

# Expose public ports and start swoole server
EXPOSE 9000
