FROM php:8.3-apache

# Instalar extensões necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Habilitar mod_headers para CORS
RUN a2enmod headers

# Configurar Apache para Laravel
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar código
COPY . .

# Instalar dependências do Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Dar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 8000

# Copiar e configurar script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]