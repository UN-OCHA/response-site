# Reliefweb Operations

## Testing

Intergration tests using existing site/config.

```bash
fin exec XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite Existing --verbose
```

## Drupal check

```bash
fin exec php vendor/bin/drupal-check -ad -e */tests/*  html/modules/custom/hr_paragraphs
```

## Conventional changelog

- `composer run changelog` to create changelog
- `composer run release` to create new release
- `composer run release:patch` to create new release and bump patch version
- `composer run release:minor` to create new release and bump minor version
- `composer run release:major` to create new release and bump major version

### Commit messages

Full info availabel at https://www.conventionalcommits.org/en/v1.0.0/

Example

```txt
fix|feat|BREAKING CHANGE|docs|chore<optional>(scope)</optional>: <short title>

<optional>more information</optional>

Refs: #issue_number
<optional>BREAKING CHANGE: what will be broken by new feature</optional>
```
