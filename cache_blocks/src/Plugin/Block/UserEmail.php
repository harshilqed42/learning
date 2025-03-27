<?php

namespace Drupal\cache_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'current user email' block.
 *
 * @Block(
 *  id = "current_user_email",
 *  admin_label = @Translation("Print Current User email"),
 * )
 */
class UserEmail extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Current User.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->currentUser->getEmail(),
    ];
  }

  /**
   * Add user to cache contexts.
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
