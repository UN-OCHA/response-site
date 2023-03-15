# Reliefweb Response

## Drupal 10

Download and enable https://www.drupal.org/project/flexible_permissions before updating!

### Issues

- [ ] https://www.drupal.org/project/components/issues/3278984 and https://www.drupal.org/project/components/issues/3299770
- [ ] https://www.drupal.org/project/csv_serialization/issues/3294354
- [ ] https://www.drupal.org/project/fullcalendar_api/issues/3340591
- [ ] https://www.drupal.org/project/linkchecker/issues/3271896 and https://www.drupal.org/project/linkchecker/issues/3335586
- [ ] https://www.drupal.org/project/maintenance200/issues/3288424
- [ ] https://www.drupal.org/project/override_node_options/issues/3269901
- [ ] https://www.drupal.org/project/subgroup/issues/3305343
- [ ] https://www.drupal.org/project/theme_switcher/issues/3290031

### Manual updates

- [ ] `composer require 'drupal/dynamic_entity_reference:^3.0'` is D10 only
- [x] `group` and `subgroup` needs to be updated to at least `2.x`

### Use patches with versions

1. Add [scripts/composer/DrupalLenientRequirement.php](https://raw.githubusercontent.com/UN-OCHA/rwint9-site/ca0d55ab6df1da02655a32a8fe0aa0d6714c2663/scripts/composer/DrupalLenientRequirement.php)
2. `composer require orakili/composer-drupal-info-file-patch-helper`
3. Add autload
    ```
      "autoload": {
          "classmap": [
              "scripts/composer/DrupalLenientRequirement.php"
          ]
      }
    ```
4. Add script
    ```
      "scripts": {
          "pre-pool-create": [
              "scripts\\composer\\DrupalLenientRequirement::changeVersionConstraint"
          ],
    ```
5. Add
    ```
        "drupal-lenient": {
            "constraint": "^10",
            "allowed-list": [
                "drupal/maintenance200",
                "drupal/theme_switcher"
            ]
        }
    ```

```bash
fin composer remove drupal/maintenance200 drupal/theme_switcher

fin composer remove --dev drupal/coder drupal/config_filter drupal/config_inspector drupal/console drupal/core-dev drupal/dev_mode drupal/devel_php marcocesarato/php-conventional-changelog mglaman/drupal-check phpcompatibility/php-compatibility phpspec/prophecy-phpunit phpunit/php-code-coverage phpunit/phpunit weitzman/drupal-test-traits

fin composer update

fin composer require 'drupal/core-recommended:^10' 'drupal/core-composer-scaffold:^10' 'drupal/core-project-message:^10' --update-with-dependencies --no-update

fin composer update

fin composer require drupal/maintenance200 drupal/theme_switcher

fin composer require --dev drupal/coder drupal/config_filter drupal/config_inspector drupal/core-dev marcocesarato/php-conventional-changelog mglaman/drupal-check phpcompatibility/php-compatibility phpspec/prophecy-phpunit phpunit/php-code-coverage phpunit/phpunit weitzman/drupal-test-traits

drush cr

drush updb -y
```

## Testing

Intergration tests using existing site/config.

```sh {name=runtests}
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite Existing --verbose
```

## Local development

For local development, see the stack repository.

Add this line to settings.local.php: `$config['config_split.config_split.config_dev']['status'] = TRUE;` to enable `config_split` and install the development modules.

After importing a fresh database, run `drush cim` to enable devel, database log and stage_file_proxy.

## Drupal check

```sh {name=drupalcheck}
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

```sh {name=changelog}
git fetch --all
today=$(date +%d-%m-%Y)
git checkout -b $today-prep-release
composer run release:patch
composer update
git add composer.json
git add CHANGELOG.md
git commit -m "chore: $today prep release"
git push origin $today-prep-release
gh_pr
```

- Merge to dev
- [create PR to merge to main](https://github.com/UN-OCHA/response-site/compare/main...develop)
- Merge to main
- [Tag a new release](./gh_release)
- Deploy

### Commit messages

Full info availabel at https://www.conventionalcommits.org/en/v1.0.0/

Example

```txt
fix|feat|BREAKING CHANGE|docs|chore<optional>(scope)</optional>: <short title>

<optional>more information</optional>

Refs: #issue_number
<optional>BREAKING CHANGE: what will be broken by new feature</optional>
```
