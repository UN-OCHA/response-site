<?php

namespace Drupal\hr_paragraphs\EventSubscriber;

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
   * Mail manager.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Mail\MailManager $mail_manager
   *   The mail manager service.
   */
  public function __construct(MailManager $mail_manager) {
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

    $this->mailManager->mail('hr_paragraphs', 'user_created', 'info@humanitarianresponse.info', 'en', $params);
  }

}
