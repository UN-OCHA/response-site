<?php

namespace Drupal\ocha_drd\Plugin\Action;

use Drupal\drd\Plugin\Action\BaseEntityRemote;
use Drupal\drd\Entity\BaseInterface as RemoteEntityInterface;
use Drupal\drd\Entity\DomainInterface;

/**
 * OCHA Requirements action.
 *
 * @Action(
 *  id = "ocha_drd_action_requirements",
 *  label = @Translation("OCHA Requirements"),
 *  type = "drd_domain",
 * )
 */
class OchaDrdRequirements extends BaseEntityRemote {

  /**
   * {@inheritdoc}
   */
  public function executeAction(RemoteEntityInterface $entity = NULL): bool|array|string {
    if (!($entity instanceof DomainInterface)) {
      return FALSE;
    }

    $response = parent::executeAction($entity);
    if (is_array($response)) {

      $settings = $entity->getRemoteSettings();
      $settings['ocha_drd.current_release'] = $response['ocha_drd.current_release'];
      $settings['ocha_drd.deployment_identifier'] = $response['ocha_drd.deployment_identifier'];

      /* @noinspection PhpUnhandledExceptionInspection */
      $entity
        ->cacheSettings($settings)
        ->save();
      return $response;
    }
    return FALSE;
  }

}
