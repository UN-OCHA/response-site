<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\group\Entity\Controller\GroupContentController;
use Drupal\group\Entity\GroupContentType;
use Drupal\group\Entity\GroupInterface;

/**
 * Returns responses for GroupContent routes.
 */
class GroupContentTitleController extends GroupContentController {

  /**
   * {@inheritdoc}
   */
  public function createFormTitle(GroupInterface $group, $plugin_id) {
    /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
    $plugin = $group->getGroupType()->getContentPlugin($plugin_id);
    $group_content_type = GroupContentType::load($plugin->getContentTypeConfigId());

    switch ($group_content_type->getContentPluginId()) {
      case 'subgroup:cluster':
        return $this->t('Add Cluster or Working Group');

      case 'group_node:page':
        return $this->t('Add Page');

    }

    return $this->t('Add @name', ['@name' => $group_content_type->label()]);
  }

}
