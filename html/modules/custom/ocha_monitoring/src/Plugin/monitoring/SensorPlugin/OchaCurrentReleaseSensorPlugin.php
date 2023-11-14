<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors current release.
 *
 * @SensorPlugin(
 *   id = "ocha_current_release",
 *   label = @Translation("Current release"),
 *   description = @Translation("Current release."),
 *   addable = FALSE
 * )
 *
 * Based on the drupal core system state cron_last.
 */
class OchaCurrentReleaseSensorPlugin extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $current_release = \Drupal::state()->get('environment_indicator.current_release');
    $result->setValue($current_release ?? 'not specified');
    $result->setMessage($current_release ?? 'Not specified');

    if ($current_release) {
      $result->setStatus(SensorResultInterface::STATUS_OK);
    }
    else {
      $result->setStatus(SensorResultInterface::STATUS_UNKNOWN);
    }
  }

}
