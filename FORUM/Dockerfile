FROM php:8-cli

RUN docker-php-ext-install mysqli

COPY . /app

WORKDIR /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]