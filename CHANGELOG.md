# Changelog

All notable changes to `period` will be documented in this file

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
