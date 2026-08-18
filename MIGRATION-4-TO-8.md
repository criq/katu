# Migrating from katu 4.x to 8.x

> **Keep this document updated.** Any 8.x change that is breaking, deprecation-related, or that another app would have to repeat when leaving 4.x belongs here: add a row to [Tag changelog](#tag-changelog) and a short note under the matching section. Do not leave the work only in `AGENTS.md` or a consuming app's task spec.

This is the upgrade guide for **other codebases** on **`criq/katu` 4.x** (PHP 7.4). 8.x is a new major: **PHP 8.4 only**. 4.x stays on 7.4; there is no dual-runtime 8.x.

First consumer: [jidelniplan/jidelniplan](https://github.com/jidelniplan/jidelniplan) branch `ID-1033-php-84` (ticket ID-1033).

---

## Versioning

Tags are `{major}.YYYYMMDD.N`.

| Line | PHP | Branch | Notes |
|------|-----|--------|--------|
| **4.x** | 7.4 | `v4` | Current production for apps that have not moved |
| **8.x** | **≥ 8.4** | `php-8.4` | Runtime compatibility. Not a rewrite to `match`, enums, or constructor promotion in the 8.4 bump |

Require **`^8.20260818.2`** or later (not `^8.20260818.1`) so you get DateTime / ArrayAccess / model-hydration fixes.

Sibling first-party packages (`criq/sexy`, `criq/fatty`, `criq/effekt`, `criq/fono`, `criq/naam`, `criq/kleur`) also jumped to **8.x** with `"php": ">=8.4"` and `"criq/katu": "^8"`. Bump them together.

---

## What 8.x is not

Leave these for a later pass unless you are ready to take them on:

- Twig 3, Symfony 6, PHP-DI 7, PHPUnit 10, Monolog 3
- PHP 8 *syntax* modernization in app or katu (`match`, enums, property promotion, typed properties)
- Cutting production over before a soak on a non-www instance

katu 8 still requires **PHP-DI ^6** and **Twig 1/2** (`twig/extensions`). That **blocks Twig 3** until those requires change.

---

## Prerequisites

1. **Runtime PHP 8.4** (FPM / CLI / Docker / CI). Not 8.5 in the first pass (ecosystem ceiling; PhpSpreadsheet `<8.5`).
2. Composer `config.platform.php` **`8.4.0`** in the app (or omit platform and actually run 8.4).
3. A git branch so 7.4 production can stay on katu **4.x** until soak.

---

## Composer

In the **app**:

```json
{
  "require": {
    "php": ">=8.4",
    "criq/katu": "^8.20260818.2",
    "criq/sexy": "^8.20260818.2"
  },
  "config": {
    "platform": {
      "php": "8.4.0"
    }
  }
}
```

Include every `criq/*` package you use, all on **^8**.

Then:

```bash
# Do NOT use --with-all-dependencies / -W
composer update criq/katu criq/sexy criq/fatty criq/effekt criq/fono criq/naam criq/kleur --no-interaction
```

**Why not `-W`:** katu pins several Symfony/Monolog packages at `@stable`. A recursive update can jump to **Symfony 6/8** and **Monolog 3**, which PHP-DI 6 / `psr/log` 1 cannot follow. If you must lockless-install (CI, gitignored lockfile), add app-level **`conflict`** for `monolog/monolog >=3`, `psr/log >=2`, and Symfony 6+ components you actually use, and pin `symfony/event-dispatcher` to `^5.4` instead of `@stable`.

### Packages katu 8 no longer requires

| Package | PHP constraint on 4.x | Replacement |
|---------|----------------------|-------------|
| `jwage/easy-csv` | `^7.2` only | Use `league/csv` (already a katu require) or drop usage |
| `ralouphie/mimey` | no PHP 8 | Use another MIME helper; katu `File` no longer depends on it |

If **your app** required those only transitively, `composer prohibits php 8.4.0` should clear after the katu 8 bump. If you required them directly, replace or drop them in the app.

---

## App PHP you must fix

katu 8 runs on 8.4; **your** classes still need to be 8.4-legal. Turn `E_DEPRECATED` on for first-party code (app + `vendor/criq`) or you will miss these until production.

### Implicit nullable (fatal / deprecation)

```php
// 4.x / PHP 7.4 — deprecated on 8.4
public function foo(User $user = null)

// 8.x apps
public function foo(?User $user = null)
```

Same for `string $x = null` → `?string $x = null`. Union types (`string|null`) work on 8.4; if you still share a 7.4 tree, stay on `?Type`.

### Required parameter after optional

PHP 8 rejects `foo($a = null, $b)`. Give `$b` a default or drop the default on `$a`.

### LSP / interface return types

PHP 8.4 deprecates missing or incompatible return types on:

| Interface / parent | Typical fix |
|--------------------|-------------|
| `ArrayAccess::offsetSet` | `: void` |
| `ArrayAccess::offsetGet` | `: mixed` |
| `Iterator::current` / `key` | `: mixed` |
| `Iterator::rewind` / `next` | `: void` |
| `Iterator::valid` | `: bool` |
| `Countable::count` | `: int` |
| `DateTime::createFromTimestamp` | `int\|float $timestamp): static` if you subclass `DateTime` |

katu 8.20260818.2 already types its own collections and `Katu\Tools\Calendar\Time`. **Your** `ArrayAccess` subclasses still need the same.

`#[\ReturnTypeWillChange]` is a stopgap, not a substitute for `: void` / `: mixed` once you are 8.4-only.

### Null into string functions

`trim()`, `strlen()`, `mb_strlen()`, `preg_match` subject, `preg_split` subject, `str_pad` — passing **`null`** is deprecated. Use `?? ""`, `(string)`, or skip the call.

katu 8.20260818.2: `Locale::setPreference()` only `preg_match`es strings; `Time::__construct(null)` uses `"now"`; Sortable `str_pad` uses string pad args.

### Dynamic properties (PHP 8.2+)

Assigning an **undeclared** public property is deprecated. PDO hydration used to do `$object->$column = $value` for every selected column.

**katu 8.20260818.2** stores undeclared keys on `Katu\Models\Base` via `__set` / `__get` (`$_undeclaredProperties`). Declared public properties still win. `Model::getColumnValues()` reads `$this->{$column}`, so undeclared columns still persist.

You do **not** need `#[AllowDynamicProperties]` on every model. Prefer declaring real columns as `public $foo` with `@var` anyway.

`get_object_vars($model)` does **not** include the undeclared bag. Use `$model->columnName` or `getColumnValues()`. The deprecated `saveWithoutCallback()` path still uses `get_object_vars` filtered by table columns — prefer `persist()` / `persistWithoutCallbacks()`.

### `FILTER_SANITIZE_STRING`

Removed in PHP 8.1. katu 8 uses `FILTER_UNSAFE_RAW` in `ColumnDescription`. Grep your app for `FILTER_SANITIZE_STRING`.

### `realpath()` / path helpers

Pass a **string**. katu 8 casts `File` to string before `realpath()`.

### Deprecated model APIs (already on 4.x, still true)

- `Model::insert()` / `upsert()` / `save*()` — use `new` + setters + `persist()` or `persistWithoutCallbacks()`.
- Do not call `persist()` in tight bulk loops when callbacks are expensive; stamp `timePersisted` and `persistWithoutCallbacks()`.

---

## What katu 8 already did for you

You should **not** re-fix these in a fork:

- `"php": ">=8.4"`
- Dropped `jwage/easy-csv` and `ralouphie/mimey`
- `FILTER_UNSAFE_RAW` instead of `FILTER_SANITIZE_STRING`
- Explicit `?Type` on File, Setting, View, Route, and other implicit-nullable signatures
- `Time::createFromTimestamp(int|float $timestamp): static`
- `Time` constructor never passes `null` to `DateTime`
- ArrayAccess / Iterator return types on katu collections
- `Base` undeclared-property bag for hydration
- `Locale` / Sortable null-to-string deprecations

If a consuming app still sees those deprecations, it is on **8.20260818.1** or older — bump to **8.20260818.2+**.

---

## Suggested app sequence

1. Branch off production. Keep www / prod on PHP 7.4 + katu **4.x**.
2. Run PHP 8.4 in Docker (or equivalent) for that branch only.
3. `composer prohibits php 8.4.0` — fix lockfile blockers (often the two dropped packages above).
4. Bump `criq/*` to **^8** without `-W`.
5. Fix implicit nullable + required-after-optional + ArrayAccess in **app** code.
6. Run the unit suite. Then turn `E_DEPRECATED` on for `app/` + `vendor/criq/` (keep third-party vendor quiet until Twig 3 / PHP-DI 7).
7. Soak on a non-production instance. Cut www later.

---

## Tests

PHPUnit 9 still works. PHPUnit 10 is optional later.

If PHPUnit’s binary `require`s `vendor/autoload.php` **before** bootstrap, compile-time implicit-nullable deprecations in php-di / illuminate print unless the **process** starts with `E_DEPRECATED` off. Then bootstrap can set `E_ALL` and a filter that throws on first-party files only.

PHPUnit 9 will **not** install its error handler if bootstrap already registered one.

---

## Tag changelog

Add a row when you tag 8.x. Newest first.

| Tag | Date | Breaking / migrate notes |
|-----|------|--------------------------|
| **8.20260818.3** | 2026-08-18 | Added this file (`MIGRATION-4-TO-8.md`). No runtime change. |
| **8.20260818.2** | 2026-08-18 | `Time` DateTime LSP + null constructor; ArrayAccess/Iterator return types; `Base::__set`/`__get` for undeclared columns; Locale `preg_match` only on strings; Sortable `str_pad` strings |
| **8.20260818.1** | 2026-08-18 | First 8.x: `php >=8.4`; drop easy-csv + mimey; `FILTER_UNSAFE_RAW`; `realpath((string) File)`; explicit nullable signatures |

4.x tags are unchanged; do not backport 8.x-only signatures there.

---

## Sibling criq majors

| Package | 8.x floor (as of 2026-08-18) |
|---------|------------------------------|
| `criq/katu` | **8.20260818.2** |
| `criq/sexy` | **8.20260818.2** |
| `criq/fatty` | **8.20260818.2** |
| `criq/effekt` | 8.20260818.1 |
| `criq/fono` | 8.20260818.1 |
| `criq/kleur` | 8.20260818.1 |
| `criq/naam` | 8.20260818.1 (left 5.x) |

Sexy 8 types `ExpressionCollection` ArrayAccess/Iterator. Fatty 8 avoids `trim(null)` on calculator params.

After tagging katu, **push `php-8.4` and the tag to `origin` immediately** (Composer on servers only sees GitHub tags), then bump the app `composer.json`.
