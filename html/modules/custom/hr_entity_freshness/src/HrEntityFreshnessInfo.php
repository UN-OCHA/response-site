<?php

namespace Drupal\hr_entity_freshness;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\group\Entity\Group;

/**
 * Add freshness info.
 */
class HrEntityFreshnessInfo implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['addFreshnessInfo'];
  }

  /**
   * Add freshness info.
   */
  public static function addFreshnessInfo($group_id) {
    $build = [];

    $group = Group::load($group_id);
    if ($timestamp = hr_entity_freshness_read($group)) {
      $build['hr_entity_freshness_timestamp'] = [
        '#type' => 'markup',
        '#markup' => '<div class="entity-freshness">' . t('Most recent content @date.', [
          '@date' => date('d M Y', $timestamp),
        ]) . '</div>',
      ];
    }

    return $build;
  }

}
