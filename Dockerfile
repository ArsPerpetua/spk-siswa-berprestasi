# ─── Build stage ─────────────────────────────────────────────────────────────
FROM php:8.1-cli AS builder

# Install system dependencies needed by Composer and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gd \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy dependency manifests first for layer caching
COPY composer.json composer.lock ./

# Install production dependencies only (no dev packages)
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
        --prefer-dist

# Copy the rest of the application source
COPY . .

# ─── Runtime stage ────────────────────────────────────────────────────────────
FROM php:8.1-cli AS runtime

# Install only the runtime PHP extensions required by CodeIgniter 4 + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mbstring \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gd \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Tune OPcache for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

# Copy the fully-built application from the builder stage
COPY --from=builder /app /app

# Ensure writable directories exist with correct permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chmod -R 775 writable

# CodeIgniter 4 environment — override via Railway environment variables
ENV CI_ENVIRONMENT=production

# Railway injects $PORT at runtime; default to 8080 for local runs
ENV PORT=8080

EXPOSE 8080

# Use the same start command defined in railway.toml
CMD ["sh", "-c", "php spark serve --host 0.0.0.0 --port ${PORT}"]
