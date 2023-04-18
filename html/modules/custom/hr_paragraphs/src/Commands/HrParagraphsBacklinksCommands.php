<?php

namespace Drupal\hr_paragraphs\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\file\FileRepositoryInterface;
use Drupal\hr_paragraphs\Controller\ReliefwebController;
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
   * File repository.
   *
   * @var \Drupal\Core\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * File system.
   *
   * @var \Drupal\Core\file\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Reliefweb controller.
   *
   * @var \Drupal\hr_paragraphs\Controller\ReliefwebController
   */
  protected $reliefwebController;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, ClientInterface $http_client, FileRepositoryInterface $file_repository, FileSystem $file_system, ReliefwebController $reliefweb_controller) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
    $this->fileRepository = $file_repository;
    $this->fileSystem = $file_system;
    $this->reliefwebController = $reliefweb_controller;
  }

  /**
   * Fix home links.
   *
   * @command hr_paragraphs:backlinks-home
   * @validate-module-enabled hr_paragraphs
   * @usage hr_paragraphs:backlinks-home
   *   Fix home links.
   */
  public function fixHomeLinks() {
    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('link_type', 'home')
      ->sort('lid')
      ->execute();

    if (empty($ids)) {
      $this->logger('backlinks')->notice('Nothing to do');
      return;
    }

    /** @var \Drupal\linkchecker\Entity\LinkCheckerLink[] $links */
    $links = $this->entityTypeManager->getStorage('linkcheckerlink')->loadMultiple($ids);
    foreach ($links as $link) {
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
   * @usage hr_paragraphs:backlinks-operation
   *   Fix operation links.
   */
  public function fixOperationLinks() {
    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('link_type', 'operation')
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
            $row->value = str_replace($link->getUrl(), $new_url, $row->value);
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
   * @usage hr_paragraphs:backlinks-cluster
   *   Fix cluster links.
   */
  public function fixClusterLinks() {
    $ids = $this->entityTypeManager->getStorage('linkcheckerlink')->getQuery()
      ->accessCheck(FALSE)
      ->condition('url', 'https://www.humanitarianresponse.info/%', 'LIKE')
      ->condition('link_type', 'cluster')
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
   * Fetch panes from source site.
   */
  protected function fetchNidFromAlias($alias) {
    $sync_domain = $this->configFactory->get('hr_paragraphs.settings')->get('sync_domain', 'http://hrinfo.docksal.site');
    $sync_credentials = $this->configFactory->get('hr_paragraphs.settings')->get('sync_credentials', '');
    if (!empty($sync_credentials)) {
      $sync_domain = str_replace('https://', 'https://' . $sync_credentials . '@', $sync_domain);
    }
    $url = $sync_domain . '/reverse-alias';

    $options = [
      'query' => [
        'alias' => $alias,
      ],
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

}
