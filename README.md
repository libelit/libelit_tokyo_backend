## Libelit Backend

### Pre-requisites

- PHP 8.3 (https://www.php.net/downloads)
- Composer (https://getcomposer.org/download/)

#### Redis Container (for caching)
```bash
docker run -v /local-data/:/data -d --name redis-stack -p 6379:6379 -p 8001:8001 redis/redis-stack:latest
```

#### Mariadb Container
```bash
docker run --name mariadb -e MYSQL_ROOT_PASSWORD=Test1234 -p 3306:3306  -d docker.io/library/mariadb:10.5
```

### Setup
```bash
git clone https://github.com/libelit/libelit-backend
```
### Environment
```bash
cp .env.example .env
```

### Installation
```bash
cd libelit-backend
composer install
php artisan key:generate
```

### Run migrations
```bash
cd libelit-backend
php artisan migrate
php artisan db:seed
php artisan passport:client --personal
```

### Run Server
```bash
cd libelit-backend
php artisan serve
```
