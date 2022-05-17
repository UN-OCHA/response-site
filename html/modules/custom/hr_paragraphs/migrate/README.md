# Migrations

## Export

```bash
drush sqlq "select nid, title, status, field_operation_status_value, from_unixtime(changed), concat('https://www.humanitarianresponse.info/node/', nid), field_iso3_value from node inner join field_data_field_operation_status operation_status on operation_status.entity_id = node.nid inner join field_data_field_country country on country.entity_id = node.nid inner join field_data_field_iso3 iso3 on iso3.entity_id = country.field_country_target_id where type = 'hr_operation';" > operations.tsv

drush sqlq "select node.nid, node.title, node.status, from_unixtime(node.changed), concat('https://www.humanitarianresponse.info/node/', node.nid), operation.title, operation.status, field_operation_status_value, concat('https://www.humanitarianresponse.info/node/', operation.nid), operation.nid from node inner join og_membership on og_membership.etid = node.nid inner join node operation on og_membership.gid = operation.nid inner join field_data_field_operation_status operation_status on operation_status.entity_id = operation.nid where node.type = 'hr_bundle';" > clusters.tsv

drush sqlq "select gid, node.title, concat('https://www.humanitarianresponse.info/node/', node.nid), etid, users.name, users.mail, users.status, concat('https://www.humanitarianresponse.info/user/', users.uid) from og_membership inner join node on node.nid = og_membership.gid inner join users on users.uid = og_membership.etid where entity_type = 'user' and users.uid > 1;" > membership.tsv

drush sqlq "select node.nid, REPLACE(node.title, '\t', '' ), node.status, node.type, from_unixtime(node.changed), users.name, users.mail, concat('https://www.humanitarianresponse.info/node/', node.nid), operation.title, operation.status, field_operation_status_value, concat('https://www.humanitarianresponse.info/node/', operation.nid), operation.nid, operation.type from node inner join og_membership on og_membership.etid = node.nid inner join node operation on og_membership.gid = operation.nid inner join users on users.uid = node.uid inner join field_data_field_operation_status operation_status on operation_status.entity_id = operation.nid where node.type not in ('hr_bundle', 'hr_operation');" > pages.tsv
```

## Import

Scripts have to be run in order

1. operations
2. clusters
3. pages

### Remove existing content

Node Ids to preserve:
- 1
- 37
- 38
- 40
- 42
- 43
- 44

```bash
drush entity:delete node --exclude=1,37,38,40,42,43,44
drush entity:delete group --bundle=cluster
drush entity:delete group
```

PS: `drush entity:delete` does not delete unpublished entities, see https://github.com/drush-ops/drush/issues/5058

### Operations

Only **active** and **published** operations are migrated.

```bash
drush scr html/modules/custom/hr_paragraphs/migrate/operations.php
```

- Sidebar is populated with list of clusters and list of pages
- Reliefweb tabs are populated based on country code
- HDX tab is populated based on country code
- Panes are added to the home page

### Clusters

Only **published** clusters belonging to **active** and **published** operations are migrated.

```bash
drush scr html/modules/custom/hr_paragraphs/migrate/clusters.php
```

- Sidebar of the operation is being used
- Reliefweb tabs are populated based on that from the operation and using the cluster name as search parameter
- HDX tab is **not** populated
- Panes are added to the home page

### Pages

Only **published** pages of type **hr_page** belonging to **active** and **published** clusters/operations are migrated.

```bash
drush scr html/modules/custom/hr_paragraphs/migrate/pages.php
```

- Sidebar of the operation is being used
- Panes are added to the home page

### Panes

The following panes are being migrated:

- RSS feed (rss_feed):
  * `hr_layout_rss_feeds`
- Text block (text_block):
  * `fieldable_panels_pane`
  * `custom`
  * `node_body`
- ReliefWeb document (reliefweb_document):
  * `hr_documents`
  * `hr_infographics`
  * `hr_infographics_key_infographics`
  * `hr_documents_key_documents`
  * `hr_layout_standard`
- ReliefWeb River (reliefweb_river):
  * `hr_layout_reliefweb`
  * `hr_reliefweb_key_document`

The following are not being migrated:

- FTS:
  * `fts_visualization`
  *-* `existing_bean`

Todo:


For text fields:

- inline images are detected and saved locally
- internal links to `https://www.humanitarianresponse.info` are rewritten to relative urls
