<?php

namespace Drupal\hr_paragraphs\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupRelationship;
use Drupal\linkchecker\LinkCheckerLinkInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler that builds the group entity label for the linkchecker_link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("linkcheckerlink_group_entity_label")
 */
class LinkcheckerLinkGroupEntityLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['link_to_entity'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['link_to_entity'] = [
      '#title' => $this->t('Link to entity'),
      '#description' => $this->t('Make entity label a link to entity page.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['link_to_entity']),
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

    if (!empty($this->options['link_to_entity'])) {
      try {
        $this->options['alter']['url'] = $parent_entity->toUrl();
        $this->options['alter']['make_link'] = TRUE;
      }
      catch (UndefinedLinkTemplateException $e) {
        $this->options['alter']['make_link'] = FALSE;
      }
      catch (EntityMalformedException $e) {
        $this->options['alter']['make_link'] = FALSE;
      }
    }

    return $this->sanitizeValue($parent_entity->label());
  }

}
