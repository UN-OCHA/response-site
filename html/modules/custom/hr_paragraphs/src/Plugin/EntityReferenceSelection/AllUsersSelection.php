<?php

namespace Drupal\hr_paragraphs\Plugin\EntityReferenceSelection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\user\Plugin\EntityReferenceSelection\UserSelection;

/**
 * Provides an entity reference selection for all users.
 *
 * @EntityReferenceSelection(
 *   id = "default:all_users",
 *   label = @Translation("All users selection"),
 *   entity_types = {"user"},
 *   group = "default",
 *   weight = 1
 * )
 */
class AllUsersSelection extends UserSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = DefaultSelection::buildEntityQuery($match, $match_operator);

    // Allow blocked users.
    $query->accessCheck(FALSE);

    $configuration = $this->getConfiguration();

    // Only real users.
    $query->condition('uid', 1, '>');

    // Filter on email address.
    if (isset($match)) {
      $condition_group = $query->orConditionGroup();
      $condition_group->condition('mail', $match, $match_operator);
      $condition_group->condition('name', $match, $match_operator);
      $query->condition($condition_group);
    }

    // Filter by role.
    if (!empty($configuration['filter']['role'])) {
      $query->condition('roles', $configuration['filter']['role'], 'IN');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableNewEntities(array $entities) {
    // Mirror the conditions checked in buildEntityQuery().
    if ($role = $this->getConfiguration()['filter']['role']) {
      $entities = array_filter($entities, function ($user) use ($role) {
        /** @var \Drupal\user\UserInterface $user */
        return !empty(array_intersect($user->getRoles(), $role));
      });
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function entityQueryAlter(SelectInterface $query) {
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $target_type = $this->getConfiguration()['target_type'];

    $query = $this->buildEntityQuery($match, $match_operator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage($target_type)->loadMultiple($result);
    /** @var \Drupal\user\Entity\User */
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $options[$bundle][$entity_id] = $entity->label() . ' (' . $entity->getEmail() . ')';
    }

    return $options;
  }

}
