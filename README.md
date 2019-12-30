
```shell
composer require joselee214/laravel
```

Add the service provider to your `config/app.php` file within the `providers` key:

```php
// ...
'providers' => [
    /*
     * Package Service Providers...
     */

    Joselee214\Ypc\Joselee214ServiceProvider::class,
],
// ...
```
### Configuration for local environment only

If you wish to enable generators only for your local environment, you should install it via composer using the --dev option like this:

```shell
composer require joselee214/laravel --dev
```

Then you'll need to register the provider in `app/Providers/AppServiceProvider.php` file.

```php
public function register()
{
    if ($this->app->environment() == 'local') {
        $this->app->register(\Joselee214\Ypc\Joselee214ServiceProvider::class);
    }
}
```

## Models

![Generating models with artisan](https://cdn-images-1.medium.com/max/800/1*hOa2QxORE2zyO_-ZqJ40sA.png "Making artisan code my Eloquent models")

Add the `models.php` configuration file to your `config` directory and clear the config cache:

```shell
php artisan vendor:publish --tag=Joselee214-models
php artisan config:clear
```

### Usage

Assuming you have already configured your database, you are now all set to go.

- Let's scaffold some of your models from your default connection.

```shell
php artisan zz:models
```

- You can scaffold a specific table like this:

```shell
php artisan zz:models --table=users
```

- You can also specify the connection:

```shell
php artisan zz:models --connection=mysql
```

- If you are using a MySQL database, you can specify which schema you want to scaffold:

```shell
php artisan zz:models --schema=shop
```
