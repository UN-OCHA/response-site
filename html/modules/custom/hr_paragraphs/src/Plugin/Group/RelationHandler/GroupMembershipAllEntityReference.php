<?php

namespace Drupal\hr_paragraphs\Plugin\Group\RelationHandler;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\group\Plugin\Group\RelationHandler\GroupMembershipEntityReference;

/**
 * Configures the entity reference for the group_membership relation plugin.
 */
class GroupMembershipAllEntityReference extends GroupMembershipEntityReference {

  /**
   * {@inheritdoc}
   */
  public function configureField(BaseFieldDefinition $entity_reference) {
    $this->parent->configureField($entity_reference);

    $handler_settings = $entity_reference->getSetting('handler_settings');
    $handler_settings['include_anonymous'] = FALSE;

    $entity_reference->setSetting('handler', 'default:all_users');
    $entity_reference->setSetting('handler_settings', $handler_settings);
  }

}
