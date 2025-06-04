#!/bin/bash
php -d display_errors=1 -d display_startup_errors=1 -d error_reporting=E_ALL -S localhost:8080
##le rendre excecutable avec ceci chmod +x start-server.sh ensuite lance ci sur le terminale

## pour l'autoload
composer dump-autoload