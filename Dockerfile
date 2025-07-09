FROM php:8.2-apache

# Install required packages
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y default-mysql-server procps && \
    docker-php-ext-install mysqli pdo pdo_mysql

# Copy PHP files
COPY src/ /var/www/html/

# Copy SQL file
COPY init.sql /init.sql

# Set file permissions
RUN chown -R www-data:www-data /var/www/html

# Expose web port
EXPOSE 80

# Start both MySQL and Apache in the foreground
CMD mysqld_safe & \
    sleep 5 && \
    mysql -u root < /init.sql && \
    apache2-foreground

