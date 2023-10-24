<?php

namespace Drupal\hr_paragraphs\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\State;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\hr_paragraphs\Controller\ReliefwebController;
use Drupal\paragraphs\Entity\Paragraph;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Drush commandfile.
 *
 * @property \Consolidation\Log\Logger $logger
 */
class HrParagraphsBacklinksCommands extends DrushCommands {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The HTTP client to fetch the files with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * State.
   *
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * Reliefweb controller.
   *
   * @var \Drupal\hr_paragraphs\Controller\ReliefwebController
   */
  protected $reliefwebController;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, ClientInterface $http_client, State $state, ReliefwebController $reliefweb_controller) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
    $this->state = $state;
    $this->reliefwebController = $reliefweb_controller;
  }

  /**
   * Fix home links.
   *
   * @command hr_paragraphs:backlinks-home
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-home
   *   Fix home links.
   */
  public function fixHomeLinks($options = [
    'reset' => FALSE,
  ]) {

    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'home')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      $data = $parent->get($link->getParentEntityFieldName());
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row->value = str_replace($link->getUrl(), '/', $row->value);
          }
          break;

      }

      $parent->set($link->getParentEntityFieldName(), $data);
    }

  }

  /**
   * Fix operation links.
   *
   * @command hr_paragraphs:backlinks-operation
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-operation
   *   Fix operation links.
   */
  public function fixOperationLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'operation')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      $result = $this->fetchNidFromAlias(implode('/', $parts));
      if ($result) {
        $id = $result['nid'] ?? '';
        if ($id) {
          $group = $this->entityTypeManager->getStorage('group')->load($id);
          if ($group) {
            $new_url = $group->toUrl()->toString();
          }
        }
      }

      if (!$new_url) {
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            if (!is_array($row)) {
              $row->value = str_replace($link->getUrl(), $new_url, $row->value);
              $updated = TRUE;
            }
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
      }

      if ($updated) {
        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix cluster links.
   *
   * @command hr_paragraphs:backlinks-cluster
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-cluster
   *   Fix cluster links.
   */
  public function fixClusterLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'cluster')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      $result = $this->fetchNidFromAlias(implode('/', $parts));
      if ($result) {
        $id = $result['nid'] ?? '';
        if ($id) {
          $group = $this->entityTypeManager->getStorage('group')->load($id);
          if ($group) {
            $new_url = $group->toUrl()->toString();
          }
        }
      }

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->notice('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix document links.
   *
   * @command hr_paragraphs:backlinks-document
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-document
   *   Fix document links.
   */
  public function fixDocumentLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', ['document', 'infographic'], 'IN')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      // Try to find the document on RW using URL.
      $new_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info' . $url);

      // Check on HRInfo.
      if (empty($new_url)) {
        $result = $this->fetchNidFromAlias(implode('/', $parts));
        if ($result) {
          $id = $result['nid'] ?? '';
          if ($id) {
            $node = $this->entityTypeManager->getStorage('node')->load($id);
            if ($node) {
              $new_url = $node->toUrl()->toString();
            }
          }
        }
      }

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->info('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix documents links.
   *
   * @command hr_paragraphs:backlinks-documents
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-documents
   *   Fix documents links.
   */
  public function fixDocumentsLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', ['documents', 'infographics'], 'IN')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination, link to tab on operation or cluster.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);

      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      // Skip meeting minutes.
      if (strpos($url, 'meeting-minutes') !== FALSE) {
        $this->logger('backlinks')->notice('Skipping meeting minutes');
        continue;
      }

      // Try to find the group containing the link.
      $group = $parent;
      while ($group instanceof Paragraph) {
        $group = $group->getParentEntity();
      }

      if (!$group instanceof Group) {
        $this->logger('backlinks')->notice('Not part of a group');
        continue;
      }

      if ($link->link_type->value == 'documents') {
        $new_url = Url::fromRoute('hr_paragraphs.operation.reports', [
          'group' => $group->id(),
        ])->toString();
      }
      else {
        $new_url = Url::fromRoute('hr_paragraphs.operation.maps', [
          'group' => $group->id(),
        ])->toString();
      }

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->info('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix file links.
   *
   * @command hr_paragraphs:backlinks-file
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-file
   *   Fix file links.
   */
  public function fixFileLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'file')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      // Lookup node on HRInfo.
      $filename = array_pop($parts);
      $node_url = $this->fetchAliasFromFilename($filename);
      if (!isset($node_url['alias'])) {
        $this->logger('backlinks')->notice('No new url found on HRInfo for ' . $url);
        continue;
      }

      // Try to find the document on RW using URL.
      $new_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info/' . $node_url['alias']);

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->info('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix node links.
   *
   * @command hr_paragraphs:backlinks-node
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-node
   *   Fix node links.
   */
  public function fixNodeLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'node')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      // Lookup node on HRInfo.
      $nid = array_pop($parts);
      $node_url = $this->fetchAliasFromNid($nid);
      if (!isset($node_url['alias'])) {
        $this->logger('backlinks')->notice('No new url found on HRInfo for ' . $url);
        continue;
      }

      // Try to find the document on RW using URL.
      $new_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info/' . $node_url['alias']);

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->info('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fix private file links.
   *
   * @command hr_paragraphs:backlinks-privatefile
   * @validate-module-enabled hr_paragraphs
   * @option reset
   *   Start from the beginning.
   * @usage hr_paragraphs:backlinks-privatefile
   *   Fix private file links.
   */
  public function fixPrivateFileLinks($options = [
    'reset' => FALSE,
  ]) {
    $last_id = $this->state->get('hr_paragraphs_backlinks_' . __FUNCTION__, 0);
    if (!empty($options['reset'])) {
      $last_id = 0;
    }

    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('status', TRUE)
      ->condition('link_type', 'private file')
      ->condition('lid', $last_id, '>')
      ->range(0, 250)
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    $this->logger('backlinks')->notice('Found ' . count($ids) . ' to check');

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
      $this->state->set('hr_paragraphs_backlinks_' . __FUNCTION__, $link->id());

      // Load parent entity.
      $parent = $link->getParentEntity();

      // Get data.
      /** @var \Drupal\Core\Field\FieldItemListInterface $data */
      $data = $parent->get($link->getParentEntityFieldName())->getValue();
      $field_definition = $parent->getFieldDefinition($link->getParentEntityFieldName());

      // Get new destination.
      $new_url = '';
      $url = parse_url($link->getUrl(), PHP_URL_PATH);
      $parts = explode('/', $url);

      // Remove language.
      if ($parts[1] === 'en' || $parts[1] === 'es' || $parts[1] === 'fr' || $parts[1] === 'ru') {
        unset($parts[1]);
        $parts = array_values($parts);
      }

      $this->logger('backlinks')->notice('Processing ' . $url . ' (' . $link->id() . ') on ' . $parent->label());

      // Lookup node on HRInfo.
      if (strpos($url, '/file/') !== FALSE) {
        $fid = array_pop($parts);
        if ($fid == 'download') {
          $fid = array_pop($parts);
        }
        $node_url = $this->fetchAliasFromFileId($fid);
      }
      elseif (strpos($url, '/system/') !== FALSE) {
        $filename = array_pop($parts);
        $node_url = $this->fetchAliasFromFilename($filename);
      }

      if (!isset($node_url['alias'])) {
        $this->logger('backlinks')->notice('No new url found on HRInfo for ' . $url);
        continue;
      }

      // Try to find the document on RW using URL.
      $new_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info/' . $node_url['alias']);

      if (!$new_url) {
        $this->logger('backlinks')->notice('No new url found for ' . $url);
        continue;
      }

      // Track changes.
      $updated = FALSE;
      switch ($field_definition->getItemDefinition()->getDataType()) {
        case 'field_item:text_long':
          foreach ($data as &$row) {
            $row['value'] = str_replace($link->getUrl(), $new_url, $row['value']);
            $updated = TRUE;
          }
          break;

        case 'field_item:link':
          foreach ($data as &$row) {
            if ($row['uri'] == $link->getUrl()) {
              $row['uri'] = $new_url;
              $updated = TRUE;
            }
          }
          break;

        default:
          $this->logger('backlinks')->error('Skipping ' . $link->getUrl() . ' (' . $field_definition->getItemDefinition()->getDataType() . ')');
          break;

      }

      if ($updated) {
        $this->logger('backlinks')->info('Changing ' . $url . ' (' . $link->id() . ') to ' . $new_url);

        // Disable link checking.
        $link->setDisableLinkCheck();
        $link->save();

        // Save entity.
        $parent->set($link->getParentEntityFieldName(), $data);
        $parent->save();
      }
    }
  }

  /**
   * Fetch node id from alias.
   */
  protected function fetchNidFromAlias($alias) {
    $query = [
      'alias' => $alias,
      'ts' => time(),
    ];

    return $this->doReverseLookup($query);
  }

  /**
   * Fetch alias from file id.
   */
  protected function fetchAliasFromFileId($fid) {
    $query = [
      'fid' => $fid,
      'ts' => time(),
    ];

    return $this->doReverseLookup($query);
  }

  /**
   * Fetch alias from filename.
   */
  protected function fetchAliasFromFilename($filename) {
    $query = [
      'filename' => $filename,
      'ts' => time(),
    ];

    return $this->doReverseLookup($query);
  }

  /**
   * Fetch alias from nid.
   */
  protected function fetchAliasFromNid($nid) {
    $query = [
      'nid' => $nid,
      'ts' => time(),
    ];

    return $this->doReverseLookup($query);
  }

  /**
   * Reverse lookup on HRInfo.
   */
  protected function doReverseLookup($query = []) {
    $sync_domain = $this->configFactory->get('hr_paragraphs.settings')->get('sync_domain', 'http://hrinfo.docksal.site');
    $sync_credentials = $this->configFactory->get('hr_paragraphs.settings')->get('sync_credentials', '');
    if (!empty($sync_credentials)) {
      $sync_domain = str_replace('https://', 'https://' . $sync_credentials . '@', $sync_domain);
    }
    $url = $sync_domain . '/reverse-lookup';

    $options = [
      'query' => $query,
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ],
    ];

    try {
      $response = $this->httpClient->request('GET', $url, $options);

      if ($response->getStatusCode() == 200) {
        return json_decode($response->getBody() . '', TRUE);
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return FALSE;
  }

  /**
   * Fetch ReliefWeb URL from source URL.
   */
  protected function fetchReliefWebDocumentUrl($hrinfo_url) {
    $parameters = $this->reliefwebController->buildReliefwebParameters(0, 1, []);

    // Remove facets.
    unset($parameters['facets']);
    $parameters['filter']['conditions'][] = [
      'field' => 'origin',
      'value' => $hrinfo_url,
    ];

    $results = $this->reliefwebController->executeReliefwebQuery($parameters);
    if (empty($results['data'])) {
      return FALSE;
    }

    $docs = $this->reliefwebController->buildReliefwebObjects($results);
    $doc = reset($docs);

    return $doc['url'] ?? '';
  }

}
