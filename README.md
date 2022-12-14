[![Latest Version on Packagist](https://img.shields.io/packagist/v/vigstudio/livewire-comments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/livewire-comments)
[![Total Downloads](https://img.shields.io/packagist/dt/vigstudio/livewire-comments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/livewire-comments)

#  Livewire Comments Package
- [Documents](https://vgcomment.netlify.app/livewire-comments/)
- [Live Demo](https://vgcomment.nghiane.com/)

## _Features_
- [✓] Add comments to any model
- [✓] Multiple comment systems on the same page
- [✓] Multiple auth guards
- [✓] Drag and drop, copy and paste upload files
- [✓] reCaptcha v3 support
- [✓] Emoji support
- [✓] Markdown support
- [✓] NSFW check support
- [x] Mention support
- [x] Admin Settings Panel

## _Packages_
- [Livewire](https://laravel-livewire.com/docs/2.x/installation)
- [AlpineJs](https://alpinejs.dev/essentials/installation)
- [Heroicons Blade Components](https://github.com/archielite/laravel-heroicons)
- [VgComments](https://github.com/vigstudio/vgcomments)

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
