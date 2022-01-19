<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'ocha_doc_store_assessment_document' widget.
 *
 * @FieldWidget (
 *   id = "ocha_doc_store_assessment_document_widget",
 *   label = @Translation("OCHA assessment document widget"),
 *   field_types = {
 *     "ocha_doc_store_assessment_document"
 *   }
 * )
 */
class OchaAssessmentDocumentWidget extends WidgetBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'endpoint' => 'http://docstore.local.docksal/api/v1/files',
      'api-key' => 'abcd',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

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
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Progress indicator: @progress_indicator', ['@progress_indicator' => $this->getSetting('progress_indicator')]);
    $summary[] = $this->t('Endpoint: @endpoint', ['@endpoint' => ocha_docstore_files_get_endpoint_base($this->getSetting('endpoint'))]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $state_name = $this->fieldDefinition->getName() . '[' . $delta . '][accessibility]';
    $element_process = $element['#process'] ?? [];
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->fieldDefinition->getLabel(),
    ];

    $element['document'] = [];
    $element['document']['#title'] = $this->t('Document');
    $element['document']['#process'] = array_merge($element_process,
    [[get_class($this), 'process']]);

    $element['accessibility'] = [
      '#type' => 'select',
      '#title' => $this->t('Accessibility'),
      '#options' => [
        'Not Applicable' => $this->t('Not Applicable'),
        'Publicly Available' => $this->t('Publicly Available'),
        'Available on Request' => $this->t('Available on Request'),
        'Not Available' => $this->t('Not Available'),
      ],
      '#default_value' => $items[$delta]->accessibility,
      '#weight' => -10,
    ];

    $element['instructions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Instructions'),
      '#default_value' => $items[$delta]->instructions,
      '#maxlength' => 255,
      '#weight' => 21,
      '#rows' => 5,
      '#attributes' => [
        'class' => [
          'js-text-full',
          'text-full',
        ],
      ],
      '#states' => [
        'visible' => [
          [':input[name="' . $state_name . '"]' => ['value' => 'Available on Request']],
          [':input[name="' . $state_name . '"]' => ['value' => 'Publicly Available']],
        ],
      ],
    ];

    $element['file'] = [
      '#type' => 'file',
      '#title' => $this->t('File'),
      '#default_value' => $items[$delta]->filename,
      '#maxlength' => 255,
      '#weight' => 22,
      '#states' => [
        'visible' => [
          ':input[name="' . $state_name . '"]' => ['value' => 'Publicly Available'],
        ],
      ],
    ];

    if ($items[$delta]->media_uuid) {
      $element['file']['#description'] = $this->t('Current file: @filename', [
        '@filename' => $items[$delta]->filename,
      ]);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // phpcs:ignore
    $all_files = \Drupal::request()->files->get('files', []);
    foreach ($all_files as $file_info) {
      if (!is_object($file_info)) {
        continue;
      }

      // Move file if it's rally uploaded.
      if (is_uploaded_file($file_info->getRealPath())) {
        $contents = file_get_contents($file_info->getRealPath());
        $filename = trim($file_info->getClientOriginalName(), '.');

        // phpcs:ignore
        $response = \Drupal::httpClient()->request(
          'POST',
          ocha_docstore_files_get_endpoint_base($this->getSetting('endpoint')),
          [
            'body' => json_encode([
              'filename' => $filename,
              'data' => base64_encode($contents),
              'private' => FALSE,
            ]),
            'headers' => [
              'API-KEY' => ocha_docstore_files_get_endpoint_apikey($this->getSetting('api-key')),
            ],
          ]
        );

        $body = $response->getBody() . '';
        $body = json_decode($body);

        // @todo Check return value.
        if ($body->uuid) {
          $uuids[] = $body->media_uuid;
          $field_state['existing_files'][] = $body->media_uuid;
        }
      }
    }

    return $values;
  }

  /**
   * Form API callback: Processes a file_generic field element.
   *
   * Expands the file_generic type to include the description and display
   * fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    return $element;
  }

  /**
   * Overrides \Drupal\Core\Field\WidgetBase::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    // Load the items for form rebuilds from the field state as they might not
    // be in $form_state->getValues() because of validation limitations. Also,
    // they are only passed in as $items when editing existing entities.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    if (isset($field_state['items'])) {
      $items->setValue($field_state['items']);
    }

    // Determine the number of widgets to display.
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $max = count($items);
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = ($cardinality > 1);
        break;
    }

    $title = $this->fieldDefinition->getLabel();
    $description = $this->getFilteredDescription();

    $elements = [];

    $delta = 0;
    // Add an element for every existing item.
    foreach ($items as $item) {
      $element = [
        '#title' => $title,
        '#description' => $description,
      ];
      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

      if ($element) {
        // Input field for the delta (drag-n-drop reordering).
        if ($is_multiple) {
          // We name the element '_weight' to avoid clashing with elements
          // defined by widget.
          $element['_weight'] = [
            '#type' => 'weight',
            '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
            '#title_display' => 'invisible',
            // Note: this 'delta' is the FAPI #type 'weight' element's property.
            '#delta' => $max,
            '#default_value' => $item->_weight ?: $delta,
            '#weight' => 100,
          ];
        }

        $elements[$delta] = $element;
        $delta++;
      }
    }

    $empty_single_allowed = ($cardinality == 1 && $delta == 0);
    $empty_multiple_allowed = ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED || $delta < $cardinality) && !$form_state->isProgrammed();

    // Add one more empty row for new uploads except when this is a programmed
    // multiple form as it is not necessary.
    if ($empty_single_allowed || $empty_multiple_allowed) {
      // Create a new empty item.
      $items->appendItem();
      $element = [
        '#title' => $title,
        '#description' => $description,
      ];
      $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);
      if ($element) {
        $elements[$delta] = $element;
      }
    }

    if ($is_multiple) {
      // The group of elements all-together need some extra functionality after
      // building up the full list (like draggable table rows).
      $elements['#file_upload_delta'] = $delta;
      $elements['#type'] = 'details';
      $elements['#open'] = TRUE;
      $elements['#theme'] = 'file_widget_multiple';
      $elements['#theme_wrappers'] = ['details'];
      $elements['#process'] = [[get_class($this), 'processMultiple']];
      $elements['#title'] = $title;

      $elements['#description'] = $description;
      $elements['#field_name'] = $field_name;
      $elements['#language'] = $items->getLangcode();
      // The field settings include defaults for the field type. However, this
      // widget is a base class for other widgets (e.g., ImageWidget) that may
      // act on field types without these expected settings.
      $field_settings = $this->getFieldSettings() + ['display_field' => NULL];
      $elements['#display_field'] = (bool) $field_settings['display_field'];

      // Add some properties that will eventually be added to the file upload
      // field. These are added here so that they may be referenced easily
      // through a hook_form_alter().
      $elements['#file_upload_title'] = $this->t('Add a new file');
      $elements['#file_upload_description'] = [
        '#theme' => 'file_upload_help',
        '#description' => '',
        '#upload_validators' => $elements[0]['#upload_validators'],
        '#cardinality' => $cardinality,
      ];
    }

    return $elements;
  }

}
