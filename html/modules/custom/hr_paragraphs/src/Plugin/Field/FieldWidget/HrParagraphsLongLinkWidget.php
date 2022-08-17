<?php

namespace Drupal\hr_paragraphs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'long link' widget.
 *
 * @FieldWidget(
 *   id = "hr_paragraphs_long_link",
 *   label = @Translation("HR Paragraphs Long Link Widget"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class HrParagraphsLongLinkWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $schema = $items[$delta]->getFieldDefinition()->getFieldStorageDefinition()->getSchema();
    $max_length = $schema['columns']['uri']['length'];

    $element['uri']['#maxlength'] = $max_length;

    return $element;
  }

}
