<?php

namespace Drupal\hr_paragraphs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;

/**
 * Provides a custom breadcrumb builder for group content type paths.
 */
class GroupContentBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $node = $route_match->getParameter('node');
    if (!$node) {
      return FALSE;
    }

    $group_content_array = GroupContent::loadByEntity($node);
    $group_content = reset($group_content_array);
    $group = $group_content->getGroup();

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

    $node = $route_match->getParameter('node');

    $group_content_array = GroupContent::loadByEntity($node);
    $group_content = reset($group_content_array);
    $group = $group_content->getGroup();

    if ($group->subgroup_tree->value && $group->subgroup_tree->value !== $group->id()) {
      $parent_group = Group::load($group->subgroup_tree->value);
      $breadcrumb->addLink($parent_group->toLink());
      $breadcrumb->addCacheableDependency($parent_group);
    }

    $breadcrumb->addLink($group->toLink());
    $breadcrumb->addLink($node->toLink());

    $breadcrumb->addCacheableDependency($group);
    $breadcrumb->addCacheableDependency($node);

    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
