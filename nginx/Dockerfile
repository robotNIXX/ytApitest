FROM nginx:alpine

# Копирование конфигурации
COPY conf/default.conf /etc/nginx/conf.d/default.conf

# Открытие портов
EXPOSE 80 443

# Запуск Nginx
CMD ["nginx", "-g", "daemon off;"] 