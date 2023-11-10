<?php

namespace Drupal\ocha_drd\Agent\Action\V8;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\drd_agent\Agent\Remote\Base;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the Requirements class.
 */
class OchaDrdRequirements extends Base {
  /**
   * The state system.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Drupal settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected Settings $settings;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): Base {
    return new static(
      $container,
      $container->get('account_switcher'),
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('datetime.time'),
      $container->get('state'),
      $container->get('settings')
    );
  }

  /**
   * Base constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $accountSwitcher
   *   The account switcher.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time.
   */
  final public function __construct(ContainerInterface $container, AccountSwitcherInterface $accountSwitcher, ConfigFactoryInterface $configFactory, Connection $database, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, Time $time, StateInterface $state, Settings $settings) {
    $this->container = $container;
    $this->accountSwitcher = $accountSwitcher;
    $this->configFactory = $configFactory;
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->time = $time;
    $this->state = $state;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(): array {
    $requirements = [];

    $current_release = $this->state->get('environment_indicator.current_release');
    $deployment_identifier = $this->settings->get('deployment_identifier');

    $requirements['ocha_drd.current_release'] = [
      'title' => 'Current release',
      'value' => empty($current_release) ? 'Not specified' : $current_release,
      'severity' => REQUIREMENT_OK,
    ];

    $requirements['ocha_drd.deployment_identifier'] = [
      'title' => 'Deployment identifier',
      'value' => empty($deployment_identifier) ? 'Not specified' : $deployment_identifier,
      'severity' => REQUIREMENT_OK,
    ];

    return $requirements;
  }

}
