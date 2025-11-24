<?php

declare(strict_types=1);

namespace Drupal\hr_paragraphs\Plugin\JsonLdEntity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\json_ld_schema\Entity\JsonLdEntityBase;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\Type;

/**
 * Base entity.
 */
class BaseEntity extends JsonLdEntityBase {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(EntityInterface $entity, $view_mode): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(EntityInterface $entity, $view_mode): Type {
    return Schema::thing();
  }

  /**
   * Build source reference.
   */
  protected function buildSourceReference(array $data): Type {
    return Schema::organization()
      ->identifier($data['fields']['url'])
      ->name($data['fields']['name'])
      ->url($data['fields']['url_alias']);
  }

  /**
   * Build country reference.
   */
  protected function buildCountryReference(array $data): Type {
    return Schema::country()
      ->identifier($data['fields']['url'])
      ->name($data['fields']['name'])
      ->url($data['fields']['url_alias']);
  }

}
