<?php

namespace Drupal\hr_paragraphs\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * Plugin implementation of the 'link' field type.
 *
 * @FieldType(
 *   id = "long_link",
 *   label = @Translation("HR Paragraphs Long link"),
 *   description = @Translation("Stores a URL string, optional varchar link text, and optional blob of attributes to assemble a link."),
 *   default_widget = "hr_paragraphs_long_link",
 *   default_formatter = "link",
 *   constraints = {
 *     "LinkType" = {},
 *     "LinkAccess" = {},
 *     "LinkExternalProtocols" = {},
 *     "LinkNotExistingInternal" = {}
 *   }
 * )
 */
class HrParagraphsLongLinkItem extends LinkItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['uri']['length'] = 4096;

    return $schema;
  }

}
