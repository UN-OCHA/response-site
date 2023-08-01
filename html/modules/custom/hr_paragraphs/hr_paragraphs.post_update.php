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
  $ids = $query->accessCheck(FALSE)->execute();

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
  $path_ids = $query->accessCheck(FALSE)->execute();

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

/**
 * Set status for back links.
 */
function hr_paragraphs_post_update_set_backlinks_status(&$sandbox) {
  $logger = \Drupal::logger('hr_paragraphs');

  if (!isset($sandbox['total'])) {
    $ids = \Drupal::entityQuery('linkcheckerlink')
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->notExists('link_type')
      ->execute();
    $sandbox['total'] = count($ids);
    $sandbox['last_id'] = 0;
    $sandbox['current'] = 0;

    $logger->notice('Found ' . $sandbox['total'] . ' links to check');

    if (empty($sandbox['total'])) {
      $sandbox['#finished'] = 1;
      return;
    }
  }

  $ids = \Drupal::entityQuery('linkcheckerlink')
    ->accessCheck(FALSE)
    ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
    ->condition('lid', $sandbox['last_id'], '>')
    ->notExists('link_type')
    ->sort('lid')
    ->execute();

  if (empty($ids)) {
    $sandbox['#finished'] = 1;
    return;
  }

  /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
  $links = \Drupal::entityTypeManager()->getStorage('linkcheckerlink')->loadMultiple($ids);
  foreach ($links as $link) {
    $link_type = '';
    $original_url = $link->getUrl();
    $url = parse_url($original_url, PHP_URL_PATH);

    $logger->notice($sandbox['current'] . '. Analyzing: ' . $url);

    // Operation, cluster or document.
    $parts = explode('/', $url);

    // Remove language.
    if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
      unset($parts[1]);
      $parts = array_values($parts);
    }

    // Operation in other language.
    if ($parts[1] === 'op%C3%A9rations') {
      $parts[1] = 'operations';
    }

    // Home.
    if (count($parts) === 1) {
      $link_type = 'home';
    }

    // Files.
    if ($parts[1] === 'sites' && $parts[3] === 'files') {
      $link_type = 'file';
    }

    // Private files.
    elseif ($parts[1] === 'file' && is_numeric($parts[2])) {
      $link_type = 'private file';
    }
    elseif ($parts[1] === 'system' && $parts[2] === 'files') {
      $link_type = 'private file';
    }

    // Nodes.
    elseif (count($parts) === 3 && $parts[1] === 'node') {
      $link_type = 'node';
    }

    // Applications.
    elseif (count($parts) === 3 && $parts[1] === 'applications') {
      $link_type = 'application';
    }

    // Document or infographic.
    elseif (count($parts) === 3 && $parts[1] === 'document') {
      $link_type = 'document';
    }
    elseif (count($parts) === 3 && $parts[1] === 'infographic') {
      $link_type = 'infographic';
    }

    // Documents or infographics.
    elseif (count($parts) === 3 && $parts[1] === 'documents') {
      $link_type = 'documents';
    }
    elseif (count($parts) === 3 && $parts[1] === 'infographics') {
      $link_type = 'infographics';
    }

    // Operations, clusters, ...
    elseif ($parts[1] === 'operations' || $parts[1] === 'topics') {
      if (count($parts) == 3) {
        $link_type = 'operation';
      }
      elseif (count($parts) == 4) {
        $link_type = 'cluster';
      }
      elseif (count($parts) == 5) {
        if ($parts[3] === 'document') {
          $link_type = 'document';
        }
        elseif ($parts[3] === 'infographic') {
          $link_type = 'infographic';
        }
        elseif ($parts[3] === 'assessment') {
          $link_type = 'assessment';
        }
        elseif ($parts[3] === 'documents') {
          $link_type = 'documents';
        }
        elseif ($parts[3] === 'infographics') {
          $link_type = 'infographics';
        }
        elseif ($parts[4] === 'documents') {
          $link_type = 'documents';
        }
        elseif ($parts[4] === 'infographics') {
          $link_type = 'infographics';
        }
      }
      elseif (count($parts) > 5) {
        if ($parts[3] === 'documents') {
          $link_type = 'documents';
        }
        elseif ($parts[3] === 'infographics') {
          $link_type = 'infographics';
        }
        elseif ($parts[4] === 'documents') {
          $link_type = 'documents';
        }
        elseif ($parts[4] === 'infographics') {
          $link_type = 'infographics';
        }
      }
    }

    if (!empty($link_type)) {
      $logger->notice($sandbox['current'] . '. Found type: ' . $link_type . ' for ' . implode(' -- ', $parts));
      $link->set('link_type', $link_type);
      $link->save();
    }
    else {
      $logger->error($sandbox['current'] . '. Unknown url: ' . implode(' -- ', $parts));
      $link->set('link_type', 'unknown');
      $link->save();
    }

    $sandbox['last_id'] = $link->id();
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
