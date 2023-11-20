<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors current Drupal version.
 *
 * @SensorPlugin(
 *   id = "ocha_current_drupal_version",
 *   label = @Translation("Current drupal version"),
 *   description = @Translation("Current drupal version."),
 *   addable = FALSE
 * )
 *
 * Based on the drupal core system state cron_last.
 */
class OchaCurrentDrupalVersionSensorPlugin extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $result->setValue(\DRUPAL::VERSION);
    $result->setMessage(\DRUPAL::VERSION);
    $result->setStatus(SensorResultInterface::STATUS_INFO);
  }

}
