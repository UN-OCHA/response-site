<?php

namespace Drupal\ocha_monitoring\Plugin\monitoring\SensorPlugin;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorPlugin\SensorPluginBase;

/**
 * Monitors current PHP version.
 *
 * @SensorPlugin(
 *   id = "ocha_current_php_version",
 *   label = @Translation("Current PHP version"),
 *   description = @Translation("Current PHP version."),
 *   addable = FALSE
 * )
 *
 * Based on the drupal core system state cron_last.
 */
class OchaCurrentPhpVersionSensorPlugin extends SensorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function runSensor(SensorResultInterface $result) {
    $result->setValue(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION);
    $result->setMessage(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION);
    $result->setStatus(SensorResultInterface::STATUS_OK);
  }

}
