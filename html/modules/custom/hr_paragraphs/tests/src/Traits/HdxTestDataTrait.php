<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\Traits;

/**
 * RSS test data.
 */
trait HdxTestDataTrait {

  /**
   * Test data 1.
   */
  private function getTestHdx1() {
    return <<<HDX
    {
      "help": "https://data.humdata.org/api/3/action/help_show?name=package_search",
      "success": true,
      "result": {
        "count": 1,
        "sort": "if(gt(last_modified,review_date),last_modified,review_date) desc",
        "search_facets": {
          "queries": [],
          "res_format": {
            "items": [{
              "count": 1,
              "display_name": "XLSX",
              "name": "XLSX"
            }, {
              "count": 1,
              "display_name": "Geoservice",
              "name": "Geoservice"
            }],
            "title": "res_format"
          },
          "vocab_Topics": {
            "items": [{
              "count": 1,
              "display_name": "geodata",
              "name": "geodata"
            }, {
              "count": 1,
              "display_name": "gazetteer",
              "name": "gazetteer"
            }, {
              "count": 1,
              "display_name": "common operational dataset - cod",
              "name": "common operational dataset - cod"
            }, {
              "count": 1,
              "display_name": "administrative divisions",
              "name": "administrative divisions"
            }],
            "title": "vocab_Topics"
          },
          "groups": {
            "items": [{
              "count": 1,
              "display_name": "Afghanistan",
              "name": "afg"
            }],
            "title": "groups"
          },
          "pivot": {},
          "organization": {
            "items": [{
              "count": 1,
              "display_name": "OCHA Field Information Services Section (FISS)",
              "name": "ocha-fiss"
            }],
            "title": "organization"
          },
          "license_id": {
            "items": [{
              "count": 1,
              "display_name": "Creative Commons Attribution for Intergovernmental Organisations",
              "name": "cc-by-igo"
            }],
            "title": "license_id"
          }
        },
        "facets": {
          "vocab_Topics": {
            "common operational dataset - cod": 1,
            "geodata": 1,
            "gazetteer": 1,
            "administrative divisions": 1
          },
          "organization": {
            "ocha-fiss": 1
          },
          "res_format": {
            "XLSX": 1,
            "Geoservice": 1
          },
          "groups": {
            "afg": 1
          },
          "license_id": {
            "cc-by-igo": 1
          }
        },
        "expanded": {},
        "results": [{
            "data_update_frequency": "365",
            "license_title": "Creative Commons Attribution for Intergovernmental Organisations",
            "maintainer": "84e567b6-1d09-4f7e-96f5-b69c09028cbc",
            "relationships_as_object": [],
            "private": false,
            "dataset_date": "[2019-10-22T00:00:00 TO *]",
            "num_tags": 4,
            "solr_additions": "{\"countries\": [\"Afghanistan\"]}",
            "review_date": "2021-11-24T07:26:34.266266",
            "id": "4c303d7b-8eae-4a5a-a3aa-b2331fa39d74",
            "metadata_created": "2019-10-22T08:11:12.520687",
            "archived": false,
            "caveats": "In-country humanitarian responders in Afghanistan can collect a copy of latest available datasets from OCHA Afghanistan as a member of the Information Management Working Group (IMWG).         These datasets are available         for purchase from the[National Statistic and Information Authority](https: //www.nsia.gov.af/home) (NSIA)  in Afghanistan.",
              "metadata_modified": "2022-02-03T15:50:27.452866",
              "title": "Afghanistan - Subnational Administrative Boundaries",
              "subnational": "1",
              "state": "active",
              "has_geodata": false,
              "methodology": "Other",
              "version": null,
              "is_requestdata_type": false,
              "license_id": "cc-by-igo",
              "type": "dataset",
              "has_showcases": false,
              "due_date": "2022-12-06T10:13:15",
              "dataset_preview": "first_resource",
              "num_resources": 4,
              "dataset_source": "Afghanistan Geodesy and Cartography Head Office (AGCHO)",
              "tags": [{
                "vocabulary_id": "b891512e-9516-4bf5-962a-7a289772a2a1",
                "state": "active",
                "display_name": "administrative divisions",
                "id": "491190cd-ad96-4060-8878-cb593517184f",
                "name": "administrative divisions"
              }, {
                "vocabulary_id": "b891512e-9516-4bf5-962a-7a289772a2a1",
                "state": "active",
                "display_name": "common operational dataset - cod",
                "id": "140a9fed-106d-4c6f-a178-046177e10eac",
                "name": "common operational dataset - cod"
              }, {
                "vocabulary_id": "b891512e-9516-4bf5-962a-7a289772a2a1",
                "state": "active",
                "display_name": "gazetteer",
                "id": "853d6f46-3b86-4f54-897f-65ed42a30675",
                "name": "gazetteer"
              }, {
                "vocabulary_id": "b891512e-9516-4bf5-962a-7a289772a2a1",
                "state": "active",
                "display_name": "geodata",
                "id": "2d27d72f-af37-4b38-b05e-dddc6929bd13",
                "name": "geodata"
              }],
              "last_modified": "2021-12-06T10:13:15.196398",
              "groups": [{
                "display_name": "Afghanistan",
                "description": "",
                "image_display_url": "",
                "title": "Afghanistan",
                "id": "afg",
                "name": "afg"
              }],
              "creator_user_id": "391f0864-b6e4-425f-9d46-df87aa456c2b",
              "has_quickcharts": false,
              "methodology_other": "ITOS processing",
              "relationships_as_subject": [],
              "overdue_date": "2023-02-04T10:13:15",
              "total_res_downloads": 432,
              "qa_completed": false,
              "name": "cod-ab-afg",
              "isopen": false,
              "url": null,
              "notes": "Afghanistan administrative level 0-2 and UNAMA region gazetteer and P-code geoservices.          This gazetteer is compatible with the[Afghanistan - Subnational Population Statistics](https: //data.humdata.org/dataset/cod-ps-afg) gazetteer.            Only the gazetteer the the P - code geoservices can be made generally available.Humanitarian responders who want the administrative boundary files should make a request by clicking the 'Contact the contributor'            button(below) and including:            a) the following declaration: \"I agree not to share the data with any third party or publish it online without prior permission from AGCHO.  As per the agreement, the datasets are for humanitarian use only.\"          b) name        c) organization or cluster      d) email address    The user will then receive a link to the boundary files,    which may only be used according to the above restriction.    ",
        "owner_org": "b3a25ac4-ac05-4991-923c-d25f47bef1ec",
        "batch": "a65514e5-3ebc-4ef6-a27e-1c2afce533c5",
        "updated_by_script": "HDXINTERNAL:HDXPythonLibrary/5.5.3-CODsStandardisation (2022-02-03T15:50:27.295413)",
        "license_url": "http://creativecommons.org/licenses/by/3.0/igo/legalcode",
        "resources": [{
          "pii": "false",
          "cache_last_updated": null,
          "package_id": "4c303d7b-8eae-4a5a-a3aa-b2331fa39d74",
          "datastore_active": false,
          "id": "0238eb07-4f98-4f71-9a03-905c4414f476",
          "size": 142757,
          "metadata_modified": "2021-11-30T09:50:35.464216",
          "download_url": "https://data.humdata.org/dataset/4c303d7b-8eae-4a5a-a3aa-b2331fa39d74/resource/0238eb07-4f98-4f71-9a03-905c4414f476/download/afg_adminboundaries_tabulardata.xlsx",
          "state": "active",
          "hash": "",
          "description": "Afghanistan administrative level 0-2 and UNAMA region gazetteer",
          "format": "XLSX",
          "pii_report_id": "/resources/0238eb07-4f98-4f71-9a03-905c4414f476/pii.2021-11-30T09-50-12.main.json",
          "pii_timestamp": "2021-11-30T09:50:35.101000",
          "hdx_rel_url": "/dataset/4c303d7b-8eae-4a5a-a3aa-b2331fa39d74/resource/0238eb07-4f98-4f71-9a03-905c4414f476/download/afg_adminboundaries_tabulardata.xlsx",
          "mimetype_inner": null,
          "url_type": "upload",
          "originalHash": "-988266717",
          "name": "AFG_AdminBoundaries_TabularData.xlsx",
          "mimetype": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          "cache_url": null,
          "microdata": false,
          "created": "2021-11-30T09:50:08.812727",
          "url": "https://data.humdata.org/dataset/4c303d7b-8eae-4a5a-a3aa-b2331fa39d74/resource/0238eb07-4f98-4f71-9a03-905c4414f476/download/afg_adminboundaries_tabulardata.xlsx",
          "pii_report_flag": "FINDINGS",
          "pii_predict_score": 0.25313460278317956,
          "last_modified": "2021-11-30T09:50:08.359278",
          "position": 0,
          "resource_type": "file.upload"
        }, {
          "pii": false,
          "cache_last_updated": null,
          "package_id": "4c303d7b-8eae-4a5a-a3aa-b2331fa39d74",
          "datastore_active": false,
          "id": "c999bc6d-d4d7-4608-a660-c1076371ad1c",
          "size": 0,
          "metadata_modified": "2021-11-30T09:50:13.036956",
          "download_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_DA/MapServer",
          "state": "active",
          "hash": "",
          "description": "This map service contains OCHA Common Operational Datasets for Afghanistan, in Dari: Administrative Boundaries and Regions. The service is available as ESRI Map, ESRI Feature, WMS, and KML Services. See the OCHA COD/FOD terms of use for access and use constraints.",
          "format": "Geoservice",
          "hdx_rel_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_DA/MapServer",
          "mimetype_inner": null,
          "url_type": "api",
          "position": 1,
          "name": "COD_External/AFG_DA (MapServer)",
          "mimetype": null,
          "cache_url": null,
          "microdata": false,
          "created": "2021-11-30T09:50:10.980740",
          "url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_DA/MapServer",
          "last_modified": "2021-11-30T09:50:10.958749",
          "originalHash": -988266717,
          "resource_type": "api"
        }, {
          "pii": false,
          "cache_last_updated": null,
          "package_id": "4c303d7b-8eae-4a5a-a3aa-b2331fa39d74",
          "datastore_active": false,
          "id": "27f6b881-2c27-4865-8cf6-1d604d0a6e76",
          "size": 0,
          "metadata_modified": "2021-11-30T09:50:14.205562",
          "download_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_EN/MapServer",
          "state": "active",
          "hash": "",
          "description": "This map service contains OCHA Common Operational Datasets for Afghanistan, in English: Administrative Boundaries and Regions. The service is available as ESRI Map, ESRI Feature, WMS, and KML Services. See the OCHA COD/FOD terms of use for access and use constraints.",
          "format": "Geoservice",
          "hdx_rel_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_EN/MapServer",
          "mimetype_inner": null,
          "url_type": "api",
          "position": 2,
          "name": "COD_External/AFG_EN (MapServer)",
          "mimetype": null,
          "cache_url": null,
          "microdata": false,
          "created": "2021-11-30T09:50:13.051163",
          "url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_EN/MapServer",
          "last_modified": "2021-11-30T09:50:13.037185",
          "originalHash": -988266717,
          "resource_type": "api"
        }, {
          "pii": false,
          "cache_last_updated": null,
          "package_id": "4c303d7b-8eae-4a5a-a3aa-b2331fa39d74",
          "datastore_active": false,
          "id": "6281bf9f-da18-4914-a583-b29c076bbde6",
          "size": 0,
          "metadata_modified": "2022-02-03T15:50:27.470160",
          "download_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_pcode/MapServer",
          "state": "active",
          "hash": "",
          "description": "This service is intended as a labelling layer for PCODES from OCHA's Common Operational Datasets for Afghanistan. As a map service it is intended to be used in conjunction with the basemap located at http://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_EN/MapServer. The service is available as ESRI Map, WMS, WFS and KML Services.",
          "format": "Geoservice",
          "hdx_rel_url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_pcode/MapServer",
          "mimetype_inner": null,
          "url_type": "api",
          "position": 3,
          "name": "COD_External/AFG_pcode (MapServer)",
          "mimetype": null,
          "cache_url": null,
          "microdata": false,
          "created": "2021-12-06T10:13:15.217581",
          "url": "https://gistmaps.itos.uga.edu/arcgis/rest/services/COD_External/AFG_pcode/MapServer",
          "last_modified": "2021-12-06T10:13:15.196398",
          "originalHash": -988266717,
          "resource_type": "api"
        }],
        "pageviews_last_14_days": 38,
        "organization": {
          "description": "UN Office for the Coordination of Humanitarian Affairs - Field Information Services Section based in Geneva, Switzerland.      Generic e - mail(ocha - fis - data @un.org)      ",
          "created": "2014-08-15T06:32:04.343540",
          "title": "OCHA Field Information Services Section (FISS)",
          "name": "ocha-fiss",
          "is_organization": true,
          "state": "active",
          "image_url": "",
          "type": "organization",
          "id": "b3a25ac4-ac05-4991-923c-d25f47bef1ec",
          "approval_status": "approved"
        },
        "package_creator": "murtaza"
      }],
    "facet_pivot": {},
    "facet_queries": {}
    }
    }
HDX;
  }

  /**
   * Test data 2.
   */
  private function getTestHdx2() {
    return <<<HDX
    This will not parse
HDX;
  }
}
