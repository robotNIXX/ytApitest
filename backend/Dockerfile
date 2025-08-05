FROM php:8.2-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev

# Установка PHP расширений
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_pgsql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание пользователя приложения и его домашней директории
RUN groupadd -r appuser && useradd -r -g appuser -m -d /home/appuser -s /bin/bash appuser

# Установка рабочей директории
WORKDIR /var/www/html

COPY . .

# Устанавливаем зависимости (artisan еще нет, поэтому ошибки игнорируем)
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Установка прав доступа для пользователя приложения
RUN chown -R appuser:appuser /var/www/html

# Переключение на пользователя приложения
USER appuser

# PHP-FPM будет слушать на порту 9000
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"] 