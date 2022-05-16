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
    // Show child groups only on operations.
    if ($route = $collection->get('view.subgroups_of_a_group.page_1')) {
      $route->setRequirement('_custom_access_group_type', 'TRUE');
    }

    // Deny group managers access to delete form.
    if ($route = $collection->get('entity.group.delete_form')) {
      $route->setRequirement('_permission', 'administer group');
    }
  }

}
