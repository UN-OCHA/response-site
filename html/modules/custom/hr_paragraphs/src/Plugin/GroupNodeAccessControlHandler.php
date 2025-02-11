<?php

namespace Drupal\hr_paragraphs\Plugin;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Plugin\Group\RelationHandlerDefault\AccessControl;
use Drupal\node\Entity\Node;

/**
 * Provides access control for GroupRelationship entities and grouped entities.
 */
class GroupNodeAccessControlHandler extends AccessControl {

  /**
   * {@inheritdoc}
   */
  public function entityAccess(EntityInterface $entity, $operation, AccountInterface $account, $return_as_object = FALSE) {
    $result = parent::entityAccess($entity, $operation, $account, TRUE);

    if ($operation == 'view' && !$account->hasPermission('access content overview')) {
      if ($entity instanceof Node) {
        /** @var \Drupal\group\Entity\Storage\GroupRelationshipStorage */
        $storage = \Drupal::entityTypeManager()->getStorage('group_relationship');
        $activeGroupListEntity = $storage->loadByEntity($entity);

        // Not a group node.
        if (empty($activeGroupListEntity)) {
          return $return_as_object ? $result : $result->isAllowed();
        }

        foreach ($activeGroupListEntity as $groupRelationship) {
          $group = $groupRelationship->getGroup();
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
