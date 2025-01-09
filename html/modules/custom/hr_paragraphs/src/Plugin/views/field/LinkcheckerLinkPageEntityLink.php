<?php

namespace Drupal\hr_paragraphs\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\linkchecker\LinkCheckerLinkInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler that builds the page entity label for the linkchecker_link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("linkcheckerlink_page_entity_link")
 */
class LinkcheckerLinkPageEntityLink extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['absolute_link'] = [
      'default' => FALSE,
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['absolute_link'] = [
      '#title' => $this->t('Create absolute link'),
      '#description' => $this->t('Create absolute link.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['absolute_link']),
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $linkchecker_link = $this->getEntity($values);

    if (!$linkchecker_link instanceof LinkCheckerLinkInterface) {
      return '';
    }

    if (!$linkchecker_link->hasField('entity_id')) {
      return '';
    }

    if ($linkchecker_link->get('entity_id')->isEmpty()) {
      return '';
    }

    $linked_entity = $linkchecker_link->get('entity_id')->entity;

    if (!$linked_entity instanceof EntityInterface) {
      return '';
    }

    $revision_id = $linked_entity->getRevisionId();
    while ($linked_entity->getEntityTypeId() === 'paragraph' && $linked_entity->getParentEntity() !== NULL) {
      $linked_entity = $linked_entity->getParentEntity();

      $previous_revision = TRUE;
      $field_names = [];
      foreach ($linked_entity->getFields() as $field) {
        if ($field instanceof EntityReferenceRevisionsFieldItemList) {
          $field_names[] = $field->getName();
        }
      }

      foreach ($field_names as $field_name) {
        foreach ($linked_entity->$field_name->getValue() as $target_ids) {
          if ($revision_id == $target_ids['target_revision_id']) {
            $previous_revision = FALSE;
            continue;
          }
        }
      }

      if ($previous_revision) {
        $this->options['alter']['make_link'] = FALSE;
        return $this->t('The linked content originates from a prior revision of a paragraph.');
      }
    }

    if (!empty($this->options['absolute_link'])) {
      return $this->sanitizeValue($linked_entity->toUrl('canonical', ['absolute' => TRUE])->toString());
    }
    else {
      return $this->sanitizeValue($linked_entity->toUrl('canonical', ['absolute' => FALSE])->toString());
    }
  }

}
