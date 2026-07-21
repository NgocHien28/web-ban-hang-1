FROM php:8.4-apache

RUN a2enmod rewrite

WORKDIR /var/www/html