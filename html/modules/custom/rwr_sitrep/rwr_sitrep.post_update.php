<?php

/**
 * @file
 * Post update hooks hooks.
 */

/**
 * Add default taxonomy term for cluster_or_working_group_type.
 *
 * @todo Consider adding terms for 'Cluster', 'Working group' and 'Sector'.
 */
function rwr_sitrep_post_update_add_terms() {
  // Add default terms.
  $term = rwr_sitrep_create_term_if_needed('cluster_or_working_group_type', 'Situation Report');
  $term->set('field_pdf_enabled', TRUE);
  $term->save();

}
