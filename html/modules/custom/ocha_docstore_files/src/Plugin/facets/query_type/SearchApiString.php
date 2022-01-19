<?php

namespace Drupal\ocha_docstore_files\Plugin\facets\query_type;

use Drupal\facets\QueryType\QueryTypePluginBase;
use Drupal\facets\Result\Result;
use Drupal\search_api\Query\QueryInterface;

/**
 * Provides support for string facets within the Search API scope.
 *
 * This is a copy of the `search_api_string` query type with special
 * handling of values in the form `uuid:label` used on Assessment Registry
 * to avoid extra lookup to get the label.
 *
 * This expects that 2 fields are indexed:
 * 1. a field with only the `uuid` (ex: field_countries).
 * 2. a field with the uuid and label concatenated (ex: field_countries__facet).
 *
 * The `xxx__facet` field is used for to build the facets and the uuid field is
 * used for the actual filtering.
 *
 * @FacetsQueryType(
 *   id = "ar_search_api_string",
 *   label = @Translation("String"),
 * )
 */
class SearchApiString extends QueryTypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $query = $this->query;

    // Only alter the query when there's an actual query object to alter.
    if (!empty($query)) {
      $operator = $this->facet->getQueryOperator();
      $field_identifier = $this->facet->getFieldIdentifier();
      $exclude = $this->facet->getExclude();

      if ($query->getProcessingLevel() === QueryInterface::PROCESSING_FULL) {
        // Set the options for the actual query.
        $options = &$query->getOptions();
        $options['search_api_facets'][$field_identifier] = $this->getFacetOptions();
      }

      // Add the filter to the query if there are active values.
      $active_items = $this->facet->getActiveItems();

      if (count($active_items)) {
        $filter = $query->createConditionGroup($operator, ['facet:' . $field_identifier]);

        // Use the UUID field which is supposed to have the same name without
        // the `__facet` suffix.
        $field_identifier = str_replace('__facet', '', $field_identifier);
        foreach ($active_items as $value) {
          $filter->addCondition($field_identifier, $value, $exclude ? '<>' : '=');
        }
        $query->addConditionGroup($filter);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query_operator = $this->facet->getQueryOperator();

    if (!empty($this->results)) {
      $facet_results = [];
      foreach ($this->results as $result) {
        if ($result['count'] || $query_operator == 'or') {
          $result_filter = $result['filter'];
          if ($result_filter[0] === '"') {
            $result_filter = substr($result_filter, 1);
          }
          if ($result_filter[strlen($result_filter) - 1] === '"') {
            $result_filter = substr($result_filter, 0, -1);
          }
          $count = $result['count'];

          // If the value is in the form `uuid:label`, split it and use the
          // uuid as raw value and the label as display value.
          if (preg_match('/^([a-f0-9-]{36}):(.+)/', $result_filter, $matches) === 1) {
            $result = new Result($this->facet, $matches[1], $matches[2], $count);
          }
          else {
            // This will generate facet with label uuid.
            $result = new Result($this->facet, $result_filter, $result_filter, $count);
          }
          $facet_results[] = $result;
        }
      }
      $this->facet->setResults($facet_results);
    }

    return $this->facet;
  }

}
