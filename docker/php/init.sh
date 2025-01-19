#!/bin/bash

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:execute --up 'DoctrineMigrations\Version20220618'
apache2-foreground