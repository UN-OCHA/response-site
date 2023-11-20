<?php

namespace Drupal\ocha_monitoring;

use Drupal\Component\Utility\Html;
use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\SensorRunner;
use Psr\Log\LoggerInterface;

/**
 * Converts monitoring sensor result objects.
 */
class OchaHealthcheckGenerator {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The sensor runner service.
   *
   * @var \Drupal\monitoring\SensorRunner
   */
  protected $sensorRunner;

  /**
   * Check results.
   *
   * @var \OhDear\HealthCheckResults\CheckResults
   */
  protected $checkResults;

  /**
   * Constructs an OhdearHealthcheckGenerator object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.channel.ohdear_integration service.
   * @param \Drupal\monitoring\SensorRunner $sensor_runner
   *   The sensor runner service.
   */
  public function __construct(LoggerInterface $logger, SensorRunner $sensor_runner) {
    $this->logger = $logger;
    $this->sensorRunner = $sensor_runner;
  }

  /**
   * Converts sensor result.
   */
  public function convertSensorResult(SensorResultInterface $sensorResult) : array {
    $sensor_id = $sensorResult->getSensorId();
    $sensor_config = $sensorResult->getSensorConfig();
    $label = $sensor_config->getLabel();
    $shortSummary = $sensor_config->getDescription() ?? '';
    $notificationMessage = Html::decodeEntities($sensorResult->getMessage());
    if ($value = $sensorResult->getValue()) {
      $meta = [
        'value' => $value,
        'value_label' => $sensor_config->getValueLabel(),
      ];
    }
    else {
      $meta = [];
    }

    return [
      'sensor_id' => $sensor_id,
      'label' => $label,
      'notification' => $notificationMessage,
      'summary' => $shortSummary,
      'status' => $sensorResult->getStatus(),
      'meta' => $meta,
    ];
  }

  /**
   * Runs all enabled sensors and converts them to OhDear healthchecks.
   *
   * @return string
   *   Json data of all monitoring sensor results converted to healthchecks.
   */
  public function getData() {
    $checkResults = [];
    $results = $this->sensorRunner->runSensors();

    foreach ($results as $result) {
      $checkResults[] = $this->convertSensorResult($result);
    }

    return $checkResults;
  }

}
