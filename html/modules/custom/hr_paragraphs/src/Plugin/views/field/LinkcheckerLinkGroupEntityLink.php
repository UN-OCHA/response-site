<?php

namespace Drupal\hr_paragraphs\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupRelationship;
use Drupal\linkchecker\LinkCheckerLinkInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler that builds the group entity link for the linkchecker_link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("linkcheckerlink_group_entity_link")
 */
class LinkcheckerLinkGroupEntityLink extends FieldPluginBase {

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

    while ($linked_entity->getEntityTypeId() === 'paragraph' && $linked_entity->getParentEntity() !== NULL) {
      $linked_entity = $linked_entity->getParentEntity();
    }

    $parent_entity = $linked_entity;

    // Get group.
    $group_relationship_array = GroupRelationship::loadByEntity($linked_entity);
    $group_relationship = reset($group_relationship_array);
    if ($group_relationship) {
      $parent_entity = $group_relationship->getGroup();
    }

    if (!empty($this->options['absolute_link'])) {
      return $this->sanitizeValue($parent_entity->toUrl('canonical', ['absolute' => TRUE])->toString());
    }
    else {
      return $this->sanitizeValue($parent_entity->toUrl('canonical', ['absolute' => FALSE])->toString());
    }
  }

}
