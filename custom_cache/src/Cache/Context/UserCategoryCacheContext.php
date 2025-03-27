<?php

namespace Drupal\custom_cache\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a cache context based on the user's category.
 */
class UserCategoryCacheContext implements CacheContextInterface {

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
   * Constructs a UserCategoryCacheContext object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The Account Proxy service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("User's category");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    // Load the currunt user.
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    // Check if user has a data for category field.
    if ($user && !$user->isAnonymous() && !$user->get('field_category')->isEmpty()) {
      return $user->get('field_category')->target_id;
    }

    return 'none'; // Default cache context for anonymous or users without category
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
