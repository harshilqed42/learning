<?php

namespace Drupal\cache_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Articles' block.
 *
 * @Block(
 *  id = "most_recent",
 *  admin_label = @Translation("Most recent Articles"),
 * )
 */
class MostRecentArticle extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager variable.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node_storage = $this->entityTypeManager->getStorage('node');
    $node_ids = $node_storage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(FALSE)
      ->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);

    foreach ($nodes as $key => $node) {
      $titles[] = $node->title->value;
      $cache_tags[] = 'node: ' . $key;
    }

    return [
      '#theme' => 'most_recent',
      '#titles' => $titles,
    ];
  }

  /**
   * Add node_list to cache tags.
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_list']);
  }

}
