<?php

namespace Drupal\custom_article_tools\EventSubscriber;

use Drupal\views_event_dispatcher\Event\Views\ViewsQueryAlterEvent;
use Drupal\views_event_dispatcher\ViewsHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * View alter event subscriber class.  
 */
class ViewAlterEventSubscriber implements EventSubscriberInterface {
  
  public const VIEW_ID = "articles_list";
  public const VIEW_DISPLAY_ID = "page_1";

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ViewsHookEvents::VIEWS_QUERY_ALTER => 'excludeFututeArticles'
    ];
  }

  /**
   * Alters view query to exclude form listing articles created with future date.
   * 
   * @param \Drupal\views_event_dispatcher\Event\Views\ViewsQueryAlterEvent $event
   *    Subscribed event object.
   */
  public function excludeFututeArticles(ViewsQueryAlterEvent $event) {
    $view = $event->getView();
    if ($view->id() !== self::VIEW_ID || $view->current_display !== self::VIEW_DISPLAY_ID) {
      return;
    }
    
    $query = $event->getQuery();
    
    $now = \Drupal::time()->getRequestTime();
    $node_table = $query->ensureTable('node_field_data');
    
    $query->addWhere(0, "$node_table.created", $now, '<=');
  }
}
