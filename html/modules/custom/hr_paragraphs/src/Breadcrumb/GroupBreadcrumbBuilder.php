<?php

namespace Drupal\hr_paragraphs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\Group;

/**
 * Provides a custom breadcrumb builder for group content type paths.
 */
class GroupBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $group = $route_match->getParameter('group');
    if (!$group) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    $group = $route_match->getParameter('group');

    if ($group->subgroup_tree->value && $group->subgroup_tree->value !== $group->id()) {
      $parent_group = Group::load($group->subgroup_tree->value);
      $breadcrumb->addLink($parent_group->toLink());
      $breadcrumb->addCacheableDependency($parent_group);
    }

    $breadcrumb->addLink($group->toLink());
    $breadcrumb->addCacheableDependency($group);
    $breadcrumb->addCacheContexts(['route']);

    if (strpos($route_match->getRouteName(), 'hr_paragraphs.operation.') !== FALSE) {
      $breadcrumb->addLink(Link::createFromRoute($route_match->getRouteObject()->getDefault('_title'), $route_match->getRouteName(), [
        'group' => $group->id(),
      ]));
    }

    return $breadcrumb;
  }

}
