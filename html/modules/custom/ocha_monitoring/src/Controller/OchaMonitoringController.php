<?php

namespace Drupal\ocha_monitoring\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\ocha_monitoring\OchaHealthcheckGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns JSON response.
 */
class OchaMonitoringController extends ControllerBase {

  /**
   * Healthcheck generator service.
   *
   * @var \Drupal\ocha_monitoring\OchaHealthcheckGenerator
   */
  protected $generator;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The core renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs controller.
   */
  public function __construct(OchaHealthcheckGenerator $generator, LoggerInterface $logger, RequestStack $request_stack, RendererInterface $renderer) {
    $this->generator = $generator;
    $this->logger = $logger;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('ocha_monitoring.generator'),
      $container->get('logger.channel.ocha_monitoring'),
      $container->get('request_stack'),
      $container->get('renderer'),
    );
  }

  /**
   * Builds the response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function buildJson() {
    // Render response in context to avoid metadata leaks. Some monitoring
    // plugins are rendered on sensor run and cached at that time as well. That
    // metadata is hard to access, so we need rendering in context detour.
    $context = new RenderContext();
    $response = $this->renderer->executeInRenderContext($context, function () {
      if ($this->access($this->currentUser())->isForbidden()) {
        $data = '{"error": "Access denied!"}';
        $json_response = new CacheableJsonResponse(
          $data,
          403,
          ['Cache-Control' => 'no-cache'],
          TRUE
        );
      }
      else {
        $data = $this->generator->getData();
        $json_response = new CacheableJsonResponse(
          $data,
          200,
          ['Cache-Control' => 'no-cache'],
          FALSE
        );
      }
      $cacheable_metadata = $json_response->getCacheableMetadata();
      $this->buildCache($cacheable_metadata);
      $json_response->addCacheableDependency($cacheable_metadata);
      return $json_response;
    });

    // If there is metadata left on the context, apply it on the response.
    if (!$context->isEmpty()) {
      if ($metadata = $context->pop()) {
        $this->buildCache($metadata);
        $response->addCacheableDependency($metadata);
      }
    }

    return $response;
  }

  /**
   * Access result callback.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Determines the access to controller.
   */
  public function access(AccountInterface $account) {
    $header_secret = $this->requestStack->getCurrentRequest()->headers->get('ocha-monitoring-key') ?? NULL;
    $config_secret = $this->config('ocha_monitoring')->get('key');
    if (($header_secret && $header_secret === $config_secret)
      || $account->hasPermission('monitoring reports')) {
      $access_result = AccessResult::allowed();
    }
    else {
      $access_result = AccessResult::forbidden();
      $this->logger->warning('Unauthorized access to monitoring results denied.');
    }
    $access_result
      ->setCacheMaxAge(0)
      ->addCacheContexts([
        'headers:ocha-monitoring-key',
        'user.roles',
      ])
      ->addCacheTags(['monitoring_sensor_result']);
    return $access_result;
  }

  /**
   * Make sure healthcheck response is not cached.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheable_metadata
   *   Cacheable metadata.
   */
  protected function buildCache(CacheableMetadata $cacheable_metadata) {
    $cacheable_metadata->setCacheContexts([
      'headers:ocha-monitoring-key',
      'user.roles',
    ]);
    $cacheable_metadata->setCacheTags(['monitoring_sensor_result']);
    $cacheable_metadata->setCacheMaxAge(0);
  }

}
