<?php

namespace Drupal\paragraphs_group_permissions;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Defines a class containing permission callbacks.
 */
class ParagraphsTypeGroupPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of Paragraphs type permissions.
   */
  public function paragraphTypePermissions() {
    $perms = [];

    // Generate paragraph permissions for all Paragraphs types.
    foreach (ParagraphsType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Builds a standard list of node permissions for a given type.
   *
   * @param \Drupal\paragraphs\Entity\ParagraphsType $type
   *   The machine name of the node type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(ParagraphsType $type) {
    $type_id = $type->id();

    return [
      'view paragraph content ' . $type_id => [
        'title' => $type->label() . ': View content',
        'description' => 'Is able to view Paragraphs content of type ' . $type->label(),
      ],
      'create paragraph content ' . $type_id => [
        'title' => $type->label() . ': Create content',
        'description' => 'Is able to create Paragraphs content of type ' . $type->label(),
      ],
      'update paragraph content ' . $type_id => [
        'title' => $type->label() . ': Edit content',
        'description' => 'Is able to update Paragraphs content of type ' . $type->label(),
      ],
      'delete paragraph content ' . $type_id => [
        'title' => $type->label() . ': Delete content',
        'description' => 'Is able to delete Paragraphs content of type ' . $type->label(),
      ],
    ];
  }

}
