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
 - `.env` files will be configured, missing ones are created from `.env.example` beforehand:
   - `.env.example` — the application name and the default database connection settings, plus the Clerk credentials when the `clerk` authentication type is selected
   - `.env` — the application name and the default database connection settings, plus the Clerk credentials when the `clerk` authentication type is selected
   - `.env.development` — the application name, `APP_ENV=development`, the application URL, the Redis-based maintenance, cache, queue and session drivers, emptied database connection settings, the selected filesystem disk with the Google Cloud Storage keys for the `gcs` storage, the Clerk credentials except `CLERK_SIGNER_KEY_PATH`, and the code owner email in `TELESCOPE_REPORT_MAIL_TO`, which Telescope uses as the report recipient
   - `.env.ci-testing` — the application name, `APP_ENV=testing`, a generated `APP_KEY`, `LOG_CHANNEL=stderr` and the test database connection settings, where `DB_HOST` gets a `_test` suffix (`pgsql_test`) and must be provided by the project's `docker-compose.yml`
   - `.env.testing` — the same as `.env.ci-testing`, plus `FAIL_EXPORT_JSON=false`
 - For `Web` and `Multiplatform` application types, `config/cors.php` will be published and its `paths` option will be set to `['*']`
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
