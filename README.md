# Старт проекта

## Установка

1. Установить Docker и Docker Compose
2. Запустить контейнеры:
```bash
docker-compose up --build -d
```
3. Установить зависимости:
```bash
docker-compose exec backend composer install
```
4. Настроить .env файл:
```bash
cp .env.example .env
cp .env.example .env.testing 
``` 
5. Сгенерировать ключ и настройте необходимые переменные:
```bash
docker-compose exec backend php artisan key:generate
```
    
6. Запустить миграции:
```bash
docker-compose exec backend php artisan migrate  
docker-compose exec backend php artisan migrate  --env=testing
```
7. Запустить сидирование данных:  
__Проверьте наличие файла youtube_channels.csv в директории backend/storage/app/private/__
```bash
docker-compose exec backend php artisan db:seed
```


## Запуск тестов
```bash
docker-compose exec backend php artisan test
```