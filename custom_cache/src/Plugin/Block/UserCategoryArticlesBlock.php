<?php

namespace Drupal\custom_cache\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a to display articles based on the user’s category.
 *
 * @Block(
 *   id = "user_category_block",
 *   admin_label = @Translation("User Category Articles")
 * )
 */
class UserCategoryArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Constructs a UserCategoryArticlesBlock object.
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
    $articles = [];
    $storage = $this->entityTypeManager->getStorage('node');

    // Get the current user's preferred category.
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());

    dump($user);
    exit;

    // Check if user has a data for category field.
    if ($user && !$user->isAnonymous() && !$user->get('field_category')->isEmpty()) {
      $category_tid = $user->get('field_category')->target_id;

      // Retrieve articles from the preferred category.
      $nids = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'article')
        ->condition('field_tags', $category_tid)
        ->accessCheck(TRUE)
        ->range(0, 5)
        ->sort('created', 'DESC')
        ->execute();

      $nodes = $storage->loadMultiple($nids);

      foreach ($nodes as $node) {
        $articles[] = [
          '#markup' => $node->toLink()->toString(),
        ];
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $articles,
      '#cache' => [
        'contexts' => ['user_category'],
        'tags' => ['node_list'],
      ],
    ];
  }

}
