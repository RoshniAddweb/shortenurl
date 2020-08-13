<?php

namespace Drupal\shorten_url\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Shorten URL' block.
 *
 * @Block(
 *   id = "shorten_url_form_block",
 *   admin_label = @Translation("Shorten URLs"),
 *   category = @Translation("Forms")
 * )
 */
class ShortenURLBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\shorten_url\Form\ShortenURLForm');
  }

}
