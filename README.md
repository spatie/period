# Complex period comparisons

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/period.svg?style=flat-square)](https://packagist.org/packages/spatie/period)
[![Build Status](https://img.shields.io/travis/spatie/period/master.svg?style=flat-square)](https://travis-ci.org/spatie/period)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/period.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/period)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/period.svg?style=flat-square)](https://packagist.org/packages/spatie/period)

Complex period comparisons.

## Installation

You can install the package via composer:

```bash
composer require spatie/period
```

## Usage

Overlap with at least one other period

```php
/*
 * A       [========]
 * B                   [==]
 * C                           [=====]
 *
 * D              [===============]
 *
 * OVERLAP        [=]   [==]   [==]
 */
 
$a = Period::make('2018-01-01', '2018-01-31');
$b = Period::make('2018-02-10', '2018-02-20');
$c = Period::make('2018-03-01', '2018-03-31');

$d = Period::make('2018-01-20', '2018-03-10');

$overlaps = $d->overlap($a, $b, $c);
```

Diff between multiple periods

```php
/*
 * A                   [====]
 * B                               [========]
 * C         [=====]
 * CURRENT      [========================]
 *
 * DIFF             [=]      [====]
 */

$a = Period::make('2018-01-05', '2018-01-10');
$b = Period::make('2018-01-15', '2018-03-01');
$c = Period::make('2017-01-01', '2018-01-02');

$current = Period::make('2018-01-01', '2018-01-31');

$diff = $current->diff($a, $b, $c);
```

Overlap with all periods

```php
/*
 * A              [============]
 * B                   [==]
 * C                  [=======]
 *
 * OVERLAP             [==]
 */

$a = Period::make('2018-01-01', '2018-01-31');
$b = Period::make('2018-01-10', '2018-01-15');
$c = Period::make('2018-01-10', '2018-01-31');

$overlap = $a->overlapAll($b, $c);
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Brent Roose](https://github.com/brendt)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
