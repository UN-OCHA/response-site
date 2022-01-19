<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'ocha_doc_store_file_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "ocha_doc_store_file_formatter",
 *   label = @Translation("OCHA Document store file formatter"),
 *   field_types = {
 *     "ocha_doc_store_file"
 *   }
 * )
 */
class OchaDocStoreFileFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'raw_url_only' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['raw_url_only'] = [
      '#title' => $this->t('Display the raw URL only'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('raw_url_only'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => $this->viewValue($item),
      ];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    if ($item->private) {
      $output = $item->filename . ' (Private)';
    }
    else {
      $url = Url::fromUserInput('/attachments/' . $item->media_uuid . '/' . $item->filename, [
        'absolute' => $this->getSetting('raw_url_only'),
        'attributes' => [
          'target' => '_blank',
          'rel' => 'noopener noreferrer',
        ],
      ]);

      if ($this->getSetting('raw_url_only')) {
        $output = $url->toString();
      }
      else {
        $output = Link::fromTextAndUrl($item->filename, $url)->toString();
      }
    }
    return $output;
  }

}
