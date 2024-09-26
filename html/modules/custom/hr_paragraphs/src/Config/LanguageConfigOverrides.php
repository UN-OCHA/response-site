<?php

namespace Drupal\hr_paragraphs\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\FileStorageFactory;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Language config overrides.
 */
class LanguageConfigOverrides implements ConfigFactoryOverrideInterface {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language object used to override configuration data.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $language;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageDefault $default_language) {
    $this->configFactory = $config_factory;
    $this->language = $default_language->get();
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];

    foreach ($names as $name) {
      if (strpos($name, 'views.view.') === 0) {
        $file_storage = FileStorageFactory::getSync();
        $config = $file_storage->read($name);
        if (empty($config)) {
          continue;
        }

        if (!isset($config['display'])) {
          continue;
        }

        foreach ($config['display'] as $key => $display_info) {
          if (!isset($display_info['display_options'])) {
            continue;
          }

          $display_options = $display_info['display_options'];
          if (isset($display_options['title'])) {
            // phpcs:ignore
            $overrides[$name]['display'][$key]['display_options']['title'] = $this->t($display_options['title']);
          }
          if (isset($display_options['menu']) && isset($display_options['menu']['title'])) {
            // phpcs:ignore
            $overrides[$name]['display'][$key]['display_options']['menu']['title'] = $this->t($display_options['menu']['title']);
          }
        }
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return $this->language ? $this->language->getId() : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguage(?LanguageInterface $language = NULL) {
    $this->language = $language;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $metadata = new CacheableMetadata();
    if ($this->language) {
      $metadata->setCacheContexts(['languages:language_interface']);
    }
    return $metadata;
  }

}
