<?php

namespace Drupal\itm_showcase\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\BcRoute;
use Drupal\file\Entity\File;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Represents Showcase records as resources.
 *
 * @RestResource (
 *   id = "itm_showcase",
 *   label = @Translation("Showcases"),
 *   uri_paths = {
 *     "canonical" = "/api/list",
 *     "https://www.drupal.org/link-relations/create" = "/api/itm-showcase-example"
 *   }
 * )
 *
 * @DCG
 * This plugin exposes database records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. You may
 * find an example of such configuration in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can make use of REST UI module.
 * @see https://www.drupal.org/project/restui
 * For accessing Drupal entities through REST interface use
 * \Drupal\rest\Plugin\rest\resource\EntityResource plugin.
 */
class GetShowcaseResource extends ResourceBase implements DependentPluginInterface
{

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * CurrentRequest.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
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
   * @param \Drupal\Core\Database\Connection $db_connection
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type manager
   * @param \Symfony\Component\HttpFoundation\Request $currentRequest
   *   Current Request.
   */
  public function __construct(array                      $configuration,
                                                         $plugin_id,
                                                         $plugin_definition,
                              array                      $serializer_formats,
                              LoggerInterface            $logger,
                              Connection                 $db_connection,
                              EntityTypeManagerInterface $entityTypeManager,
                              Request                    $currentRequest
  )
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->dbConnection = $db_connection;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRequest = $currentRequest;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return JsonResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get()
  {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $fileStorage = $this->entityTypeManager->getStorage('file');
    $baseUrl = $this->currentRequest->getSchemeAndHttpHost();
    $featured = $this->currentRequest->query->get('featured');
    $showcaseIds = $nodeStorage
      ->getQuery()
      ->condition('type', 'showcase')
      ->condition('field_featured', $featured)
      ->pager(3)
      ->addTag('sort_by_random')
      ->execute();
    $showcases = $nodeStorage->loadMultiple($showcaseIds);
    $result = [];
    /** @var \Drupal\node\Entity\Node $showcase */
    foreach ($showcases as $showcase) {
      /** @var Drupal\file\Plugin\Field\FieldType\FileFieldItemList $file */
      $file = $showcase->get('field_featured_image')->getValue();
      $file = reset($file);
      if ($file['target_id']) {
        /** @var File $file_uri */
        $file_uri = $baseUrl . $fileStorage->load($file['target_id'])->createFileUrl();
      }
      $articleId = $showcase->get('field_article')->getValue();
      $articleId = reset($articleId);
      $article = $nodeStorage->load($articleId['target_id']);
      $result[] = [
        'featured_image' => $file_uri,
        'title' => $showcase->get('title')->value,
        'article' => [
          'title' => $article->get('title')->value,
          'url' => $baseUrl . '/node/' . $article->id(),
        ],
      ];
    }
    return new JsonResponse($result);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies()
  {
    return [];
  }

}
