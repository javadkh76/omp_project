FROM docker.io/bitnami/laravel as base

WORKDIR /app
COPY . .

CMD bash -c "composer install && php artisan migrate && php artisan serve --host 0.0.0.0 --port 8000"

