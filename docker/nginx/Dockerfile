FROM nginx:1.15

LABEL maintainer="Victoria Etim <victoria@initsng.com>"

COPY default.prod.conf /etc/nginx/conf.d/default.conf

CMD ["nginx", "-g", "daemon off;"]

RUN sudo chmod -R 777 /var/www/html/storage/logs
