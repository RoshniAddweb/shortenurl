<?php

namespace Drupal\shorten_url\Plugin\rest\resource;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides shorten URLs data.
 *
 * @RestResource(
 *   id = "shorten_url_redirection_resource",
 *   label = @Translation("Shorten URLs"),
 *   uri_paths = {
 *     "canonical" = "/rest-api/shorten_url_redirection/{short_code}",
 *     "https://www.drupal.org/link-relations/create" = "/rest-api/shorten_url_redirection"
 *   }
 * )
 */
class ShortenURLsResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->currentUser = $current_user;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_rest'),
      $container->get('current_user'),
      $container->get('database')
    );
  }

  /**
   * Responds to entity GET requests.
   *
   * @return:
   *  Return data of the short code.
   */
  public function get($short_code = NULL) {
    $response = [];

    // Search by short code.
    $query = \Drupal::database()
      ->select('short_urls', 'su')
      ->condition('su.short_code', $short_code, '=')
      ->fields('su', ['id', 'long_url', 'short_code', 'created']);
    $result = $query->execute()->fetch();

    if (!empty($result)) {
      $response = (array) $result;
    }
    else {
      $response = ['error' => 'No valid short found.'];
    }

    return new JsonResponse(['status' => 200, 'result' => $response]);
  }

}
