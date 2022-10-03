# Changelog

All notable changes to `period` will be documented in this file

## 2.3.5 - 2022-10-03

Revert previous release

## Make Precision consts public - 2022-10-03

**Full Changelog**: https://github.com/spatie/period/compare/2.3.3...2.3.4

## 2.3.3 - 2022-03-03

**Full Changelog**: https://github.com/spatie/period/compare/2.3.2...2.3.3

## 2.3.2 - 2021-12-23

## What's Changed

- Error "Undefined array key 0" fix by @aliowacom in https://github.com/spatie/period/pull/105

## New Contributors

- @aliowacom made their first contribution in https://github.com/spatie/period/pull/105

**Full Changelog**: https://github.com/spatie/period/compare/2.3.1...2.3.2

## 2.3.1 - 2021-12-01

## What's Changed

- Add PHP 8.1 Support by @patinthehat in https://github.com/spatie/period/pull/102
- Improve PHP 8.1.0 support by @kyryl-bogach in https://github.com/spatie/period/pull/103

## New Contributors

- @patinthehat made their first contribution in https://github.com/spatie/period/pull/102
- @kyryl-bogach made their first contribution in https://github.com/spatie/period/pull/103

**Full Changelog**: https://github.com/spatie/period/compare/2.3.0...2.3.1

## 2.3.0 - 2021-10-14

- Add `PeriodCollection::sort()` (#97)

## 2.2.0 - 2021-10-13

- Add `PeriodCollection::unique()` (#96)

## 2.1.3 - 2021-10-07

- Don't initialize Period::asString in constructor

## 2.1.2 - 2021-10-07

- Fix subtraction of empty PeriodCollection

## 2.1.1 - 2021-06-11

- Reindex collection array after filtering values (#87)

## 2.1.0 - 2021-03-24

- Add `PeriodCollection::subtract(PeriodCollection|Period $others)` (#84)
- Rename parameter `PeriodCollection::overlap(PeriodCollection $others)`
- Rename parameter `PeriodCollection::overlapAll(PeriodCollection ...$others)`

## 2.0.0 - 2021-03-17

- Bump required PHP version to `^8.0`
- Fix bug with `overlapAll` when no overlap
- All period properties are now typed, this affects you if you extend from `Period` or `PeriodCollection`
- Return types of several methods have been changed from `Period` to `static`
- `Period::duration()` returns an instance of `PeriodDuration`
- `Period::length()` now uses the Period's precision instead of always returning days
- `Period::overlap()` renamed to `Period::overlapAny()`
- `Period::overlapSingle()` renamed to `Period::overlap()`
- `Period::diff()` renamed to `Period::subtract()`
- `Period::subtract()` (previously `diff`) no longer returns the gap when there's no overlap
- `Period::diffSingle()` renamed to `Period::diffSymmetric()`
- `Period::contains()` now accepts both `DateTimeInterface` and `Period`
- `PeriodCollection::overlap()` now accepts one or several periods
- Renamed all getters like `getIncludedEnd()` and `getStart()` to `includedEnd()` and `start()`, etc.
- Add `Period::fromString()`
- Add `Period::asString()`

## 1.6.0 - 2021-02-24

- Add `Period::renew` (#74)

## 1.5.3 - 2020-12-03

- PHP8 compatibility

## 1.5.2 - 2020-11-19

- Keep timezone when boundaries are timezoned (#71)

## 1.5.1 - 2020-10-21

- Support multiple precisions when checking touchesWith (#68)

## 1.5.0 - 2020-03-31

- Add `filter` to `PeriodCollection`

## 1.4.5 - 2020-02-05

- Fix for PeriodCollection::gaps() with excluded boundaries (#58)

## 1.4.4 - 2019-08-05

- ~Performance improvement in `Period::contains()` (#46)~ edit: this change wasn't merged and targeted at 2.0

## 1.4.3 - 2019-07-09

- ~Improve iterator performance (#42)~ edit: this change wasn't merged and targeted at 2.0

## 1.4.2 - 2019-05-27

- Allow extension of Period that forces extension of DateTimeImmutable (#38)

## 1.4.1 - 2019-04-23

- Support PeriodCollection::make()
- Improved PeriodCollection doc blocks

## 1.4.0 - 2019-04-23

- Add `map` and `reduce` to `PeriodCollection`

## 1.3.1 - 2019-04-19

- Remove unused code

## 1.3.0 - 2019-04-19

- Add period collection add

## 1.2.0 - 2019-04-19

- Add period collection intersect

## 1.1.3 - 2019-04-05

- Even better docblock support for static return types

## 1.1.2 - 2019-04-05

- Better docblock support for static return types

## 1.1.1 - 2019-02-01

- Fix bug with null element in diff

## 1.1.0 - 2019-01-26

- Make Period iterable

## 1.0.0 - 2019-01-17

- First stable release

## 0.5.1 - 2019-01-14

- Fix bug with precision not being correctly copied

## 0.5.0 - 2019-01-09

- Add boundary and precision support

## 0.4.1 - 2019-01-08

- No overlap returns empty collection

## 0.4.0 - 2018-12-19

- Add visualizer

## 0.3.3 - 2018-12-18

- Support edge case for two period diffs

## 0.3.2 - 2018-12-11

- Add better return types to support inherited periods

## 0.3.0 - 2018-11-30

- Add `Period::contains`

## 0.2.0 - 2018-11-27

- Initial dev release
