# Use the official WordPress image with PHP 7.4 and Apache
FROM wordpress:latest

# Increase PHP memory limit
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Enable and configure PHP OPcache
# RUN docker-php-ext-install opcache \
#     && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
#     && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
#     && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
#     && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
#     && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini

RUN docker-php-ext-install calendar


# Enable Apache modules for better performance
RUN a2enmod expires headers rewrite

# Set up Apache performance tuning
RUN echo "ServerSignature Off" >> /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "FileETag None" >> /etc/apache2/apache2.conf \
    && echo "TraceEnable off" >> /etc/apache2/apache2.conf

# Cleanup unnecessary files to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------

# You may need to customize other settings based on your needs

# Example: Copy your custom WordPress theme or plugins
# COPY custom-theme /var/www/html/wp-content/themes/custom-theme
# COPY custom-plugin /var/www/html/wp-content/plugins/custom-plugin