# Reliefweb Response

## Testing

Intergration tests using existing site/config.

```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite Existing --verbose
```

## Drupal check

```bash
php vendor/bin/drupal-check -ad -e *Widget.php  html/modules/custom/hr_paragraphs/src
```

## Conventional changelog

- `composer run changelog` to create changelog
- `composer run release` to create new release
- `composer run release:patch` to create new release and bump patch version
- `composer run release:minor` to create new release and bump minor version
- `composer run release:major` to create new release and bump major version

## New release

On develop branch run the following

```bash
today=$(date +%d-%m-%Y)
git checkout -b $today-prep-release
composer run release
git add composer.json
git add CHANGELOG.md
git commit -m 'chore: $today prep release'
git push origin $today-prep-release
```

Merge to dev, [create PR to merge to main](https://github.com/UN-OCHA/response-site/compare/main...develop)

### Commit messages

Full info availabel at https://www.conventionalcommits.org/en/v1.0.0/

Example

```txt
fix|feat|BREAKING CHANGE|docs|chore<optional>(scope)</optional>: <short title>

<optional>more information</optional>

Refs: #issue_number
<optional>BREAKING CHANGE: what will be broken by new feature</optional>
```
