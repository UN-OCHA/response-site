<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\link\LinkItemInterface;

/**
 * Plugin implementation of the 'ocha_assessment_document' field type.
 *
 * @FieldType (
 *   id = "ocha_doc_store_assessment_document",
 *   label = @Translation("OCHA assessment document (docstore)"),
 *   description = @Translation("OCHA assessment document."),
 *   category = @Translation("OCHA"),
 *   default_widget = "ocha_doc_store_assessment_document_widget",
 *   default_formatter = "ocha_doc_store_assessment_document_default"
 * )
 */
class OchaAssessmentDocument extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'title' => DRUPAL_OPTIONAL,
      'link_type' => LinkItemInterface::LINK_GENERIC,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->accessibility == 'Publicly Available') {
      if ($this->media_uuid !== NULL) {
        return FALSE;
      }
      if ($this->uri !== NULL) {
        return FALSE;
      }
    }

    if ($this->accessibility == 'Available on Request') {
      // Instructions is optional.
      return FALSE;
    }

    if ($this->accessibility == 'Not Available') {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];

    $properties['accessibility'] = DataDefinition::create('string')
      ->setLabel(t('Accessibility'));

    $properties['media_uuid'] = DataDefinition::create('string')
      ->setLabel(t('Media uuid'));

    $properties['filename'] = DataDefinition::create('string')
      ->setLabel(t('File name'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(FALSE);

    $properties['uri'] = DataDefinition::create('string')
      ->setLabel(t('URI'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Link text'));

    $properties['instructions'] = DataDefinition::create('string')
      ->setLabel(t('Instructions'))
      ->setSetting('case_sensitive', FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [];

    $schema['columns']['accessibility'] = [
      'description' => 'The link text.',
      'type' => 'varchar',
      'length' => 255,
    ];

    $schema['columns']['media_uuid'] = [
      'description' => 'Media uuid.',
      'type' => 'varchar',
      'length' => 255,
    ];

    $schema['columns']['filename'] = [
      'type' => 'varchar',
      'binary' => TRUE,
    ];

    $schema['columns']['uri'] = [
      'description' => 'The URI of the link.',
      'type' => 'varchar',
      'length' => 2048,
    ];

    $schema['columns']['title'] = [
      'description' => 'The link text.',
      'type' => 'varchar',
      'length' => 255,
    ];

    $schema['columns']['instructions'] = [
      'description' => 'Instructions text.',
      'type' => 'text',
      'size' => 'big',
    ];

    return $schema;
  }

}
