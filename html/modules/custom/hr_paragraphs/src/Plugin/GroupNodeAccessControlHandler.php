<?php

namespace Drupal\hr_paragraphs\Plugin;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Plugin\GroupContentAccessControlHandler;
use Drupal\node\Entity\Node;

/**
 * Provides access control for GroupContent entities and grouped entities.
 */
class GroupNodeAccessControlHandler extends GroupContentAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function entityAccess(EntityInterface $entity, $operation, AccountInterface $account, $return_as_object = FALSE) {
    $result = parent::entityAccess($entity, $operation, $account, TRUE);

    if ($operation == 'view' && !$account->hasPermission('access content overview')) {
      if ($entity instanceof Node) {
        /** @var \Drupal\group\Entity\Storage\GroupContentStorage */
        $storage = \Drupal::entityTypeManager()->getStorage('group_content');
        $activGroupListEntity = $storage->loadByEntity($entity);

        // Not a group node.
        if (empty($activGroupListEntity)) {
          return $return_as_object ? $result : $result->isAllowed();
        }

        foreach ($activGroupListEntity as $groupContent) {
          $group = $groupContent->getGroup();
          if (!$group->isPublished()) {
            $result = AccessResult::forbidden();
          }

          // Check parent.
          if ($group->hasField('subgroup_tree') && !$group->subgroup_tree->isEmpty()) {
            $group = Group::load($group->subgroup_tree->value);
            if (!$group->isPublished()) {
              $result = AccessResult::forbidden();
            }
          }
        }
      }
    }

    return $return_as_object ? $result : $result->isAllowed();
  }

}
