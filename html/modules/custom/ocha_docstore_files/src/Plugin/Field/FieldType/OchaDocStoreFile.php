<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'ocha_doc_store_file' field type.
 *
 * @FieldType(
 *   id = "ocha_doc_store_file",
 *   label = @Translation("OCHA Document store file"),
 *   description = @Translation("File field for files from the document store"),
 *   default_widget = "ocha_doc_store_file_widget",
 *   default_formatter = "ocha_doc_store_file_formatter"
 * )
 */
class OchaDocStoreFile extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'endpoint' => 'http://docstore.local.docksal/api/v1/files',
      'api-key' => 'abcd',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);

    $element['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API end point'),
      '#default_value' => $this->getSetting('endpoint'),
      '#weight' => 17,
    ];

    $element['api-key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $this->getSetting('api-key'),
      '#weight' => 18,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['media_uuid'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('UUID'))
      ->setRequired(TRUE);

    $properties['filename'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('File name'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(FALSE);

    $properties['uri'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('URI'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(FALSE);

    $properties['private'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Private'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'media_uuid' => [
          'type' => 'varchar',
          'binary' => FALSE,
        ],
        'filename' => [
          'type' => 'varchar',
          'binary' => TRUE,
        ],
        'uri' => [
          'type' => 'varchar',
          'binary' => TRUE,
        ],
        'private' => [
          'type' => 'boolean',
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['media_uuid'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $media_uuid = $this->get('media_uuid')->getValue();
    if (empty($media_uuid)) {
      return TRUE;
    }
    elseif ($media_uuid === 'queued') {
      $uri = $this->get('uri')->getValue();
      return empty($uri);
    }
    return !Uuid::isValid($media_uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // If this is a queued file, create it in the docstore and retrieve
    // the proper file information.
    if ($this->get('media_uuid')->getValue() === 'queued') {
      $uri = $this->get('uri')->getValue();
      $filename = $this->get('filename')->getValue();

      if (StreamWrapperManager::getScheme($uri) === 'temporary') {
        $payload = [
          'filename' => $filename,
          'data' => base64_encode(file_get_contents($uri)),
          'private' => FALSE,
        ];
      }
      else {
        $payload = [
          'filename' => $filename,
          'uri' => $uri,
          'private' => FALSE,
        ];
      }

      // Docstore API endpoint and key.
      $endpoint = ocha_docstore_files_get_endpoint_base($this->getSetting('endpoint'));
      $api_key = ocha_docstore_files_get_endpoint_apikey($this->getSetting('api-key'));

      // phpcs:ignore
      $response = \Drupal::httpClient()->request(
        'POST',
        $endpoint,
        [
          'body' => json_encode($payload),
          'headers' => [
            'API-KEY' => $api_key,
          ],
        ]
      );

      $body = $response->getBody() . '';
      $body = json_decode($body);

      // @todo Check return value.
      if (!empty($body->uuid)) {
        $this->setValue([
          'media_uuid' => $body->uuid,
          'uri' => $body->uri,
          'filename' => $body->filename,
          'private' => !empty($body->private),
        ]);
      }
      else {
        $this->setValue([]);
      }
    }
  }

}
