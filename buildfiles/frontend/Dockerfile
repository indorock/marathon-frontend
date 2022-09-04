FROM php:8.1.10-apache-buster
MAINTAINER Mark <mark@markmulder.net>

RUN apt update && apt install mc git -y
RUN a2enmod rewrite
RUN service apache2 restart

# Copy your files to working directory /var/www/html
# ADD ./ /var/www/html
# RUN chmod -R 0755 /var/www/html

EXPOSE 80
#CMD ["/run.sh"]