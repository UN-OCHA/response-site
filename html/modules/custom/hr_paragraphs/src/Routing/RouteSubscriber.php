<?php

namespace Drupal\hr_paragraphs\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('view.subgroups_of_a_group.page_1')) {
      $route->setRequirement('_custom_access_group_type', 'TRUE');
    }
  }

}
