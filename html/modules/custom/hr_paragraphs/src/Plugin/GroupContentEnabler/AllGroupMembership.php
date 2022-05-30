<?php

namespace Drupal\hr_paragraphs\Plugin\GroupContentEnabler;

use Drupal\group\Plugin\GroupContentEnabler\GroupMembership;

/**
 * Allow selecting blocked users..
 */
class AllGroupMembership extends GroupMembership {

  /**
   * {@inheritdoc}
   */
  public function getEntityReferenceSettings() {
    $settings = parent::getEntityReferenceSettings();
    $settings['handler'] = 'default:all_users';

    return $settings;
  }

}
