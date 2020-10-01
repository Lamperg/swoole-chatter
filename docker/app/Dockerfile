ARG APP_PHP_VERSION

# Base app image
#=============================================================================
FROM php:${APP_PHP_VERSION}-fpm as base

ARG APP_SYS_USER_ID
ARG APP_SYS_GROUP_ID

# Set working directory
WORKDIR /var/www/github-actions-training

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    zip \
    unzip \
    # Cleanup app-list to reduce size of the image
     && apt-get clean \
     && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Swoole
RUN pecl install swoole

# Add user for application
RUN groupadd -g ${APP_SYS_GROUP_ID} www
RUN useradd -u ${APP_SYS_USER_ID} -ms /bin/bash -g www www

# Change application directory permissions
RUN chown -R www:www /var/www/github-actions-training

# Change current user to www
USER www

# Expose public ports and start swoole server
EXPOSE 80 9000

CMD ["php", "index.php"]
