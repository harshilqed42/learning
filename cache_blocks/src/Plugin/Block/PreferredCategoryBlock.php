<?php

namespace Drupal\cache_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Provides a 'Preferred Category Block' block.
 *
 * @Block(
 *   id = "preferred_category_block",
 *   admin_label = @Translation("Preferred Category Block"),
 *   category = @Translation("Custom")
 * )
 */
class PreferredCategoryBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current user.
    $current_user = \Drupal::currentUser();

    $current_user = User::load($current_user->id());

    // Get the field value for the user's preferred categories (multi-value).
    $preferred_category_values = $current_user->get('field_sports')->getValue();

    // Extract the target_ids (taxonomy term IDs) from the multi-value field.
    $preferred_category_tids = [];
    foreach ($preferred_category_values as $value) {
      $preferred_category_tids[] = $value['target_id'];
    }

    // If there are preferred categories selected by the user.
    if (!empty($preferred_category_tids)) {
      // Query nodes that belong to the preferred categories.
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'article')
        ->condition('field_sports', $preferred_category_tids, 'IN') // Use 'IN' to filter by multiple categories.
        ->sort('created', 'DESC')
        ->accessCheck(FALSE) // Bypass node access checks.
        ->range(0, 5); // Limit to 5 articles.

      $nids = $query->execute();

      // Load the node entities.
      $nodes = Node::loadMultiple($nids);

      // Return a render array with the nodes.
      $content = [];
      foreach ($nodes as $node) {
        $content[] = [
          '#theme' => 'item_list',
          '#items' => [
            $node->toLink()->toString(),
          ],
          '#title' => $this->t('Articles from your preferred categories'),
        ];
      }

      return $content;
    }

    // If no preferred category is selected or no articles are found:
    return [
      '#markup' => $this->t('No preferred categories selected or no articles found.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContext() {
    // Return custom cache context based on the user's id
    return Cache::mergeContexts(parent::getCacheContexts(), ['user:' . \Drupal::currentUser()->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Custom cache tags to invalidate the cache when certain content changes.
    return Cache::mergeTags(parent::getCacheTags(), ['node_list']);
  }

}
