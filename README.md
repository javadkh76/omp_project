# omp_project
laravel purchase system. contains token base authentication and 3 different payment gateway.

## Initial Environment

### Use default

```bash
cd app
cp .env.example .env
```
Default env configs is compatible with the default configuration of database and app.

## Installing

### docker-compose

```bash
cd docker
docker-compose up
```

## Run Tests


```bash
docker exec -it omp_app sh -c "php artisan test"
```
Before run tests be sure that "omp_app" container is running.