Como rodar o projeto:

Tenha o XAMPP instalado e ligue o Mysql;

dependencias:
npm i;
composer install;
copy .env.example .env;
php artisan storage:link;

rodar o servidor do projeto:

php artisan key:generate;
php migrate:fresh;
php artisan serve;

npm run dev.
