[![Latest Version on Packagist](https://img.shields.io/packagist/v/vigstudio/livewire-comments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/livewire-comments)
[![Total Downloads](https://img.shields.io/packagist/dt/vigstudio/livewire-comments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/livewire-comments)

#  Livewire Comments Package
- [Documents](https://vgcomment.netlify.app/livewire-comments/)
- [Live Demo](https://vgcomment.nghiane.com/)

## _Features_
- [x] Add comments to any model
- [x] Multiple comment systems on the same page
- [x] Multiple auth guards
- [x] Image and File upload support
- [x] Drag and drop, copy and paste upload files support
- [x] reCaptcha v3 support
- [x] Emoji support
- [x] Markdown support
- [x] NSFW image upload check support

## _Pending Features_
- [ ] Allow guest to comment
- [ ] Admin panel
- [ ] Mention user with @
- [ ] Emoji Suggestion Popup
- [ ] Delete Report comment
- [ ] Ratting system
- [ ] Toolbar for comment
- [ ] Comment history
- [ ] Show Nested comments
- [ ] Unit test

## _Packages_
- [Livewire](https://laravel-livewire.com/docs/2.x/installation)
- [AlpineJs](https://alpinejs.dev/essentials/installation)
- [Heroicons Blade Components](https://github.com/archielite/laravel-heroicons)
- [VgComments](https://github.com/vigstudio/vgcomments)
- [TailwindCss](https://tailwindcss.com)
- [Plyr](https://plyr.io)
- [highlight.js](https://highlightjs.org)
- [picmo](https://picmojs.com)
- [Laravel Mix](https://github.com/laravel-mix/laravel-mix)
- [Laravel Echo](https://laravel.com/docs/9.x/broadcasting#installing-laravel-echo)

## _Introduction_
Package use Macroable trait to add comments to any model. It uses Livewire and AlpineJs to create a comment system with a lot of features.

Package support multiple comment systems on the same page.

Package support multiple auth guards.

## _Prerequisites_
- [Composer](https://getcomposer.org/download/)
- [Laravel 9.x](https://laravel.com/docs/9.x/installation)
- [Livewire](https://laravel-livewire.com/docs/2.x/installation)
- [AlpineJs](https://alpinejs.dev/essentials/installation)
- [Laravel Echo](https://laravel.com/docs/9.x/broadcasting#installing-laravel-echo)


## _Install Livewire Comments Package_

**In your terminal run:**
```bash
composer require vigstudio/livewire-comments
```

**Publish the assets files with:**
```bash
php artisan vendor:publish --tag=vgcomment-assets-livewire
```

**You can publish the config with:**
```bash
php artisan vendor:publish --tag=vgcomment-config
```
Edit prefix route in `config/vgcomment.php` file.
```php
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the URI path where VgComment will be accessible from. Feel free to
    | change this path to anything you like.
    |
    */
    'prefix' => 'vgcomment',
```

Edit connection name in `config/vgcomment.php` file.
```php
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all of your database work.
    |
    */
    'connection' => env('DB_CONNECTION', 'mysql'),
```

Edit table names in `config/vgcomment.php` file.

```php
    /*
    |--------------------------------------------------------------------------
    | Name of Tables in Database
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be created by the migration and
    | used by the Comment model shipped with this package.
    |
    | "comments"    : Comments Table
    | "files"       : Files Attachment Table
    | "reactions"   : Reactions Table
    | "reports"     : Reports Table
    | "settings"    : Settings Table
    |
    */
    'table' => [
        'comments' => 'vgcomments',
        'files' => 'vgcomment_files',
        'reactions' => 'vgcomment_reactions',
        'reports' => 'vgcomment_reports',
        'settings' => 'vgcomment_settings',
    ],
```

Config Column or Attribute User Model in `config/vgcomment.php` file.
```php
        /*
    |--------------------------------------------------------------------------
    | Column of User Table for get Data
    |--------------------------------------------------------------------------
    |
    | This is the setting for column of user table for get data.
    | "user_column_name"  : Column name for get name user
    | "user_column_email" : Column name for get email user
    | "user_column_url"   : Column name for get url user
    |
    */
    'user_column_name' => 'name',
    'user_column_email' => 'email',
    'user_column_url' => 'url',
    'user_column_avatar_url' => 'avatar_url',
```

Set moderation user in `config/vgcomment.php` file.
```php
        /*
    |--------------------------------------------------------------------------
    | Users Manager Comments
    |--------------------------------------------------------------------------
    |
    | This is the setting for users manager comments.
    | 'guard' => [user_id]
    |
    | Example:
    | 'web' => [1, 2, 3]
    | 'api' => [1, 2, 3]
    |
    */
    'moderation_users' => [
        'web' => [1],
    ],
```

**Run the migrate command to create the necessary tables:**
Before running the migrate command, you can edit the `config/vgcomment.php` file to change the table names.
```bash
php artisan migrate
```

**Additionally you may want to clear the config, cache, etc:**
```bash
php artisan optimize:clear
```

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/nghianecom)

[!["Donate Me!"](https://i.ibb.co/Pw6s74r/image.png)](https://nghiane.com)
