<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\group\Entity\Controller\GroupRelationshipController;
use Drupal\group\Entity\GroupRelationshipType;
use Drupal\group\Entity\GroupInterface;

/**
 * Returns responses for GroupContent routes.
 */
class GroupContentTitleController extends GroupRelationshipController {

  /**
   * {@inheritdoc}
   */
  public function createFormTitle(GroupInterface $group, $plugin_id) {
    /** @var \Drupal\group\Plugin\Group\Relation\GroupRelationInterface $plugin */
    $plugin = $group->getGroupType()->getPlugin($plugin_id);

    switch ($plugin->getRelationTypeId()) {
      case 'subgroup:cluster':
        return $this->t('Add Cluster or Working Group');

      case 'group_node:page':
        return $this->t('Add Page');

    }

    return $this->t('Add @name', ['@name' => $plugin->getRelationType()->getLabel()]);
  }

}
