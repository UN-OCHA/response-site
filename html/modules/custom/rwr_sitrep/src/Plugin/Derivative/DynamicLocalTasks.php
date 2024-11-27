<?php

namespace Drupal\rwr_sitrep\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines dynamic local tasks.
 */
class DynamicLocalTasks extends DeriverBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    // This task is for operation groups only, removed for other groups.
    // @see rwr_sitrep_menu_local_tasks_alter().
    $this->derivatives['rwr_sitrep.operation'] = $base_plugin_definition;
    $this->derivatives['rwr_sitrep.operation']['title'] = $this->t("Reports");
    $this->derivatives['rwr_sitrep.operation']['route_name'] = "view.group_nodes.page_1";
    $this->derivatives['rwr_sitrep.operation']['base_route'] = "entity.group.canonical";
    return parent::getDerivativeDefinitions($base_plugin_definition);

  }

}
