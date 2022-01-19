<?php

namespace Drupal\ocha_docstore_files;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\external_entities\ExternalEntityAccessControlHandler;

/**
 * Defines a generic access control handler for external entities.
 */
class OchaExternalEntityAccessControlHandler extends ExternalEntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // If not published, check edit access.
    if (in_array($operation, ['view label', 'view']) && $entity->hasField('field_published') && !$entity->field_published->value) {
      $result = AccessResult::allowedIfHasPermission($account, "update {$entity->getEntityTypeId()} external entity");
    }
    else {
      $result = parent::checkAccess($entity, $operation, $account);
    }

    return $result;
  }

}
