# ppe-dvtinh99

install 
````
composer require ppe/dvtinh
````
paste this code to .env file
````
DB_CORE_CONNECTION=ppe_core
DB_CORE_HOST=mariadb
DB_CORE_PORT=3306
DB_CORE_DATABASE=ppe_core_db
DB_CORE_USERNAME=root
DB_CORE_PASSWORD=root

````
Change file passport.php on
vendor/laravel/passport/config/passport.php
for move passport's table to core database

````
'storage' => [
        'database' => [
            'connection' => env('DB_CORE_CONNECTION', 'ppe_core'),
        ],
    ],
````
add config/ppe.php to config/

paste code to config/database.php
````
'connections' => [

        'ppe_core' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_CORE_HOST', '127.0.0.1'),
            'port' => env('DB_CORE_PORT', '5432'),
            'database' => env('DB_CORE_DATABASE', 'forge'),
            'username' => env('DB_CORE_USERNAME', 'forge'),
            'password' => env('DB_CORE_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        .....
````

extends model Users and UserAvatar
````
class User extends \ppe\dvtinh\Models\User
{
...
}
````

````
class UserAvatar extends \ppe\dvtinh\Models\UserAvatar
{
..
}
````
you can keep relationship 
