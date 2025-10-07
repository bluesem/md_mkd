<?php

namespace Drupal\custom_article_tools\EventSubscriber;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Node alter form event subscriber for altering article node edit form.
 */
class NodeFormAlterEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => 'alterNodeForm'

    ];
  }

  /**
   * Adds custom_promoted_to_newsletter checkbox
   * to article node edit form.
   * 
   * @param $event
   *   Subscribed event object.
   */
  public function alterNodeForm(FormAlterEvent $event) {
    $form = &$event->getForm();
    $form_state = $event->getFormState();

    $form_object = $form_state->getFormObject();

    if (
      str_starts_with($event->getFormId(), 'node_article') &&
      str_contains($event->getFormId(), '_form') &&
      $form_object instanceof EntityFormInterface
    ) {
      $node = $form_object->getEntity();

      $form['custom_promoted_to_newsletter'] = [
        '#type' => "checkbox",
        '#title' => "Promoted to newsletter",
        '#default_value' => $node->get('field_promoted_to_newslet')->value ?? 0,
      ];

        $form['actions']['submit']['#submit'][] = [self::class, 'savePromotedToNewsletterChoice'];
    }

  }

  /**
   * Submit callback. Saves promoted to newsletter value to
   * field_promoted_to_newslet field.
   * 
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function savePromotedToNewsletterChoice(array $form, FormStateInterface $form_state) {
 
    $form_object = $form_state->getFormObject();

    if (!$form_object instanceof EntityFormInterface) {
      return;
    }

    $node = $form_object->getEntity();
    $node->set('field_promoted_to_newslet', $form_state->getValue('custom_promoted_to_newsletter'));
    $node->save();
  }
}
