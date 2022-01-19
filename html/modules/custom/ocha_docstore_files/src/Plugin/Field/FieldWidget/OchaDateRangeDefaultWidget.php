<?php

namespace Drupal\ocha_docstore_files\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;

/**
 * Plugin implementation of the 'daterange_default' widget.
 *
 * @FieldWidget(
 *   id = "ocha_daterange_default",
 *   label = @Translation("Date range (OCHA)"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class OchaDateRangeDefaultWidget extends DateRangeDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Always hide time parts.
    $time_type = 'none';
    $time_format = '';

    $element['value']['#date_time_element'] = $time_type;
    $element['value']['#date_time_format'] = $time_format;

    $element['end_value']['#date_time_element'] = $time_type;
    $element['end_value']['#date_time_format'] = $time_format;

    return $element;
  }

}
