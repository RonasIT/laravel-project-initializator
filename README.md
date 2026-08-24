[![Coverage Status](https://coveralls.io/repos/github/RonasIT/laravel-project-initializator/badge.svg?branch=main)](https://coveralls.io/github/RonasIT/laravel-project-initializator?branch=main)

# Laravel Project initializator

## Installation

```bash
    composer require ronasit/laravel-project-initializator --dev
```

## Usage

To begin the initialization process, run `php artisan init {application name}`. 
This will prompt you with questions regarding the project and the necessary packages for the initial setup.
Upon completion:
 - A new README.md file will be created
 - `.env` files will be configured
 - Required packages will be installed:
   - [`laravel/ui`](https://github.com/laravel/ui)
   - [`ronasit/laravel-helpers`](https://github.com/RonasIT/laravel-helpers)
   - [`ronasit/laravel-swagger`](https://github.com/RonasIT/laravel-swagger)
   - [`ronasit/laravel-entity-generator`](https://github.com/RonasIT/laravel-entity-generator) (dev)
   - [`laravel/pint`](https://github.com/laravel/pint) (dev)
   - [`ronasit/laravel-telescope-extension`](https://github.com/RonasIT/laravel-telescope-extension), unless Laravel Telescope is already installed
 - Optional packages will be installed depending on your answers:
   - [`ronasit/laravel-clerk`](https://github.com/RonasIT/laravel-clerk) — when the `clerk` authentication type is selected
   - [`ronasit/laravel-media`](https://github.com/RonasIT/laravel-media) — when the project works with media files, plus [`spatie/laravel-google-cloud-storage`](https://github.com/spatie/laravel-google-cloud-storage) for the `gcs` storage
   - [`ronasit/laravel-exponent-push-notifications`](https://github.com/RonasIT/laravel-exponent-push-notifications) — when a `Mobile`/`Multiplatform` application uses push notifications
 - This package will be automatically removed

## Contributing

Thank you for considering contributing to Laravel Project initializator package! The contribution guide
can be found in the [Contributing guide](CONTRIBUTING.md).

## License

Laravel Project Initializator package is open-sourced software licensed under the [MIT license](LICENSE).
