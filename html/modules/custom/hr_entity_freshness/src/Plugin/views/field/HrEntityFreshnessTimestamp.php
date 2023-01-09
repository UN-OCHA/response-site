<?php

namespace Drupal\hr_entity_freshness\Plugin\views\field;

use Drupal\views\Plugin\views\field\Date;

/**
 * Field handler to display the timestamp.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("hr_entity_freshness_timestamp")
 */
class HrEntityFreshnessTimestamp extends Date {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

}
