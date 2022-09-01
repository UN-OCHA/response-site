<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\group\Entity\Controller\GroupRelationshipController;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRelationshipType;

/**
 * Returns responses for GroupContent routes.
 */
class GroupContentTitleController extends GroupRelationshipController {

  /**
   * {@inheritdoc}
   */
  public function createFormTitle(GroupInterface $group, $plugin_id) {
    /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
    $plugin = $group->getGroupType()->getPlugin($plugin_id);
    $group_content_type = GroupRelationshipType::load($plugin->getContentTypeConfigId());

    switch ($group_content_type->getPluginId()) {
      case 'subgroup:cluster':
        return $this->t('Add Cluster or Working Group');

      case 'group_node:page':
        return $this->t('Add Page');

    }

    return $this->t('Add @name', ['@name' => $group_content_type->label()]);
  }

}
