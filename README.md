# PHP Database Migration Validator

![php-database-migration-validator](https://user-images.githubusercontent.com/1849174/149621805-f81bc379-e25e-4f98-8e5c-4102c0cad749.gif)

<p align="center">
<a href="https://discord.gg/83Yd8MgYp9"><img src="https://img.shields.io/static/v1?logo=discord&label=&message=Discord&color=36393f&style=flat-square" alt="Discord"></a>
<a href="https://github.com/antonkomarev/php-database-migration-validator/releases"><img src="https://img.shields.io/github/release/antonkomarev/php-database-migration-validator.svg?style=flat-square" alt="Releases"></a>
<a href="https://github.com/antonkomarev/php-database-migration-validator/blob/master/LICENSE"><img src="https://img.shields.io/github/license/antonkomarev/php-database-migration-validator.svg?style=flat-square" alt="License"></a>
</p>

## Installation

Pull in the package through Composer.

```shell
php composer require antonkomarev/php-database-migration-validator
```

## Usage

### Validate migrations are irreversible

**Validate all files in directory**

```shell
php vendor/bin/php-database-migration-validator --rule=irreversible database/migrations/
```

**Validate single file**

```shell
php vendor/bin/php-database-migration-validator --rule=irreversible database/migrations/example.php
```

## License

- `PHP Database Migration Validator` package is open-sourced software licensed under the [MIT license](LICENSE) by [Anton Komarev].

## About CyberCog

[CyberCog] is a Social Unity of enthusiasts. Research the best solutions in product & software development is our passion.

- [Follow us on Twitter](https://twitter.com/cybercog)

<a href="https://cybercog.su"><img src="https://cloud.githubusercontent.com/assets/1849174/18418932/e9edb390-7860-11e6-8a43-aa3fad524664.png" alt="CyberCog"></a>

[Anton Komarev]: https://komarev.com
[CyberCog]: https://cybercog.su
