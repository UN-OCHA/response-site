<?php

namespace Drupal\hr_paragraphs\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\group\Entity\Group;

/**
 * Checks access .
 */
class CustomAccessGroupType implements AccessInterface {

  /**
   * Group.
   *
   * @var mixed
   */
  protected $group;

  /**
   * Constructor.
   */
  public function __construct($group) {
    $this->group = $group;
  }

  /**
   * A custom access check.
   *
   * @param int $group
   *   Group Id from route.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access($group) {
    $group = Group::load($group);
    if ($group->bundle() == 'operation') {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
