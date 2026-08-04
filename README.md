# Livewire Comments

Livewire **4** UI for [`vigstudio/vgcomments`](https://github.com/vigstudio/vgcomments) `^2.0`.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vigstudio/livewire-comments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/livewire-comments)

**Version:** `2.0` · PHP `^8.2` · Laravel `^10|^11|^12|^13` · Livewire `^4.0`

**Demo:** [vgcomment.nghiane.com/demo/livewire](https://vgcomment.nghiane.com/demo/livewire)

## Installation

```bash
composer require vigstudio/livewire-comments:^2.0
php artisan vendor:publish --tag=vgcomment-config
php artisan vendor:publish --tag=vgcomment-assets
php artisan vendor:publish --tag=vgcomment-assets-livewire
php artisan migrate
php artisan optimize:clear
```

Host app must already run **Livewire 4**.

## Usage

```html
<head>
    @livewireStyles
    @commentStyles
</head>
<body>
    <livewire:livewire-comments::comments :pageId="$pageId" />

    @livewireScripts
    @commentScripts
</body>
```

Only include comment assets on pages that render the component.

## License

MIT.
