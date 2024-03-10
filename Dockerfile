FROM node:18-alpine AS builder

WORKDIR /app

COPY package.json .
RUN npm install

COPY . .
ENV NODE_ENV production

RUN npm run build

FROM php:8.2-fpm AS production
ENV NODE_ENV production


# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Get latest Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Create system user to run Composer
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

WORKDIR /var/www

RUN chown -R $user:$user /var/www

COPY . .

# COPY --from=builder /app/public/build /var/www/public
COPY --from=builder /app/public/build /var/www/public

RUN composer install

USER $user

