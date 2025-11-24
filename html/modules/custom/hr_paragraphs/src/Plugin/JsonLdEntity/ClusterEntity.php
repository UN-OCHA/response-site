<?php

declare(strict_types=1);

namespace Drupal\hr_paragraphs\Plugin\JsonLdEntity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

/**
 * A cluster entity.
 *
 * @JsonLdEntity(
 *   label = "Cluster Entity",
 *   id = "hr_paragraphs_cluster",
 * )
 */
class ClusterEntity extends BaseEntity {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(EntityInterface $entity, $view_mode): bool {
    // Make sure it is a group.
    if (!$entity instanceof Group) {
      return FALSE;
    }

    // Only apply to cluster.
    if ($entity->bundle() !== 'cluster') {
      return FALSE;
    }

    if (!$entity->hasField('field_organizations') || $entity->get('field_organizations')->isEmpty()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(EntityInterface $entity, $view_mode): Type {
    /** @var \Drupal\group\Entity\Group $entity */
    $organization_ids = $entity->get('field_organizations')->getValue();

    $reliefweb_api_client = \Drupal::service('hr_paragraphs.reliefweb_api_client');
    $organizations = $reliefweb_api_client->getOrganizationsById();

    $project = Schema::project();
    $project->name($entity->label());

    $orgs = [];
    foreach ($organization_ids as $organization_ref) {
      $organization_id = $organization_ref['value'];
      if (isset($organizations[$organization_id])) {
        $orgs[] = $this->buildSourceReference($organizations[$organization_id]);
      }
    }

    $project->member($orgs);
    return $project;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(EntityInterface $entity, $view_mode): CacheableMetadata {
    $cache_data = new CacheableMetadata();
    $cache_data->addCacheableDependency($entity);
    $cache_data->addCacheTags(['hr_paragraphs:reliefweb_organizations']);
    return $cache_data;
  }

}
