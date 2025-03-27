<?php

declare(strict_types=1);

namespace Drupal\cache_blocks\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\Core\Cache\Context\UserCacheContextBase;

/**
 * @todo Add a description for the cache context.
 *
 * Cache context ID: 'test'.
 *
 * @DCG
 * Check out the core/lib/Drupal/Core/Cache/Context directory for examples of
 * cache contexts provided by Drupal core.
 */
final class TestCacheContext extends UserCacheContextBase implements CalculatedCacheContextInterface {

  /**
   * {@inheritdoc}
   */
  public static function getLabel(): string {
    return (string) t('Test');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = NULL): string {
    // @todo Calculate the cache context here.
    $context = 'some_string_value';
    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = NULL): CacheableMetadata {
    return new CacheableMetadata();
  }

}
