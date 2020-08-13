<?php

namespace Drupal\shorten_url\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;

/**
 * Shorten URLs Controller.
 */
class ShortenURLController {

  /**
   * {@inheritdoc}
   */
  public function redirectOriginalUrl($short_code) {
    // Search by short code.
    $query = \Drupal::database()
      ->select('short_urls', 'su')
      ->condition('su.short_code', $short_code, '=')
      ->fields('su', ['id', 'long_url', 'short_code', 'created']);
    $result = $query->execute()->fetch();

    if (!empty($result)) {
      $fields = [
        'short_urls_id' => $result->id,
        'uid' => \Drupal::currentUser()->id(),
        'short_code' => $result->short_code,
        'ip_address' => \Drupal::request()->getClientIp(),
        'created' => strtotime(date('d-m-y h:i:s')),
      ];

      // Insert in database.
      $analytics_id = \Drupal::database()
        ->insert('short_urls_analytics')
        ->fields($fields)
        ->execute();

      // Redirect to original url.
      return new TrustedRedirectResponse($result->long_url);
    }
    else {

      \Drupal::messenger()->addError("Oops! No valid short code found.");

      // Get previous URL.
      $referer = \Drupal::request()->headers->get('referer');

      if (!empty($referer)) {
        // Redirect to previous url.
        return new RedirectResponse($referer);
      }
      else {
        // Redirect to home page.
        $url = Url::fromRoute('<front>');
        $response = new RedirectResponse($url->toString());
        $response->send();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function viewShortUrlData($short_code) {

    // Search by short code.
    $query = \Drupal::database()
      ->select('short_urls', 'su')
      ->condition('su.short_code', $short_code, '=')
      ->fields('su', ['id', 'long_url', 'short_code', 'created']);
    $result = $query->execute()->fetch();
    $shorten_url_data = (array) $result;

    return [
      '#theme' => 'shorten_url_theme',
      '#shorten_url_data' => $shorten_url_data,
    ];
  }

}
