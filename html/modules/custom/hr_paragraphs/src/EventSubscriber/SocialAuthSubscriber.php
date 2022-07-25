<?php

namespace Drupal\hr_paragraphs\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\Event\UserFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts on Social Auth events.
 */
class SocialAuthSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Mail manager.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config.
   * @param \Drupal\Core\Mail\MailManager $mail_manager
   *   The mail manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MailManager $mail_manager) {
    $this->configFactory = $config_factory;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_FIELDS] = [
      'onUserPreCreate',
    ];

    $events[SocialAuthEvents::USER_CREATED] = [
      'onUserCreated',
    ];

    return $events;
  }

  /**
   * Set name to mail.
   *
   * @param \Drupal\social_auth\Event\UserFieldsEvent $event
   *   The Social Auth user fields event object.
   */
  public function onUserPreCreate(UserFieldsEvent $event) {
    $fields = $event->getUserFields();
    $fields['name'] = $fields['mail'];
    $event->setUserFields($fields);
  }

  /**
   * Send notification.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserCreated(UserEvent $event) {
    $params = [
      'mail' => $event->getUser()->getInitialEmail(),
    ];

    $to = $this->configFactory->get('hr_paragraphs.settings')->get('notification_email');
    if (!empty($to)) {
      $this->mailManager->mail('hr_paragraphs', 'user_created', $to, 'en', $params);
    }
  }

}
