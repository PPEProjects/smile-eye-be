### Yêu cầu môi trường
- Sử dụng docker để tạo môi trường https://www.docker.com/. Sử dụng laradock để tạo các container cho project https://laradock.io/
- Các container cần thiết để chạy dự án là
```$xslt
1. Nginx:
2. PHP 7.3
3. Redis
5. Mariadb: Database
6. PhpMyAdmin: Công cụ quản lý database
```

### Hướng dẫn setup project
- http://smile-eyes.test: domain 
- Vào thư mục laradock mở file .env chỉnh đường dẫn cho run tới dự án đang chạy
- Copy file .env.example thành file .env và chỉnh lại thông tin connect db, redis cho đúng.
  -install package
  `composer install`
- Start các docker container cần thiết của dự án
`docker-compose up -d mariadb phpmyadmin redis nginx`
- Vào docker container để chạy lệnh
`docker-compose exec workspace bash`
  
Vào chế độ dev packagist
clone package tại smile-eyes-be
````
git clone https://github.com/PPEProjects/ppe-core.git
````
update composer.json
````
composer dump-autoload

--add provider config/app.php--
\ppeCore\dvtinh\Providers\CoreDBServiceProvider::class,
````
```
run migrate: 
php artisan migrate

run factory: 
php artisan db:seed
```

http://smile-eyes.test/admin/login
Login: admin@admin.com/password

```
php artisan migrate:refresh
php artisan passport:install
```
http://smile-eyes.test/graphql-playground
```
mutation {
  register(
    input: {
      name: "myemail1@email.com"
      email: "myemail1@email.com"
      password: "123456789qq"
      password_confirmation: "123456789qq"
    }
  ) {
    status
  }
}
```
# workspace
```
docker-compose exec workspace bash;
TienNV
cd /var/www/codeby/ppe-project/smile-eyes-be;
php artisan migrate:refresh;
php artisan passport:install;

```
# media setup
```
php artisan storage:link
mkdir -m 777 -p storage/app/public/media/images storage/app/public/media/thumb-images
```