# Changelog

All notable changes to `period` will be documented in this file

## 2.0.0 - 2019-??-??

- `\Spatie\Period\PeriodCollection::overlapSingle` is no longer available,
`\Spatie\Period\PeriodCollection::overlap` should be used.
- Breaking Change: `Period::length()` now uses the Period's precision instead of always returning days
- Fix bug with `overlapAll` when no overlap

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
