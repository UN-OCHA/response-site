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

    // The user entity doesn't have a label column.
    if (isset($match)) {
      $query->condition('name', $match, $match_operator);
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

}
