<?php

namespace Drupal\azure_tweaks\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Used for accessing configuration object factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('azure_tweaks.settings');
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('user.login.http')) {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('user.pass')) {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('user.pass.http')) {
      $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('user.register')) {
      $route->setRequirement('_access', 'FALSE');
    }

    // Deny access to user_create form.
    if ($this->config->get('disable_user_create')) {
      if ($route = $collection->get('user.admin_create')) {
        $route->setRequirement('_access', 'FALSE');
      }
    }
  }

}
