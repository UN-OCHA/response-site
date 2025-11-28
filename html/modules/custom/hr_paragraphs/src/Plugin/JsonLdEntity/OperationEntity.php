<?php

declare(strict_types=1);

namespace Drupal\hr_paragraphs\Plugin\JsonLdEntity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

/**
 * An operation entity.
 *
 * @JsonLdEntity(
 *   label = "Operation Entity",
 *   id = "hr_paragraphs_operation",
 * )
 */
class OperationEntity extends BaseEntity {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(EntityInterface $entity, $view_mode): bool {
    // Make sure it is a group.
    if (!$entity instanceof Group) {
      return FALSE;
    }

    // Only apply to operation.
    if ($entity->bundle() !== 'operation') {
      return FALSE;
    }

    if (!$entity->hasField('field_countries') || $entity->get('field_countries')->isEmpty()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(EntityInterface $entity, $view_mode): Type {
    /** @var \Drupal\group\Entity\Group $entity */
    $country_ids = $entity->get('field_countries')->getValue();

    $reliefweb_api_client = \Drupal::service('hr_paragraphs.reliefweb_api_client');
    $countries = $reliefweb_api_client->getCountriesById();

    $area = Schema::administrativeArea();
    $area->name($entity->label());
    $area->identifier($entity->toUrl('canonical', ['absolute' => TRUE, 'path_processing' => FALSE])->toString());
    $area->url($entity->toUrl('canonical', ['absolute' => TRUE])->toString());

    $places = [];
    foreach ($country_ids as $country_ref) {
      $country_id = $country_ref['value'];
      if (!isset($countries[$country_id])) {
        continue;
      }
      $places[] = $this->buildCountryReference($countries[$country_id]);
    }

    if (!empty($places)) {
      $area->containsPlace($places);
    }

    $events = $this->buildEvents($entity);
    if (!empty($events)) {
      $area->event($events);
    }

    return $area;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(EntityInterface $entity, $view_mode): CacheableMetadata {
    $cache_data = new CacheableMetadata();
    $cache_data->addCacheableDependency($entity);
    $cache_data->addCacheTags(['hr_paragraphs:reliefweb_countries']);
    return $cache_data;
  }

}
