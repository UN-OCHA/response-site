<?php

declare(strict_types=1);

namespace Drupal\hr_paragraphs\Plugin\JsonLdEntity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;
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

  /**
   * Build events for group.
   */
  protected function buildEvents(Group $group): array {
    if (!$group->hasField('field_ical_url') || $group->get('field_ical_url')->isEmpty()) {
      return [];
    }

    /** @var \Drupal\hr_paragraphs\Controller\IcalController $ical_controller */
    $ical_controller = \Drupal::service('hr_paragraphs.ical_controller');
    $events = $ical_controller->getIcalEvents($group, NULL, NULL, TRUE);

    $event_references = [];
    foreach ($events as $event) {
      $event_references[] = $this->buildEventReference($event);
    }

    return $event_references;
  }

  protected function buildEventReference(array $data): Type {
    $event = Schema::event()
      ->name($data['title'])
      ->description($data['description'])
      ->startDate($data['start'])
      ->endDate($data['end']);

    if (!empty($data['location'])) {
      $event->location(
        Schema::place()->name($data['location'])
      );
    }
    return $event;
  }

}
