<?php

namespace Drupal\hr_paragraphs;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * Long uri for links.
 */
class LongLinkItem extends LinkItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['uri']['length'] = 4096;

    return $schema;
  }

}
