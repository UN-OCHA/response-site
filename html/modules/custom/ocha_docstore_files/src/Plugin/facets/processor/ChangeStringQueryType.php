<?php

namespace Drupal\ocha_docstore_files\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\PreQueryProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;

/**
 * Processor to force the use of the `ar_search_api_string` query type.
 *
 * @FacetsProcessor(
 *   id = "ar_string",
 *   label = @Translation("Use AR string query type"),
 *   description = @Translation("Use the AR search api string query type"),
 *   stages = {
 *     "pre_query" = 5,
 *     "build" = 5
 *   }
 * )
 */
class ChangeStringQueryType extends ProcessorPluginBase implements BuildProcessorInterface, PreQueryProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'ar_string';
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(FacetInterface $facet) {
  }

}
