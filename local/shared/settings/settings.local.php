<?php

// @codingStandardsIgnoreFile

/**
 * @file
 * Local development override configuration feature.
 *
 * To activate this feature, copy and rename it such that its path plus
 * filename is 'sites/default/settings.local.php'. Then, go to the bottom of
 * 'sites/default/settings.php' and uncomment the commented lines that mention
 * 'settings.local.php'.
 *
 * If you are using a site name in the path, such as 'sites/example.com', copy
 * this file to 'sites/example.com/settings.local.php', and uncomment the lines
 * at the bottom of 'sites/example.com/settings.php'.
 */

/**
 * Enable local development services.
 */
$settings['container_yamls'][] = '/srv/www/shared/settings/services.yml';

/**
 * Show all error messages, with backtrace information.
 *
 * In case the error level could not be fetched from the database, as for
 * example the database connection failed, we rely only on this value.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;

/**
 * Disable the render cache.
 *
 * Note: you should test with the render cache enabled, to ensure the correct
 * cacheability metadata is present. However, in the early stages of
 * development, you may want to disable it.
 *
 * This setting disables the render cache by using the Null cache back-end
 * defined by the development.services.yml file above.
 *
 * Only use this setting once the site has been installed.
 */
# $settings['cache']['bins']['render'] = 'cache.backend.null';

/**
 * Disable caching for migrations.
 *
 * Uncomment the code below to only store migrations in memory and not in the
 * database. This makes it easier to develop custom migrations.
 */
# $settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';

/**
 * Disable Internal Page Cache.
 *
 * Note: you should test with Internal Page Cache enabled, to ensure the correct
 * cacheability metadata is present. However, in the early stages of
 * development, you may want to disable it.
 *
 * This setting disables the page cache by using the Null cache back-end
 * defined by the development.services.yml file above.
 *
 * Only use this setting once the site has been installed.
 */
# $settings['cache']['bins']['page'] = 'cache.backend.null';

/**
 * Disable Dynamic Page Cache.
 *
 * Note: you should test with Dynamic Page Cache enabled, to ensure the correct
 * cacheability metadata is present (and hence the expected behavior). However,
 * in the early stages of development, you may want to disable it.
 */
# $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
# $settings['extension_discovery_scan_tests'] = TRUE;

/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = TRUE;

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;

// Workaround for permission issues with NFS shares
$settings['file_chmod_directory'] = 0777;
$settings['file_chmod_file'] = 0666;

# File system settings.
$config['system.file']['path']['temporary'] = '/tmp';
$settings['file_private_path'] = '/srv/www/html/sites/default/private';

// Enable config_split locally.
$config['config_split.config_split.config_dev']['status'] = TRUE;

// Default sync directory.
$settings['config_sync_directory'] = '/srv/www/config';

// Hash salt.
$settings['hash_salt'] = 'local-site-salt';

// Ensure the dev_mod module configuration is not saved.
if (isset($settings['config_exclude_modules']) && is_array($settings['config_exclude_modules'])) {
  $settings['config_exclude_modules'][] = 'dev_mode';
} else {
  $settings['config_exclude_modules'] = ['dev_mode'];
}

// Ensure the devel module configuration is not saved.
if (isset($settings['config_exclude_modules']) && is_array($settings['config_exclude_modules'])) {
  $settings['config_exclude_modules'][] = 'devel';
} else {
  $settings['config_exclude_modules'] = ['devel'];
}

// Ensure the stage_file_proxy module configuration is not saved.
if (isset($settings['config_exclude_modules']) && is_array($settings['config_exclude_modules'])) {
  $settings['config_exclude_modules'][] = 'stage_file_proxy';
} else {
  $settings['config_exclude_modules'] = ['stage_file_proxy'];
}

$config['stage_file_proxy']['origin'] = 'https://response.reliefweb.int';
$config['stage_file_proxy']['origin_dir'] = 'sites/default/files';

// Enable/disable page/render caching and css/js aggregation.
$no_cache = TRUE;
if (!empty($no_cache)) {
  $config['system.performance']['css']['preprocess'] = FALSE;
  $config['system.performance']['js']['preprocess'] = FALSE;
  $settings['cache']['bins']['render'] = 'cache.backend.null';
  $settings['cache']['bins']['page'] = 'cache.backend.null';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
}

$settings['env_link_fixer_disabled'] = TRUE;

# $settings['env_link_fixer_custom_mappings'] = [
#   'response-local.test' => 'response.reliefweb.int',
# ]
// // Change kint depth_limit setting.
// if (class_exists('Kint')) {
//   // Set the depth_limit to prevent out-of-memory.
//   \Kint::$depth_limit = 4;
// }
