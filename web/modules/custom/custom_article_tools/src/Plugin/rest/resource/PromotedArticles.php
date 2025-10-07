<?php

namespace Drupal\custom_article_tools\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns promoted articles as JSON.
 */

#[RestResource(
  id: "promoted_articles",
  label: new TranslatableMarkup("Articles promoted to newsletter"),
  uri_paths: [
    "canonical" => "/api/articles/promoted",
  ]
)]

class PromotedArticles extends ResourceBase {
    
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
  }
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'), 
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('custom_article_tools'),
    );
  }

   /**
   * GET /api/articles/promoted
   */
  public function get(): ResourceResponse {
    
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'article')
      ->condition('status', 1)
      ->condition('field_promoted_to_newslet', 1)
      ->sort('created', 'DESC');

    $nids = $query->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $items = [];
    
    foreach ($nodes as $node) {
    
      $items[] = [
        'id' => (int) $node->id(),
        'title' => $node->label(),
        'created' => date('c',  $node->getCreatedTime()),
      ];
    }

    $response = new ResourceResponse($items, 200);

    $cache = new CacheableMetadata();
    
    $cache->addCacheTags(['node_list']);
    
    foreach ($nodes as $node) {
      $cache->addCacheableDependency($node);
    }

    $response->addCacheableDependency($cache);
    return $response;
  }

}