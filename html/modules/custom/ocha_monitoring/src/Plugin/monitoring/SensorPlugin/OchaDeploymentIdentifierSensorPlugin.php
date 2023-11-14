<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\Core\Site\Settings;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors deployment identifier.
 *
 * @SensorPlugin(
 *   id = "ocha_deployment_identifier",
 *   label = @Translation("Deployment identifier"),
 *   description = @Translation("Deployment identifier."),
 *   addable = FALSE
 * )
 *
 * Based on the drupal core system state cron_last.
 */
class OchaDeploymentIdentifierSensorPlugin extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $deployment_identifier = Settings::get('deployment_identifier');
    $result->setValue($deployment_identifier ?? 'not specified');
    $result->setMessage($deployment_identifier ?? 'Not specified');

    if ($deployment_identifier) {
      $result->setStatus(SensorResultInterface::STATUS_OK);
    }
    else {
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
    }
  }

}
