<?php

namespace Drupal\custom_cache\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to displays the latest 3 article titles.
 *
 * @Block(
 *   id = "latest_articles_block",
 *   admin_label = @Translation("Latest Articles Block")
 * )
 */
class LatestArticles extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Account Proxy service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a LatestArticlesBlock object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The Account Proxy service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Retrieve the last 3 articles.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    dump($nids);
    exit;
    $articles = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    // Build the list of article titles.
    $items = [];
    foreach ($articles as $article) {
      $items[] = $article->toLink()->toRenderable();
    }

    // Build the tags array.
    $tags = [];
    foreach ($nids as $nid) {
      $tags[] = "node:$nid";
    }

    // Get the current user's email.
    $email_text = $this->currentUser->isAuthenticated() ? $this->currentUser->getEmail() : 'no email, please login';

    return [
      '#theme' => 'item_list',
      '#items' => array_merge($items, ["email: " . $email_text]),
      '#cache' => [
        'tags' => $tags,
        'contexts' => ['user'],
      ],
    ];
  }

}
