<?php

namespace Drupal\custom_article_tools\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber handles Node Article manipulations.
 */
class NodeArticleEventSubscriber implements EventSubscriberInterface {

  public const CONTENT_TYPE = "article";

  /** @var \Drupal\Core\Logger\LoggerChannelInterface */
  private LoggerChannelInterface $logger;  

  /**
   * Class constructor.
   * 
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('custom_article_tools');
  }


  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => ['entityInsert']
    ];
  }

  /**
   * Function to react on entity insert event.
   */
  public function entityInsert(EntityInsertEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface && $entity->bundle() === self::CONTENT_TYPE) {
      $this->logger->notice(
        'New article added: . ID: @id, title: "@title"',
        [
          '@id' => $entity->id(),
          '@title' => $entity->label(),
        ]
      );
    }
  }
}
