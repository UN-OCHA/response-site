<?php

namespace Drupal\hr_paragraphs\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileRepositoryInterface;
use Drupal\hr_paragraphs\Controller\ReliefwebController;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Drush commandfile.
 *
 * @property \Consolidation\Log\Logger $logger
 */
class HrParagraphsCommands extends DrushCommands {

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
   * Import operations from csv.
   *
   * @command hr_paragraphs:import-operations
   * @validate-module-enabled hr_paragraphs
   * @option skip-existing
   *   Skip existing operations.
   * @option ids
   *   List of Ids to import
   * @usage hr_paragraphs:import-operations --skip-existing --ids=1,2,3
   *   Import operations.
   */
  public function importOperations($options = [
    'skip-existing' => FALSE,
    'ids' => '',
  ]) {
    $filename = 'operations.tsv';
    $handle = $this->loadTsvFile($filename);

    // Headers.
    $header_lowercase = [
      'id',
      'name',
      'published',
      'active',
      'changed',
      'url',
      'iso3',
    ];

    $row_counter = 0;
    while ($row = fgetcsv($handle, 0, "\t")) {
      $data = [];
      for ($i = 0; $i < count($row); $i++) {
        $data[$header_lowercase[$i]] = trim($row[$i]);
      }

      // Skip missing operations.
      if (!isset($data['id'])) {
        continue;
      }

      // Limit Ids if needed.
      if (!empty($options['ids'])) {
        if (!in_array($data['id'], explode(',', $options['ids']))) {
          continue;
        }
      }

      // Only active operations.
      if ($data['active'] != 'active') {
        continue;
      }

      // Only published operations.
      if ($data['published'] != 1) {
        continue;
      }

      $row_counter++;
      $this->logger->info("{$row_counter}. Processing {$data['name']} ({$data['id']})");

      // Delete group if it exists.
      /** @var \Drupal\group\Entity\Group $group */
      if ($group = $this->entityTypeManager->getStorage('group')->load($data['id'])) {
        $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) exists.");
        if ($options['skip-existing']) {
          $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) already exists, skipping.");
          continue;
        }
        $group->delete();
      }

      // Create the operation.
      /** @var \Drupal\group\Entity\Group $group */
      $group = $this->entityTypeManager->getStorage('group')->create([
        'id' => $data['id'],
        'type' => 'operation',
        'label' => $data['name'],
        'langcode' => 'en',
      ]);

      $rw_country_id = $this->getCountryIdFromIso3($data['iso3']);
      if (!empty($rw_country_id)) {
        // Add ReliefWeb tabs and rivers.
        $group->set('field_reliefweb_assessments', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29_%28F5%29');
        $group->set('field_maps_infographics_link', 'https://reliefweb.int/updates?view=maps&advanced-search=%28PC' . $rw_country_id . '%29');
        $group->set('field_reliefweb_documents', 'https://reliefweb.int/updates?advanced-search=%28PC' . $rw_country_id . '%29&view=reports');

        // Add HDX tab.
        $group->set('field_hdx_dataset_link', 'https://data.humdata.org/group/' . strtolower($data['iso3']));
      }

      // Save it.
      $group->setPublished()->save();

      // Remove anonymous user as member.
      $group->removeMember(User::getAnonymousUser());

      // Fetch and add panes.
      $this->addPanesToEntity($group);
    }

    fclose($handle);
  }

  /**
   * Import clusters from csv.
   *
   * @command hr_paragraphs:import-clusters
   * @validate-module-enabled hr_paragraphs
   * @option skip-existing
   *   Skip existing clusters.
   * @option ids
   *   List of Ids to import
   * @option ops-ids
   *   List of operation Ids to import
   * @usage hr_paragraphs:import-clusters --skip-existing --ids=1,2,3 --ops-ids=7,8,9
   *   Import clusters.
   */
  public function importClusters($options = [
    'skip-existing' => FALSE,
    'ids' => '',
    'ops-ids' => '',
  ]) {
    $filename = 'clusters.tsv';
    $handle = $this->loadTsvFile($filename);

    // Headers.
    $header_lowercase = [
      'id',
      'name',
      'published',
      'changed',
      'url',
      'operation',
      'operation published',
      'operation active',
      'operation url',
      'operation id',
    ];

    $row_counter = 0;
    while ($row = fgetcsv($handle, 0, "\t")) {
      $data = [];
      for ($i = 0; $i < count($row); $i++) {
        $data[$header_lowercase[$i]] = trim($row[$i]);
      }

      // Skip missing operations.
      if (!isset($data['operation id'])) {
        continue;
      }

      // Limit Ids if needed.
      if (!empty($options['ids'])) {
        if (!in_array($data['id'], explode(',', $options['ids']))) {
          continue;
        }
      }

      // Limit Operation Ids if needed.
      if (!empty($options['ops-ids'])) {
        if (!in_array($data['operation id'], explode(',', $options['ops-ids']))) {
          continue;
        }
      }

      // Skip inactive operations.
      if ($data['operation active'] != 'active') {
        continue;
      }

      // Skip unpublished clusters.
      if ($data['published'] != 1) {
        continue;
      }

      // Skip unpublished operations.
      if ($data['operation published'] != 1) {
        continue;
      }

      $row_counter++;
      $this->logger->info("{$row_counter}. Processing {$data['name']} ({$data['id']})");

      // Load operation.
      /** @var \Drupal\group\Entity\Group $operation */
      $operation = $this->entityTypeManager->getStorage('group')->load($data['operation id']);
      if (!$operation) {
        $this->logger->info("{$row_counter}. Operation {$data['operation id']} not found.");
        continue;
      }

      // Delete group if it exists.
      if ($group = $this->entityTypeManager->getStorage('group')->load($data['id'])) {
        $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) exists.");
        if ($options['skip-existing']) {
          $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) already exists, skipping.");
          continue;
        }
        $group->delete();
      }

      // Create cluster.
      /** @var \Drupal\group\Entity\Group $group */
      $group = $this->entityTypeManager->getStorage('group')->create([
        'id' => $data['id'],
        'type' => 'cluster',
        'label' => $data['name'],
        'langcode' => 'en',
      ]);

      // Add ReliefWeb tabs if operation has links.
      if (!$operation->field_reliefweb_assessments->isEmpty()) {
        $group->set('field_reliefweb_assessments', $operation->field_reliefweb_assessments->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));
        $group->set('field_maps_infographics_link', $operation->field_maps_infographics_link->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));
        $group->set('field_reliefweb_documents', $operation->field_reliefweb_documents->first()->getUrl()->getUri() . '&search=' . htmlentities($data['name']));

        $group->set('field_enabled_tabs', [
          ['value' => 'documents'],
          ['value' => 'maps'],
          ['value' => 'assessments'],
        ]);
      }

      // Use sidebar from operation.
      $group->set('field_sidebar_from_operation', TRUE);
      $group->setPublished()->save();

      // Remove anonymous user as member.
      $group->removeMember(User::getAnonymousUser());

      // Fetch and add panes.
      $this->addPanesToEntity($group);

      // Add cluster to operation.
      $operation->addContent($group, 'subgroup:' . $group->bundle());
    }

    fclose($handle);
  }

  /**
   * Import pages from csv.
   *
   * @command hr_paragraphs:import-pages
   * @validate-module-enabled hr_paragraphs
   * @option skip-existing
   *   Skip existing pages.
   * @option ids
   *   List of Ids to import
   * @option group-ids
   *   List of Group Ids to import
   * @usage hr_paragraphs:import-pages --skip-existing --ids=1,2,3 --group-ids=7,8,9
   *   Import pages.
   */
  public function importPages($options = [
    'skip-existing' => FALSE,
    'ids' => '',
    'group-ids' => '',
  ]) {
    $filename = 'pages.tsv';
    $handle = $this->loadTsvFile($filename);

    // Headers.
    $header_lowercase = [
      'id',
      'name',
      'published',
      'type',
      'changed',
      'author',
      'email',
      'url',
      'operation',
      'operation url',
      'operation id',
      'operation type',
    ];

    $row_counter = 0;
    while ($row = fgetcsv($handle, 0, "\t")) {
      $data = [];
      for ($i = 0; $i < count($row); $i++) {
        $data[$header_lowercase[$i]] = trim($row[$i]);
      }

      // Skip missing operations.
      if (!isset($data['operation id'])) {
        continue;
      }

      // Limit Ids if needed.
      if (!empty($options['ids'])) {
        if (!in_array($data['id'], explode(',', $options['ids']))) {
          continue;
        }
      }

      // Limit Group Ids if needed.
      if (!empty($options['group-ids'])) {
        if (!in_array($data['operation id'], explode(',', $options['group-ids']))) {
          // Check parent Id as well.
          if (!in_array($this->getGroupParentId($data['operation id']), explode(',', $options['group-ids']))) {
            continue;
          }
        }
      }

      // Only import hr_page nodes.
      if ($data['type'] != 'hr_page') {
        continue;
      }

      // Skip unpublished pages.
      if ($data['published'] != 1) {
        continue;
      }

      $row_counter++;
      $this->logger->info("{$row_counter}. Processing {$data['name']} ({$data['id']})");

      // Load operation.
      /** @var \Drupal\group\Entity\Group $operation */
      $operation = $this->entityTypeManager->getStorage('group')->load($data['operation id']);
      if (!$operation) {
        $this->logger->info("{$row_counter}. Operation/Cluster {$data['operation id']} not found.");
        continue;
      }

      // Delete node if it exists.
      /** @var \Drupal\node\Entity\Node */
      if ($node = $this->entityTypeManager->getStorage('node')->load($data['id'])) {
        $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) exists.");
        if ($options['skip-existing']) {
          $this->logger->info("{$row_counter}. {$data['name']} ({$data['id']}) already exists, skipping.");
          continue;
        }
      }
      else {
        // Create node.
        /** @var \Drupal\node\Entity\Node */
        $node = $this->entityTypeManager->getStorage('node')->create([
          'nid' => $data['id'],
          'type' => 'page',
          'title' => $data['name'],
          'langcode' => 'en',
        ]);

        $node->setPublished()->save();

        // Fetch and add panes.
        $this->addPanesToEntity($node);
      }

      // Add page to operation/cluster.
      if (!$operation->getContentByEntityId('group_node:' . $node->bundle(), $node->id())) {
        $operation->addContent($node, 'group_node:' . $node->bundle());
      }
    }

    fclose($handle);
  }

  /**
   * Import members from csv.
   *
   * @command hr_paragraphs:import-members
   * @validate-module-enabled hr_paragraphs
   * @option skip-existing
   *   Skip existing members.
   * @option ids
   *   List of Ids to import
   * @option group-ids
   *   List of Group Ids to import
   * @usage hr_paragraphs:import-members --skip-existing --ids=1,2,3 --group-ids=7,8,9
   *   Import members.
   */
  public function importMembers($options = [
    'skip-existing' => FALSE,
    'ids' => '',
    'group-ids' => '',
  ]) {
    $filename = 'membership.tsv';
    $handle = $this->loadTsvFile($filename);

    // Headers.
    $header_lowercase = [
      'group_id',
      'operation',
      'url',
      'uid',
      'name',
      'mail',
      'active',
      'user url',
      'rid',
      'role_name',
    ];

    $row_counter = 0;
    while ($row = fgetcsv($handle, 0, "\t")) {
      $data = [];
      for ($i = 0; $i < count($row); $i++) {
        $data[$header_lowercase[$i]] = trim($row[$i]);
      }

      // Skip missing operations.
      if (!isset($data['group_id'])) {
        continue;
      }

      // Make sure mandatory fields are set.
      if (!isset($data['uid']) || !isset($data['name']) || !isset($data['mail'])) {
        $this->logger->info('Some mandatory fields are missing');
        continue;
      }
      elseif (empty($data['uid']) || empty($data['name']) || empty($data['mail'])) {
        $this->logger->info('Some mandatory fields are missing');
        continue;
      }

      // Limit Ids if needed.
      if (!empty($options['ids'])) {
        if (!in_array($data['uid'], explode(',', $options['ids']))) {
          continue;
        }
      }

      // Limit Group Ids if needed.
      if (!empty($options['group-ids'])) {
        if (!in_array($data['group_id'], explode(',', $options['group-ids']))) {
          // Check parent Id as well.
          if (!in_array($this->getGroupParentId($data['group_id']), explode(',', $options['group-ids']))) {
            continue;
          }
        }
      }

      // Skip all but managers.
      if ($data['role_name'] != 'manager') {
        continue;
      }

      $row_counter++;
      $this->logger->info("{$row_counter}. Processing {$data['name']} ({$data['uid']})");

      // Load operation.
      /** @var \Drupal\group\Entity\Group $operation */
      $operation = $this->entityTypeManager->getStorage('group')->load($data['group_id']);
      if (!$operation) {
        $this->logger->info("{$row_counter}. Operation/Cluster {$data['group_id']} not found.");
        continue;
      }

      // Load or create user.
      /** @var \Drupal\user\Entity\User $user */
      if ($user = $this->entityTypeManager->getStorage('user')->load($data['uid'])) {
        $this->logger->info("{$row_counter}. {$data['name']} ({$data['uid']}) exists.");
        if ($options['skip-existing']) {
          $this->logger->info("{$row_counter}. {$data['name']} ({$data['uid']}) already exists, skipping.");
          continue;
        }
      }
      else {
        // Create user.
        /** @var \Drupal\user\Entity\User $user */
        $user = $this->entityTypeManager->getStorage('user')->create([
          'uid' => $data['uid'],
          'name' => $data['name'],
          'mail' => $data['mail'],
        ]);

        $user->activate()->save();
      }

      // Add user to operation/cluster.
      $operation->addMember($user);

      if ($data['role_name'] == 'manager') {
        /** @var \Drupal\group\GroupMembership $member */
        $member = $operation->getMember($user);
        $membership = $member->getGroupContent();

        $role_found = FALSE;
        /** @var \Drupal\group\Entity\GroupRole $role */
        foreach ($membership->group_roles->referencedEntities() as $role) {
          if ($role->id() == $operation->bundle() . '-manager') {
            $role_found = TRUE;
            break;
          }
        }
        if (!$role_found) {
          $membership->group_roles[] = $operation->bundle() . '-manager';
          $membership->save();
        }

        // Add as member to the operation.
        if ($operation->hasField('subgroup_tree') && !$operation->subgroup_tree->isEmpty()) {
          /** @var \Drupal\group\Entity\Group $parent */
          $parent = $this->entityTypeManager->getStorage('group')->load($operation->subgroup_tree->value);
          $parent->addMember($user);
        }
      }
    }

    fclose($handle);
  }

  /**
   * Import users from csv.
   *
   * @command hr_paragraphs:import-users
   * @validate-module-enabled hr_paragraphs
   * @option skip-existing
   *   Skip existing users.
   * @option account-active
   *   Mark user active.
   * @option account-blocked
   *   Block the user.
   * @option ids
   *   List of Ids to import
   * @option emails
   *   List of email addresses to import
   * @option group-ids
   *   List of Group Ids to import
   * @usage hr_paragraphs:import-users --account-active|--account-blocked --ids=1,2,3 --emails=user@example.com --group-ids=7,8,9
   *   Import users.
   */
  public function importUsers($options = [
    'skip-existing' => TRUE,
    'migrate-all' => FALSE,
    'account-active' => FALSE,
    'account-blocked' => FALSE,
    'ids' => '',
    'group-ids' => '',
    'emails' => '',
  ]) {

    // Always skip existing users.
    $options['skip-existing'] = TRUE;

    // Don't allow account-active / account-blocked to be used simultaneously.
    if (!empty($options['account-active']) && !empty($options['account-blocked'])) {
      $this->logger->error('You cannot use --account-active and --account-blocked at the same time.');
      return;
    }

    if (!isset($options['migrate-all']) || !$options['migrate-all']) {
      // Either ids, group-ids, or emails need to be set.
      if (empty($options['ids']) && empty($options['group-ids']) && empty($options['emails'])) {
        $this->logger->error('Either --ids or --group-ids need to be set.');
        return;
      }
    }

    $filename = 'membership.tsv';
    $handle = $this->loadTsvFile($filename);

    // Headers.
    $header_lowercase = [
      'group_id',
      'operation',
      'url',
      'uid',
      'name',
      'mail',
      'active',
      'user url',
      'rid',
      'role_name',
    ];

    $row_counter = 0;
    $already_imported = [];
    while ($row = fgetcsv($handle, 0, "\t")) {
      $data = [];
      for ($i = 0; $i < count($row); $i++) {
        $data[$header_lowercase[$i]] = trim($row[$i]);
      }

      // Make sure mandatory fields are set.
      if (!isset($data['uid']) || !isset($data['name']) || !isset($data['mail'])) {
        $this->logger->info('Some mandatory fields are missing');
        continue;
      }
      elseif (empty($data['uid']) || empty($data['name']) || empty($data['mail'])) {
        $this->logger->info('Some mandatory fields are missing');
        continue;
      }

      // Skip already imported ones.
      if (in_array($data['uid'], $already_imported)) {
        continue;
      }

      if (!isset($options['migrate-all']) || !$options['migrate-all']) {
        // Limit Ids if needed.
        if (!empty($options['ids'])) {
          if (!in_array($data['uid'], explode(',', $options['ids']))) {
            continue;
          }
        }

        // Limit emails if needed.
        if (!empty($options['emails'])) {
          if (!in_array($data['mail'], explode(',', $options['emails']))) {
            continue;
          }
        }

        // Limit Group Ids if needed.
        if (!empty($options['group-ids'])) {
          if (!in_array($data['group_id'], explode(',', $options['group-ids']))) {
            // Check parent Id as well.
            if (!in_array($this->getGroupParentId($data['group_id']), explode(',', $options['group-ids']))) {
              continue;
            }
          }
        }
      }

      $row_counter++;
      $this->logger->info("{$row_counter}. Processing {$data['name']} ({$data['uid']})");

      // Load or create user.
      /** @var \Drupal\user\Entity\User $user */
      if ($user = $this->entityTypeManager->getStorage('user')->load($data['uid'])) {
        $this->logger->info("{$row_counter}. {$data['name']} ({$data['uid']}) exists.");
        if ($options['skip-existing']) {
          $this->logger->info("{$row_counter}. {$data['name']} ({$data['uid']}) already exists, skipping.");
          continue;
        }
      }
      else {
        // Create user.
        /** @var \Drupal\user\Entity\User $user */
        $user = $this->entityTypeManager->getStorage('user')->create([
          'uid' => $data['uid'],
          'name' => $data['name'],
          'mail' => $data['mail'],
        ]);

        if (!empty($options['account-active']) && $options['account-active']) {
          $user->activate();
        }
        if (!empty($options['account-blocked']) && $options['account-blocked']) {
          $user->block();
        }
        else {
          $user->activate();
        }

        $user->save();
        $already_imported[] = $user->id();
      }

    }

    fclose($handle);
  }

  /**
   * Get parent Id of a group.
   */
  protected function getGroupParentId($group_id) {
    if (empty($group_id)) {
      return FALSE;
    }

    /** @var \Drupal\group\Entity\Group $group */
    $group = $this->entityTypeManager->getStorage('group')->load($group_id);
    if (!$group) {
      return FALSE;
    }

    // Check parent.
    if ($group->hasField('subgroup_tree') && !$group->subgroup_tree->isEmpty()) {
      $parent = $this->entityTypeManager->getStorage('group')->load($group->subgroup_tree->value);
      if ($parent) {
        return $parent->id();
      }
    }

    return FALSE;
  }

  /**
   * Load tsv file.
   */
  protected function loadTsvFile($filename) {
    $csv_source_dir = $this->configFactory->get('hr_paragraphs.settings')->get('csv_source_dir');
    if (empty($csv_source_dir)) {
      $this->logger->error('"csv_source_dir" is not set.');
      exit;
    }

    return fopen(rtrim($csv_source_dir, '/') . '/' . $filename, 'r');
  }

  /**
   * Fetch panes from source site.
   */
  protected function fetchPanesFromNode($nid) {
    $sync_domain = $this->configFactory->get('hr_paragraphs.settings')->get('sync_domain', 'http://hrinfo.docksal.site');
    $sync_credentials = $this->configFactory->get('hr_paragraphs.settings')->get('sync_credentials', '');
    if (!empty($sync_credentials)) {
      $sync_domain = str_replace('https://', 'https://' . $sync_credentials . '@', $sync_domain);
    }
    $url = $sync_domain . '/node/' . $nid . '/panelist';

    $options = [
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

  /**
   * Clean out title tags.
   */
  protected function fixTitle($title) {
    $allowed_tags = [
      'a',
      'div',
    ];

    // Strip all other tags.
    $title = strip_tags($title, $allowed_tags);

    // Remove above tags including the content between tags.
    $regex = '/<[^>]*>[^<]*<[^>]*>/';
    $title = preg_replace($regex, '', $title);

    return $title;
  }

  /**
   * Fix inline images and links.
   */
  protected function fixInlineImagesAndUrls($html) {
    if (empty($html)) {
      return $html;
    }

    $doc = new \DOMDocument();
    $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);

    $tags = $doc->getElementsByTagName('img');
    foreach ($tags as $tag) {
      $src = $tag->getAttribute('src');

      // Fix filename if needed.
      $file_name = basename($src);
      if (strpos($file_name, '?') !== FALSE) {
        $file_name = substr($file_name, 0, strpos($file_name, '?'));
      }

      // Set destination.
      $destination = 'public://images/' . date('Y-m-d');
      $this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY);
      $destination .= '/' . $file_name;

      $sync_domain = $this->configFactory->get('hr_paragraphs.settings')->get('sync_domain', 'http://hrinfo.docksal.site');
      $sync_credentials = $this->configFactory->get('hr_paragraphs.settings')->get('sync_credentials', '');
      if (!empty($sync_credentials)) {
        $sync_domain = str_replace('https://', 'https://' . $sync_credentials . '@', $sync_domain);
      }
      $src = str_replace($this->configFactory->get('hr_paragraphs.settings')->get('sync_domain', 'http://hrinfo.docksal.site'), $sync_domain, $src);

      // Get and save file.
      $image = file_get_contents($src);
      $this->logger->notice(print_r([
        $src,
        $destination,
        mb_strlen($image),
      ], TRUE));

      /** @var \Drupal\file\Entity\File $tempFile */
      $temporaryFile = 'temporary://' . $file_name;
      $tempFile = $this->fileRepository->writeData($image, $temporaryFile, FileSystemInterface::EXISTS_REPLACE);

      // Resize image.
      /** @var \Drupal\image\Entity\ImageStyle $style */
      $style = $this->entityTypeManager->getStorage('image_style')->load('migrate_max_size');
      $style->createDerivative($temporaryFile, $destination);
      $file = File::create([
        'uid' => 1,
        'filename' => $file_name,
        'uri' => $destination,
        'status' => 1,
      ]);
      $file->save();

      // Update src.
      $tag->setAttribute('src', $file->createFileUrl());

      // Delete temp file.
      $tempFile->delete();
    }

    $tags = $doc->getElementsByTagName('a');
    foreach ($tags as $tag) {
      $href = $tag->getAttribute('href');

      $href = str_replace('https://www.humanitarianresponse.info/en/operations/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/fr/operations/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/ru/operations/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/es/operations/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/operations/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/en/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/fr/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/ru/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/es/', '/', $href);
      $href = str_replace('https://www.humanitarianresponse.info/', '/', $href);

      $tag->setAttribute('href', $href);
    }

    return $doc->saveHTML();
  }

  /**
   * Add panes from source to new entity.
   */
  protected function addPanesToEntity(&$entity) {
    $panes = $this->fetchPanesFromNode($entity->id());
    if (!$panes) {
      return;
    }

    if (!$panes['panes']) {
      return;
    }

    // Reset all panes.
    $entity->field_paragraphs = [];

    $changed = FALSE;
    foreach ($panes['panes'] as $pane) {
      switch ($pane['type']) {
        case 'hr_layout_rss_feeds':
          $paragraph = Paragraph::create([
            'type' => 'rss_feed',
          ]);
          if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
            $paragraph->set('field_title', $this->fixTitle($pane['title']));
          }
          $paragraph->set('field_rss_link', [
            'uri' => $pane['rss'],
          ]);

          $paragraph->isNew();
          $paragraph->save();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;
          break;

        case 'fieldable_panels_pane':
        case 'custom':
        case 'node_body':
          if (empty($pane['title']) && empty($pane['body'])) {
            break;
          }

          // Skip tokenized content.
          if (strpos($pane['body'], '[[{') !== FALSE) {
            break;
          }

          $paragraph = Paragraph::create([
            'type' => 'text_block',
          ]);

          if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
            $paragraph->set('field_title', $this->fixTitle($pane['title']));
          }

          $paragraph->set('field_text', $this->fixInlineImagesAndUrls($pane['body']));
          $paragraph->field_text->format = 'basic_html';

          $paragraph->isNew();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;
          break;

        case 'hr_documents':
        case 'hr_infographics':
        case 'hr_infographics_key_infographics':
        case 'hr_documents_key_documents':
          foreach ($pane['target_ids'] as $target) {
            $reliefweb_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info/en/' . $target);
            if (empty($reliefweb_url)) {
              continue;
            }

            $paragraph = Paragraph::create([
              'type' => 'reliefweb_document',
            ]);
            if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
              $paragraph->set('field_title', $this->fixTitle($pane['title']));
            }

            $paragraph->set('field_reliefweb_url', $reliefweb_url);

            $paragraph->isNew();

            $entity->field_paragraphs[] = $paragraph;
            $changed = TRUE;
          }
          break;

        case 'hr_reliefweb_key_documents':
          $filters = [];
          foreach ($pane['filters'] ?? [] as $key => $value) {
            if ($key == 'country') {
              $key = 'primary_country';
            }
            if (!empty(trim($value))) {
              $filters[] = $key . ':"' . $value . '"';
            }
          }

          $reliefweb_url = 'https://reliefweb.int/updates?search=' . implode(' AND ', $filters);

          $paragraph = Paragraph::create([
            'type' => 'reliefweb_river',
          ]);
          if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
            $paragraph->set('field_title', $this->fixTitle($pane['title']));
          }

          $paragraph->set('field_reliefweb_url', $reliefweb_url);
          $paragraph->set('field_max_number_of_items', $pane['limit'] ?? 5);

          $paragraph->isNew();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;

          break;

        case 'hr_layout_reliefweb':
          $reliefweb_url = 'https://reliefweb.int/updates?search=' . $pane['country'];

          $parts = explode('/', $pane['api_path']);
          $rw_type = array_pop($parts);

          switch ($rw_type) {
            case 'reports':
              $reliefweb_url = 'https://reliefweb.int/updates?view=reports&search=' . $pane['country'];
              break;

            case 'jobs':
              // @todo unsupported.
              $reliefweb_url = 'https://reliefweb.int/jobs?search=' . $pane['country'];
              break;

            case 'training':
              $reliefweb_url = 'https://reliefweb.int/training?search=' . $pane['country'];
              break;

            case 'book':
              // Fallback to default.
              break;

            case 'blog':
              $reliefweb_url = 'https://reliefweb.int/updates?view=headlines?search=' . $pane['country'];
              break;

            case 'countries':
              $reliefweb_url = 'https://reliefweb.int/countries?search=' . $pane['country'];
              break;

            case 'disasters':
              $reliefweb_url = 'https://reliefweb.int/disasters?search=' . $pane['country'];
              break;

            case 'sources':
              // Fallback to default.
              break;

            case 'references':
              // Fallback to default.
              break;

          }

          $paragraph = Paragraph::create([
            'type' => 'reliefweb_river',
          ]);
          if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
            $paragraph->set('field_title', $this->fixTitle($pane['title']));
          }

          $paragraph->set('field_reliefweb_url', $reliefweb_url);
          $paragraph->set('field_max_number_of_items', $pane['no_of_items'] ?? 5);

          $paragraph->isNew();

          $entity->field_paragraphs[] = $paragraph;
          $changed = TRUE;
          break;

        case 'hr_layout_standard':
          $keys = [
            'gid',
            'hno',
            'monr',
            'srp',
            'opr',
            'orp',
          ];
          foreach ($keys as $key) {
            if (!$pane[$key]) {
              continue;
            }

            if (!isset($pane[$key]['entity_bundle']) || $pane[$key]['entity_bundle'] == 'hr_operation') {
              continue;
            }

            $reliefweb_url = $this->fetchReliefWebDocumentUrl('https://www.humanitarianresponse.info/en/' . $pane[$key]['target_id']);
            if (empty($reliefweb_url)) {
              continue;
            }

            $paragraph = Paragraph::create([
              'type' => 'reliefweb_document',
            ]);
            if (isset($pane['title']) && !empty($this->fixTitle($pane['title']))) {
              $paragraph->set('field_title', $this->fixTitle($pane['title']));
            }

            $paragraph->set('field_reliefweb_url', $reliefweb_url);

            $paragraph->isNew();

            $entity->field_paragraphs[] = $paragraph;
            $changed = TRUE;
          }
          break;

        case 'fts_visualization':
          // Skip it.
          break;

        default:
          $this->logger->notice("unsupported type: {$pane['type']}");
      }
    }

    if ($changed) {
      $entity->save();
    }
  }

  /**
   * Map Iso3 code to ReliefWeb country Id.
   */
  protected function getCountryIdFromIso3($iso3) {
    $iso_to_rw = [
      'AFG' => 13,
      'ALA' => 14,
      'ALB' => 15,
      'DZA' => 16,
      'ASM' => 17,
      'AND' => 18,
      'AGO' => 19,
      'AIA' => 20,
      'ATG' => 21,
      'ARG' => 22,
      'ARM' => 23,
      'ABW' => 24,
      'AUS' => 25,
      'AUT' => 26,
      'AZE' => 27,
      'AZO' => 28,
      'BHS' => 29,
      'BHR' => 30,
      'BGD' => 31,
      'BRB' => 32,
      'BLR' => 33,
      'BEL' => 34,
      'BLZ' => 35,
      'BEN' => 36,
      'BMU' => 37,
      'BTN' => 38,
      'BOL' => 39,
      'BIH' => 40,
      'BWA' => 41,
      'BRA' => 42,
      'VGB' => 43,
      'BRN' => 44,
      'BGR' => 45,
      'BFA' => 46,
      'BDI' => 47,
      'KHM' => 48,
      'CMR' => 49,
      'CAN' => 50,
      'CAI' => 51,
      'CPV' => 52,
      'CYM' => 53,
      'CAF' => 54,
      'TCD' => 55,
      'CHI' => 56,
      'CHL' => 57,
      'CHN' => 58,
      'HKG' => 59,
      'MAC' => 60,
      'TWN' => 61,
      'CXR' => 62,
      'CCK' => 63,
      'COL' => 64,
      'COM' => 65,
      'COG' => 66,
      'COK' => 67,
      'CRI' => 68,
      'CIV' => 69,
      'HRV' => 70,
      'CUB' => 71,
      'CYP' => 72,
      'CZE' => 73,
      'PRK' => 74,
      'COD' => 75,
      'DNK' => 76,
      'DJI' => 77,
      'DMA' => 78,
      'DOM' => 79,
      'EAI' => 80,
      'ECU' => 81,
      'EGY' => 82,
      'SLV' => 83,
      'GNQ' => 84,
      'ERI' => 85,
      'EST' => 86,
      'ETH' => 87,
      'FLK' => 88,
      'FRO' => 89,
      'FJI' => 90,
      'FIN' => 91,
      'FRA' => 92,
      'GUF' => 93,
      'PYF' => 94,
      'GAB' => 96,
      'GLI' => 97,
      'GMB' => 98,
      'GEO' => 100,
      'DEU' => 101,
      'GHA' => 102,
      'GIB' => 103,
      'GRC' => 104,
      'GRL' => 105,
      'GRD' => 106,
      'GLP' => 107,
      'GUM' => 108,
      'GTM' => 109,
      'GIN' => 110,
      'GNB' => 111,
      'GUY' => 112,
      'HTI' => 113,
      'HMD' => 114,
      'VAT' => 115,
      'HND' => 116,
      'HUN' => 117,
      'ISL' => 118,
      'IND' => 119,
      'IDN' => 120,
      'IRN' => 121,
      'IRQ' => 122,
      'IRL' => 123,
      'ILM' => 124,
      'ISR' => 125,
      'ITA' => 126,
      'JAM' => 127,
      'JPN' => 128,
      'JOR' => 129,
      'KAZ' => 130,
      'KEN' => 131,
      'KIR' => 132,
      'KWT' => 133,
      'KGZ' => 134,
      'LAO' => 135,
      'LVA' => 136,
      'LBN' => 137,
      'LSO' => 138,
      'LBR' => 139,
      'LBY' => 140,
      'LIE' => 141,
      'LTU' => 142,
      'LUX' => 143,
      'MDG' => 144,
      'MDR' => 145,
      'MWI' => 146,
      'MYS' => 147,
      'MDV' => 148,
      'MLI' => 149,
      'MLT' => 150,
      'MHL' => 151,
      'MTQ' => 152,
      'MRT' => 153,
      'MUS' => 154,
      'MYT' => 155,
      'MEX' => 156,
      'FSM' => 157,
      'MDA' => 158,
      'MCO' => 159,
      'MNG' => 160,
      'MNE' => 161,
      'MSR' => 162,
      'MAR' => 163,
      'MOZ' => 164,
      'MMR' => 165,
      'NAM' => 166,
      'NRU' => 167,
      'NPL' => 168,
      'NLD' => 169,
      'ANT' => 170,
      'NCL' => 171,
      'NZL' => 172,
      'NIC' => 173,
      'NER' => 174,
      'NGA' => 175,
      'NIU' => 176,
      'NFK' => 177,
      'MNP' => 178,
      'NOR' => 179,
      'PSE' => 180,
      'OMN' => 181,
      'PAK' => 182,
      'PLW' => 183,
      'PAN' => 184,
      'PNG' => 185,
      'PRY' => 186,
      'PER' => 187,
      'PHL' => 188,
      'PCN' => 189,
      'POL' => 190,
      'PRT' => 191,
      'PRI' => 192,
      'QAT' => 193,
      'KOR' => 194,
      'REU' => 195,
      'ROU' => 196,
      'RUS' => 197,
      'RWA' => 198,
      'SHN' => 199,
      'KNA' => 200,
      'LCA' => 201,
      'SPM' => 202,
      'VCT' => 203,
      'WSM' => 204,
      'SMR' => 205,
      'STP' => 206,
      'SAU' => 207,
      'SEN' => 208,
      'SRB' => 209,
      'SYC' => 210,
      'SLE' => 211,
      'SGP' => 212,
      'SVK' => 213,
      'SVN' => 214,
      'SLB' => 215,
      'SOM' => 216,
      'ZAF' => 217,
      'ESP' => 218,
      'LKA' => 219,
      'SDN' => 220,
      'SUR' => 221,
      'SJM' => 222,
      'SWZ' => 223,
      'SWE' => 224,
      'CHE' => 225,
      'SYR' => 226,
      'TJK' => 227,
      'THA' => 228,
      'MKD' => 229,
      'TLS' => 230,
      'TGO' => 231,
      'TKL' => 232,
      'TON' => 233,
      'TTO' => 234,
      'TUN' => 235,
      'TUR' => 236,
      'TKM' => 237,
      'TCA' => 238,
      'TUV' => 239,
      'UGA' => 240,
      'UKR' => 241,
      'ARE' => 242,
      'GBR' => 243,
      'TZA' => 244,
      'USA' => 245,
      'VIR' => 246,
      'URY' => 247,
      'UZB' => 248,
      'VUT' => 249,
      'VEN' => 250,
      'VNM' => 251,
      'WLF' => 252,
      'ESH' => 253,
      'WLD' => 254,
      'YEM' => 255,
      'ZMB' => 256,
      'ZWE' => 257,
      'SSD' => 8657,
      'BLM' => 14890,
      'MAF' => 14891,
      'SXM' => 14892,
      'CUW' => 14893,
      'BES' => 14894,
    ];

    if (isset($iso_to_rw[strtoupper($iso3)])) {
      return $iso_to_rw[strtoupper($iso3)];
    }

    return '';
  }

}
