# PHP DB Migration Validator

![php-db-migration-validator](https://user-images.githubusercontent.com/1849174/149624430-88547f33-9a48-4124-b648-d73c82a6e869.gif)

<p align="center">
<a href="https://discord.gg/83Yd8MgYp9"><img src="https://img.shields.io/static/v1?logo=discord&label=&message=Discord&color=36393f&style=flat-square" alt="Discord"></a>
<a href="https://github.com/antonkomarev/php-db-migration-validator/releases"><img src="https://img.shields.io/github/release/antonkomarev/php-db-migration-validator.svg?style=flat-square" alt="Releases"></a>
<a href="https://github.com/antonkomarev/php-db-migration-validator/blob/master/LICENSE"><img src="https://img.shields.io/github/license/antonkomarev/php-db-migration-validator.svg?style=flat-square" alt="License"></a>
</p>

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

**Validate file by wildcard**

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

## License

- `PHP DB Migration Validator` package is open-sourced software licensed under the [MIT license](LICENSE) by [Anton Komarev].

## About CyberCog

[CyberCog] is a Social Unity of enthusiasts. Research the best solutions in product & software development is our passion.

- [Follow us on Twitter](https://twitter.com/cybercog)

<a href="https://cybercog.su"><img src="https://cloud.githubusercontent.com/assets/1849174/18418932/e9edb390-7860-11e6-8a43-aa3fad524664.png" alt="CyberCog"></a>

[Anton Komarev]: https://komarev.com
[CyberCog]: https://cybercog.su
