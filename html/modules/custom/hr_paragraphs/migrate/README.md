# Migrations

## Export

```bash
drush sqlq "select nid, title, status, field_operation_status_value, from_unixtime(changed), concat('https://www.humanitarianresponse.info/node/', nid), field_iso3_value from node inner join field_data_field_operation_status operation_status on operation_status.entity_id = node.nid inner join field_data_field_country country on country.entity_id = node.nid inner join field_data_field_iso3 iso3 on iso3.entity_id = country.field_country_target_id where type = 'hr_operation';" > operations.tsv

drush sqlq "select node.nid, node.title, node.status, from_unixtime(node.changed), concat('https://www.humanitarianresponse.info/node/', node.nid), operation.title, operation.status, field_operation_status_value, concat('https://www.humanitarianresponse.info/node/', operation.nid), operation.nid from node inner join og_membership on og_membership.etid = node.nid inner join node operation on og_membership.gid = operation.nid inner join field_data_field_operation_status operation_status on operation_status.entity_id = operation.nid where node.type = 'hr_bundle';" > clusters.tsv

drush sqlq "select gid, node.title, concat('https://www.humanitarianresponse.info/node/', node.nid), etid, users.name, users.mail, users.status, concat('https://www.humanitarianresponse.info/user/', users.uid) from og_membership inner join node on node.nid = og_membership.gid inner join users on users.uid = og_membership.etid where entity_type = 'user' and users.uid > 1;" > membership.tsv

drush sqlq "select node.nid, REPLACE(node.title, '\t', '' ), node.status, node.type, from_unixtime(node.changed), users.name, users.mail, concat('https://www.humanitarianresponse.info/node/', node.nid), operation.title, operation.status, field_operation_status_value, concat('https://www.humanitarianresponse.info/node/', operation.nid), operation.nid, operation.type from node inner join og_membership on og_membership.etid = node.nid inner join node operation on og_membership.gid = operation.nid inner join users on users.uid = node.uid inner join field_data_field_operation_status operation_status on operation_status.entity_id = operation.nid where node.type not in ('hr_bundle', 'hr_operation');" > pages.csv
```
