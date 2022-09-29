<p align="center">
    <img src="https://github.com/outl1ne/nova-two-factor/blob/main/resources/img/nova-two-factor-banner.png?raw=true" />
</p>

[![Latest Stable Version](http://poser.pugx.org/outl1ne/nova-two-factor/v)](https://packagist.org/packages/outl1ne/nova-two-factor) [![Total Downloads](http://poser.pugx.org/outl1ne/nova-two-factor/downloads)](https://packagist.org/packages/outl1ne/nova-two-factor) [![Latest Unstable Version](http://poser.pugx.org/outl1ne/nova-two-factor/v/unstable)](https://packagist.org/packages/outl1ne/nova-two-factor) [![License](http://poser.pugx.org/outl1ne/nova-two-factor/license)](https://packagist.org/packages/outl1ne/nova-two-factor) [![PHP Version Require](http://poser.pugx.org/outl1ne/nova-two-factor/require/php)](https://packagist.org/packages/outl1ne/nova-two-factor)

# Nova-Two-Factor

This [Laravel Nova](https://nova.laravel.com) package adds 2FA support to the Nova dashboard.

## Requirements

- `php: >=8.0`
- `laravel/nova: ^4.15`

## Screenshots

#### Setup 2FA

![screenshot](/resources/img/sc-1.png)

#### Nova login screen with 2FA security

![screenshot](/resources/img/sc-3.png)

## Installation

Install the package in a Laravel Nova project via Composer and run migrations:

```bash
# Install nova-two-factor
composer require outl1ne/nova-two-factor

# Optionally publish the configuration and edit it
php artisan vendor:publish --provider="Outl1ne\NovaTwoFactor\TwoFactorServiceProvider" --tag="config"

# Run migrations
php artisan migrate
```

Add the ProtectWith2FA trait to your configured User model.

```php
<?php

namespace App\Models;

use Outl1ne\NovaTwoFactor\ProtectWith2FA;

class User extends Authenticatable {
    use ProtectWith2FA;
}

```

Add the TwoFa middleware to your project's Nova config file (`config/nova.php`).

```php
  'middleware' => [
    // ...
    \Outl1ne\NovaTwoFactor\Http\Middleware\TwoFa::class
  ],
```

Register NovaTwoFactor tool in NovaServiceProvider file.

```php
class NovaServiceProvider extends NovaApplicationServiceProvider{

public function tools()
    {
        return [
            // ...
            \Outl1ne\NovaTwoFactor\NovaTwoFactor::make()
        ];
    }

}

```
