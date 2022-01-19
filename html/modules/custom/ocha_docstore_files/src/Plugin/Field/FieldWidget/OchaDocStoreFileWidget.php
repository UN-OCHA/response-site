<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Plugin implementation of the 'ocha_doc_store_file_widget' widget.
 *
 * @FieldWidget(
 *   id = "ocha_doc_store_file_widget",
 *   module = "ocha_docstore_files",
 *   label = @Translation("OCHA Document store file widget"),
 *   multiple_values = true,
 *   field_types = {
 *     "ocha_doc_store_file"
 *   }
 * )
 */
class OchaDocStoreFileWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ElementInfoManagerInterface $element_info) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->elementInfo = $element_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('element_info'));
  }

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
    return $this->formMultipleElements($items, $form, $form_state);
  }

  /**
   * Overrides \Drupal\Core\Field\WidgetBase::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    $ajax_wrapper_id = Html::getUniqueId('ajax-wrapper');

    $elements = [
      '#queue' => ['test'],
      'existing_files' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Existing files'),
        '#access' => FALSE,
      ],
      'deleted_files' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Files to be removed'),
        '#access' => FALSE,
      ],
      'queued_files' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Queued files'),
        '#access' => FALSE,
      ],
      'add_files' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Add files'),
        'from_local' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Local file(s)'),
          'local_file' => [
            '#type' => 'file',
            '#name' => 'files[]',
            '#multiple' => TRUE,
            '#error_no_message' => TRUE,
          ],
          'upload' => [
            '#type' => 'submit',
            '#value' => $this->t('Upload file(s)'),
            '#submit' => [[get_called_class(), 'addFileFromLocal']],
            '#limit_validation_errors' => [
              array_merge($parents, ['add_files', 'from_local']),
            ],
            '#ajax' => [
              'callback' => [get_called_class(), 'rebuildWidgetForm'],
              'wrapper' => $ajax_wrapper_id,
              'effect' => 'fade',
              'progress' => [
                'type' => 'throbber',
                'message' => $this->t('Uploading files'),
              ],
            ],
          ],
        ],
        'from_uri' => [
          '#type' => 'fieldset',
          '#title' => $this->t('External URL(s)'),
          'uri' => [
            '#type' => 'textarea',
            '#rows' => 2,
            '#description' => $this->t('One URL per line.'),
            '#default_value' => '',
          ],
          'fetch' => [
            '#type' => 'submit',
            '#value' => $this->t('Add file(s)'),
            '#submit' => [[get_called_class(), 'addFileFromUri']],
            '#limit_validation_errors' => [
              array_merge($parents, ['add_files', 'from_uri']),
            ],
            '#ajax' => [
              'callback' => [get_called_class(), 'rebuildWidgetForm'],
              'wrapper' => $ajax_wrapper_id,
              'effect' => 'fade',
              'progress' => [
                'type' => 'throbber',
                'message' => $this->t('Fetching files'),
              ],
            ],
          ],
        ],
      ],
    ];

    // Retrieve (and initialize if needed) the field widget state with the
    // list of existing, queued and deleted files.
    $field_state = static::getFieldState($parents, $field_name, $form_state, $items->getValue());

    // Common ajax settings for the remove/restore buttons.
    $ajax_settings = [
      'callback' => [get_called_class(), 'rebuildWidgetForm'],
      'wrapper' => $ajax_wrapper_id,
      'effect' => 'fade',
      'progress' => [
        'type' => 'throbber',
        'message' => NULL,
      ],
    ];

    $delta = 0;

    // Add an element for every existing item.
    foreach ($field_state['existing_files'] as $index => $item) {
      if (!empty($item['uri'])) {
        $elements['existing_files']['#access'] = TRUE;
        $elements['existing_files'][$delta] = [
          'filelink' => [
            '#type' => 'link',
            '#title' => $item['filename'],
            '#url' => Url::fromUserInput('/attachments/' . $item['media_uuid'] . '/' . $item['filename']),
          ],
          'remove' => [
            '#type' => 'submit',
            '#value' => $this->t('Remove file'),
            '#name' => 'remove-existing-' . $delta,
            '#index' => $index,
            '#submit' => [[get_called_class(), 'removeExistingFile']],
            '#limit_validation_errors' => [
              array_merge($parents, ['existing_files', $index]),
            ],
            '#ajax' => $ajax_settings,
          ],
        ];
      }

      $delta++;
    }

    // Add deleted files.
    foreach ($field_state['deleted_files'] as $index => $item) {
      if (!empty($item['uri'])) {
        $elements['deleted_files']['#access'] = TRUE;
        $elements['deleted_files'][] = [
          'filelink' => [
            '#type' => 'link',
            '#title' => $item['filename'],
            '#url' => Url::fromUri($item['uri']),
          ],
          'restore' => [
            '#type' => 'submit',
            '#value' => $this->t('Restore deleted file'),
            '#name' => 'restore-deleted-' . $delta,
            '#index' => $index,
            '#submit' => [[get_called_class(), 'restoreDeletedFile']],
            '#limit_validation_errors' => [
              array_merge($parents, ['deleted_files', $index]),
            ],
            '#ajax' => $ajax_settings,
          ],
        ];
      }

      $delta++;
    }

    // Add all queued files.
    foreach ($field_state['queued_files'] as $index => $item) {
      if (!empty($item['uri'])) {
        // Uploaded file.
        if (StreamWrapperManager::getScheme($item['uri']) === 'temporary') {
          $filelink = [
            '#type' => 'markup',
            '#markup' => $item['filename'],
          ];
        }
        // Remote file.
        else {
          $filelink = [
            '#type' => 'link',
            '#title' => $item['filename'],
            '#url' => Url::fromUri($item['uri']),
          ];
        }

        $elements['queued_files']['#access'] = TRUE;
        $elements['queued_files'][] = [
          'filelink' => $filelink,
          'remove' => [
            '#type' => 'submit',
            '#value' => $this->t('Remove queued file'),
            '#skip_massage' => TRUE,
            '#name' => 'remove-queued-' . $delta,
            '#index' => $index,
            '#submit' => [[get_called_class(), 'removeQueuedFile']],
            '#limit_validation_errors' => [
              array_merge($parents, ['queued_files', $index]),
            ],
            '#ajax' => $ajax_settings,
          ],
        ];
      }

      $delta++;
    }

    $elements['#tree'] = TRUE;
    $elements['#prefix'] = '<div id="' . $ajax_wrapper_id . '">';
    $elements['#suffix'] = '</div>';

    return $elements;
  }

  /**
   * Queue uploaded files.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function addFileFromLocal(array &$form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Retrieve the current field state with the list of existing, queued and
    // deleted files.
    $field_name = $widget['#field_name'];
    $field_parents = $widget['#field_parents'];
    $field_state = static::getFieldState($field_parents, $field_name, $form_state);

    // Get uploaded files.
    $all_files = \Drupal::request()->files->get('files', []);

    // Add files without uploading.
    // @todo prevent upload of the same file?
    foreach ($all_files as $field) {
      foreach ($field as $file_info) {
        // Move file if it's rally uploaded.
        if (is_uploaded_file($file_info->getRealPath())) {
          $destination = 'temporary://' . microtime();
          if (\Drupal::service('file_system')->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY)) {
            $destination .= '/' . trim($file_info->getClientOriginalName(), '.');
            if (move_uploaded_file($file_info->getRealPath(), $destination)) {
              $field_state['queued_files'][] = [
                'filename' => trim($file_info->getClientOriginalName(), '.'),
                'uri' => $destination,
              ];
            }
          }
        }
      }
    }

    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Queue remote files.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function addFileFromUri(array &$form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Retrieve the current field state with the list of existing, queued and
    // deleted files.
    $field_name = $widget['#field_name'];
    $field_parents = $widget['#field_parents'];
    $field_state = static::getFieldState($field_parents, $field_name, $form_state);

    // Get the parents for the uri field.
    $element_parents = array_slice($button['#parents'], 0, -1);
    $element_parents[] = 'uri';

    // We use the user input because for some reason the `uri` is not available
    // in the form state values.
    $from_uri = NestedArray::getValue($form_state->getUserInput(), $element_parents);

    if (!empty($from_uri)) {
      $uris = explode("\n", $from_uri);
      foreach ($uris as $uri) {
        $uri = trim($uri);
        if (empty($uri)) {
          continue;
        }

        // Skip if there is already a file with the same url.
        foreach ($field_state['queued_files'] as $queued_file) {
          if (isset($queued_file['uri']) && $queued_file['uri'] === $uri) {
            continue 2;
          }
        }

        $field_state['queued_files'][] = [
          'filename' => basename(parse_url($uri, PHP_URL_PATH)),
          'uri' => $uri,
        ];
      }
    }

    // Clear entered URIs.
    NestedArray::setValue($form_state->getUserInput(), $element_parents, NULL);

    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Remove existing files.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function removeExistingFile(array &$form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Retrieve the current field state with the list of existing, queued and
    // deleted files.
    $field_name = $widget['#field_name'];
    $field_parents = $widget['#field_parents'];
    $field_state = static::getFieldState($field_parents, $field_name, $form_state);

    // Remove the file matching the delta of the button.
    $index = $button['#index'];
    if (isset($field_state['existing_files'][$index])) {
      $field_state['deleted_files'][] = $field_state['existing_files'][$button['#index']];
      unset($field_state['existing_files'][$index]);

      // Re-key the array.
      $field_state['existing_files'] = array_values($field_state['existing_files']);
    }

    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Restore a removed existing files.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function restoreDeletedFile(array &$form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Retrieve the current field state with the list of existing, queued and
    // deleted files.
    $field_name = $widget['#field_name'];
    $field_parents = $widget['#field_parents'];
    $field_state = static::getFieldState($field_parents, $field_name, $form_state);

    // Restore the file matching the delta of the button.
    $index = $button['#index'];
    if (isset($field_state['deleted_files'][$index])) {
      $field_state['existing_files'][] = $field_state['deleted_files'][$button['#index']];
      unset($field_state['deleted_files'][$index]);

      // Re-key the array.
      $field_state['deleted_files'] = array_values($field_state['deleted_files']);
    }

    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Remove a queued files.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function removeQueuedFile(array &$form, FormStateInterface &$form_state) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    // Retrieve the current field state with the list of existing, queued and
    // deleted files.
    $field_name = $widget['#field_name'];
    $field_parents = $widget['#field_parents'];
    $field_state = static::getFieldState($field_parents, $field_name, $form_state);

    // Restore the file matching the delta of the button.
    $index = $button['#index'];
    if (isset($field_state['queued_files'][$index])) {
      unset($field_state['queued_files'][$index]);

      // Re-key the array.
      $field_state['queued_files'] = array_values($field_state['queued_files']);
    }

    static::setWidgetState($field_parents, $field_name, $form_state, $field_state);
    $form_state->setRebuild();
  }

  /**
   * Get the field state, initializing it if necessary.
   *
   * @param array $parents
   *   Form element parents.
   * @param string $field_name
   *   Field name.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $items
   *   Existing items to initialize the state with.
   *
   * @return array
   *   Field state.
   */
  public static function getFieldState(array $parents, $field_name, FormStateInterface &$form_state, array $items = []) {
    $initialize = FALSE;
    $field_state = static::getWidgetState($parents, $field_name, $form_state);

    if (!isset($field_state['queued_files'])) {
      $field_state['queued_files'] = array_filter($items, function ($item) {
        return !empty($item['media_uuid']) && $item['media_uuid'] === 'queued';
      });
      $initialize = TRUE;
    }
    if (!isset($field_state['deleted_files'])) {
      $field_state['deleted_files'] = [];
      $initialize = TRUE;
    }
    if (!isset($field_state['existing_files'])) {
      $field_state['existing_files'] = array_filter($items, function ($item) {
        return !empty($item['media_uuid']) && Uuid::isValid($item['media_uuid']);
      });
      $initialize = TRUE;
    }

    if ($initialize) {
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    return $field_state;
  }

  /**
   * Rebuild form.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response of the ajax upload.
   */
  public static function rebuildWidgetForm(array &$form, FormStateInterface &$form_state, Request $request) {
    $button = $form_state->getTriggeringElement();
    $widget = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    $response = new AjaxResponse();
    $response->setAttachments($form['#attached']);

    return $response->addCommand(new ReplaceCommand(NULL, $widget));
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);

    // Combine the existing files and the queued ones. The queued files will
    // be saved in the docstore in the preSave() of the field type.
    // @see Drupal\ocha_docstore_files\Plugin\Field\FieldType::preSave()
    $massaged = [];
    foreach ($field_state['existing_files'] as $item) {
      $massaged[] = $item;
    }
    foreach ($field_state['queued_files'] as $item) {
      $massaged[] = $item + ['media_uuid' => 'queued'];
    }
    return $massaged;
  }

}
