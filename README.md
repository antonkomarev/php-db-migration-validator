# PHP DB Migration Validator

![php-db-migration-validator](https://user-images.githubusercontent.com/1849174/149624430-88547f33-9a48-4124-b648-d73c82a6e869.gif)

<p align="center">
<a href="https://discord.gg/83Yd8MgYp9"><img src="https://img.shields.io/static/v1?logo=discord&label=&message=Discord&color=36393f&style=flat-square" alt="Discord"></a>
<a href="https://github.com/antonkomarev/php-db-migration-validator/releases"><img src="https://img.shields.io/github/release/antonkomarev/php-db-migration-validator.svg?style=flat-square" alt="Releases"></a>
<a href="https://github.com/antonkomarev/php-db-migration-validator/blob/master/LICENSE"><img src="https://img.shields.io/github/license/antonkomarev/php-db-migration-validator.svg?style=flat-square" alt="License"></a>
</p>

## Introduction

In modern PHP frameworks such as Symfony and Laravel, migrations usually have `up` and `down` methods.
In `up` method of migration definition you had to write code which is called only on running migration forward and in `down` — the code which is called only on rolling migration back.
It is standard practice to make database migrations irreversible.
Migrations should be backward compatible and only go forward.

In Laravel, a missing or empty `down` method does not prevent rollback migration on execution of `php artisan migrate:rollback` CLI command.
The state of the database will not change, but the migration will be removed from the registry of applied migrations,
and the next execution of `php artisan migrate` will call the `up` method again.
To prevent this behavior, all migrations should have `down` method that will throw an Exception, nothing more.

PHP DB Migration Validator checks whether all migration files meet this requirement.
You can add it to the server's git hooks to prevent migration rollback, or add validation step to CI.

## Installation

Pull in the package through Composer.

```shell
php composer require antonkomarev/php-db-migration-validator
```

## Usage

### Validate migrations are irreversible

**Validate file**

```shell
php vendor/bin/php-db-migration-validator --rule=irreversible migrations/file.php
```

**Validate many files**

```shell
php vendor/bin/php-db-migration-validator --rule=irreversible migrations/file.php migrations/file2.php
```

**Validate many files by wildcard**

```shell
php vendor/bin/php-db-migration-validator --rule=irreversible migrations/2022_*
```

**Validate files in directory**

```shell
php vendor/bin/php-db-migration-validator --rule=irreversible migrations/
```

**Validate files in many directories**

```shell
php vendor/bin/php-db-migration-validator --rule=irreversible app/migrations/ vendor/migrations/
```

### CI automation

Automating the validation of all contributions to the repository as part of the Continuous Integration is one of the possible ways to use this tool.

#### GitHub Action

Create file `.github/workflows/db-migration-validation.yaml` in the application repository.

```yaml
name: DB Migration Validation

on:
    push:

jobs:
    db-migration-validation:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v2
            - uses: shivammathur/setup-php@v2
              with:
                  php-version: 8.1
                  extensions: tokenizer
                  coverage: none
            - name: Install PHP DB Migration Validator dependency
              run: composer global require antonkomarev/php-db-migration-validator --no-interaction
            - name: Ensure all database migrations are irreversible
              run: php-db-migration-validator --rule=irreversible ./database/migrations
```

### Command usage instructions

```
$ php vendor/bin/php-db-migration-validator --help
PHP DB Migration Validator
--------------------------
by Anton Komarev <anton@komarev.com>

Usage: php-db-migration-validator --rule=<rule> <path>

  The following commands are available:

    help  Shows this usage instructions.

  Options:

    --rules=<rule>   Validates the database migration(s) in the specified <path>.
                     Exits with code 1 on validation errors, 2 on other errors and 0 on success.
                     Available rules (at least one should be specified):
                     - irreversible — ensure if migration file has `down` method and this method throws an Exception.
```

## License

- `PHP DB Migration Validator` package is open-sourced software licensed under the [MIT license](LICENSE) by [Anton Komarev].

## Support the project

If you'd like to support the development of PHP DB Migration Validator, then please consider [sponsoring me]. Thanks so much!

## About CyberCog

[CyberCog] is a Social Unity of enthusiasts. Research the best solutions in product & software development is our passion.

- [Follow us on Twitter](https://twitter.com/cybercog)

<a href="https://cybercog.su"><img src="https://cloud.githubusercontent.com/assets/1849174/18418932/e9edb390-7860-11e6-8a43-aa3fad524664.png" alt="CyberCog"></a>

[Anton Komarev]: https://komarev.com
[CyberCog]: https://cybercog.su
[sponsoring me]: https://paypal.me/antonkomarev
