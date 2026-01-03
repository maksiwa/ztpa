# ===============================================
# 📦 PHP 8.4 FPM Dockerfile
# ===============================================
# Ten Dockerfile buduje obraz PHP z wszystkimi
# rozszerzeniami potrzebnymi dla Symfony 8
# ===============================================

FROM php:8.4-fpm-alpine

# ─────────────────────────────────────────────
# Etykiety obrazu Docker
# ─────────────────────────────────────────────
LABEL maintainer="Maksymilian"
LABEL description="PHP 8.4 FPM for Symfony 8"

# ─────────────────────────────────────────────
# Zmienne środowiskowe
# ─────────────────────────────────────────────
ENV COMPOSER_ALLOW_SUPERUSER=1

# ─────────────────────────────────────────────
# Instalacja zależności systemowych
# ─────────────────────────────────────────────
# apk = Alpine Package Manager (lżejszy od apt)
# --no-cache = nie zapisuje cache pakietów
RUN apk add --no-cache \
    # Narzędzia deweloperskie
    git \
    curl \
    unzip \
    # Zależności dla rozszerzeń PHP
    icu-dev \
    libpq-dev \
    libzip-dev \
    oniguruma-dev \
    # Zależności dla GD (obrazy)
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# ─────────────────────────────────────────────
# Instalacja rozszerzeń PHP
# ─────────────────────────────────────────────
# docker-php-ext-configure = konfiguracja rozszerzenia
# docker-php-ext-install = kompilacja i instalacja
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    # PDO PostgreSQL - driver bazy danych
    pdo_pgsql \
    pgsql \
    # Internationalization - dla Symfony Intl
    intl \
    # OPcache - cache bytecode PHP (szybkość!)
    opcache \
    # ZIP - dla Composer
    zip \
    # Multibyte string - dla polskich znaków
    mbstring \
    # GD - przetwarzanie obrazów
    gd

# ─────────────────────────────────────────────
# Instalacja Redis extension
# ─────────────────────────────────────────────
# PECL = PHP Extension Community Library
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# ─────────────────────────────────────────────
# Instalacja Composera
# ─────────────────────────────────────────────
# Composer to menedżer pakietów PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ─────────────────────────────────────────────
# Konfiguracja PHP
# ─────────────────────────────────────────────
# Kopiujemy zoptymalizowaną konfigurację PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# ─────────────────────────────────────────────
# Katalog roboczy
# ─────────────────────────────────────────────
WORKDIR /var/www/html

# ─────────────────────────────────────────────
# Domyślna komenda
# ─────────────────────────────────────────────
CMD ["php-fpm"]
