<?php

namespace Drupal\hr_paragraphs\Plugin\Menu\LocalTask;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Local task plugin to render dynamic tab title dynamically.
 */
class DynamicTabLocalTask extends LocalTaskDefault {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle(?Request $request = NULL) {
    $group = $request->attributes->get('group');

    if ($group) {
      return $group->label->value;
    }

    return $this->t('Home');
  }

}
