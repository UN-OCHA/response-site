<?php

/**
 * @file
 * Post update hooks.
 */

use Drupal\Core\Language\Language;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\redirect\Entity\Redirect;

/**
 * Update groups for enabled tabs and used paragraphs.
 */
function hr_paragraphs_post_update_track_usage_on_group(&$sandbox) {
  if (!isset($sandbox['total'])) {
    $ids = \Drupal::entityQuery('group')->accessCheck(FALSE)->execute();
    $sandbox['total'] = count($ids);
    $sandbox['current'] = 0;

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $ids = \Drupal::entityQuery('group')
    ->range($sandbox['current'], 25)
    ->sort('id')
    ->accessCheck(FALSE)
    ->execute();

  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\group\Entity\Group $groups[] */
  $groups = \Drupal::entityTypeManager()->getStorage('group')->loadMultiple($ids);
  foreach ($groups as $group) {
    $group->isMigrating = TRUE;
    $group->save();
    $sandbox['current']++;
  }

  \Drupal::messenger()->addMessage($sandbox['current'] . ' groups processed.');

  if ($sandbox['current'] >= $sandbox['total']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  }
}

/**
 * Update users for manager groups.
 */
function hr_paragraphs_post_update_track_managers(&$sandbox) {
  if (!isset($sandbox['total'])) {
    $ids = \Drupal::entityQuery('user')->accessCheck(FALSE)->execute();
    $sandbox['total'] = count($ids);
    $sandbox['current'] = 0;

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $ids = \Drupal::entityQuery('user')
    ->range($sandbox['current'], 25)
    ->sort('uid')
    ->accessCheck(FALSE)
    ->execute();

  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\user\Entity\User $users */
  $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($ids);
  foreach ($users as $user) {
    if ($user->manager_for->isEmpty()) {
      $user->isMigrating = TRUE;
      $user->save();
    }
    $sandbox['current']++;
  }

  \Drupal::messenger()->addMessage($sandbox['current'] . ' users processed.');

  if ($sandbox['current'] >= $sandbox['total']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  }
}

/**
 * Update nodes for used paragraph types.
 */
function hr_paragraphs_post_update_track_usage_on_node(&$sandbox) {
  if (!isset($sandbox['total'])) {
    $ids = \Drupal::entityQuery('node')->accessCheck(FALSE)->execute();
    $sandbox['total'] = count($ids);
    $sandbox['current'] = 0;

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $ids = \Drupal::entityQuery('node')
    ->accessCheck(FALSE)
    ->range($sandbox['current'], 25)
    ->sort('nid')
    ->execute();

  if (empty($sandbox['total'])) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\node\Entity\Node $nodes */
  $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
  foreach ($nodes as $node) {
    $node->isMigrating = TRUE;
    $node->save();

    $sandbox['current']++;
  }

  \Drupal::messenger()->addMessage($sandbox['current'] . ' nodes processed.');

  if ($sandbox['current'] >= $sandbox['total']) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  }
}

/**
 * Delete wrong aliases.
 */
function hr_paragraphs_post_update_rename_cameroun() {
  $query = Drupal::entityTypeManager()->getStorage('path_alias')->getQuery()->accessCheck(FALSE);
  $query->condition('alias', 'cameroun', 'CONTAINS');
  $ids = $query->execute();

  if (empty($ids)) {
    return;
  }

  /** @var \Drupal\path_alias\Entity\PathAlias[] $aliases */
  $aliases = Drupal::entityTypeManager()->getStorage('path_alias')->loadMultiple($ids);
  foreach ($aliases as $alias) {
    if (strpos($alias->getAlias(), '/cameroun/') !== FALSE) {
      $new = str_replace('/cameroun/', '/cameroon/', $alias->getAlias());
      $alias->setAlias($new);
      $alias->save();
    }
    elseif (strpos($alias->getAlias(), '/cameroun') === 0) {
      $new = str_replace('/cameroun', '/cameroon', $alias->getAlias());
      $alias->setAlias($new);
      $alias->save();
    }
  }
}

/**
 * Fix Afghanistan aliases.
 */
function hr_paragraphs_post_update_afghanistan_aliases(&$sandbox) {
  /** @var \Drupal\redirect\RedirectRepository $repository */
  $repository = \Drupal::service('redirect.repository');

  $entity_type_manager = \Drupal::entityTypeManager();
  $query = $entity_type_manager->getStorage('path_alias')->getQuery();
  $query->condition('alias', '/afganistan/%', 'LIKE');
  $path_ids = $query->execute();

  $aliases = PathAlias::loadMultiple($path_ids);
  foreach ($aliases as $alias) {
    $old_alias = $alias->getAlias();
    $new_alias = str_replace('/afganistan/', '/afghanistan/', $old_alias);
    $alias->setAlias($new_alias);
    $alias->save();

    if (!$repository->findMatchingRedirect($old_alias)) {
      Redirect::create([
        'redirect_source' => $old_alias,
        'redirect_redirect' => $new_alias,
        'language' => Language::LANGCODE_NOT_SPECIFIED,
        'status_code' => '301',
      ])->save();
    }
  }
}
