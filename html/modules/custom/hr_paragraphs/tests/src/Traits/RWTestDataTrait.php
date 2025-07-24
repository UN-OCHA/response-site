<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\Traits;

/**
 * Reliefweb test data.
 */
trait RWTestDataTrait {

  /**
   * The API endpoint we use for our tests.
   */
  private const API_ENDPOINT = 'https:\/\/api.reliefweb.int\/v2\/reports';

  /**
   * Test data 1.
   */
  private function getTestRw1() {
    return '{
      "time": 26,
      "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=10&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=country.id&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=13&filter%5Bconditions%5D%5B0%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B1%5D%5Bfield%5D=source.type.id&filter%5Bconditions%5D%5B1%5D%5Bvalue%5D=271&filter%5Bconditions%5D%5B1%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B2%5D%5Bfield%5D=theme.id&filter%5Bconditions%5D%5B2%5D%5Bvalue%5D=4587&filter%5Bconditions%5D%5B2%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B3%5D%5Bfield%5D=format.id&filter%5Bconditions%5D%5B3%5D%5Bvalue%5D=5&filter%5Bconditions%5D%5B3%5D%5Bnegate%5D=0&facets%5B0%5D%5Bfield%5D=source.name&facets%5B0%5D%5Blimit%5D=2000&facets%5B0%5D%5Bsort%5D=value%3Aasc&facets%5B1%5D%5Bfield%5D=theme.name&facets%5B1%5D%5Blimit%5D=2000&facets%5B1%5D%5Bsort%5D=value%3Aasc&facets%5B2%5D%5Bfield%5D=format.name&facets%5B2%5D%5Blimit%5D=2000&facets%5B2%5D%5Bsort%5D=value%3Aasc&facets%5B3%5D%5Bfield%5D=disaster_type&facets%5B3%5D%5Blimit%5D=2000&facets%5B3%5D%5Bsort%5D=value%3Aasc&facets%5B4%5D%5Bfield%5D=language.name&facets%5B4%5D%5Blimit%5D=2000&facets%5B4%5D%5Bsort%5D=value%3Aasc&facets%5B5%5D%5Bfield%5D=date.original&facets%5B5%5D%5Binterval%5D=month&facets%5B6%5D%5Bfield%5D=date.changed&facets%5B6%5D%5Binterval%5D=month&facets%5B7%5D%5Bfield%5D=disaster.name&facets%5B7%5D%5Blimit%5D=2000&facets%5B7%5D%5Bsort%5D=value%3Aasc",
      "links":
      {
        "self":
        {
          "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=10&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=country.id&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=13&filter%5Bconditions%5D%5B0%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B1%5D%5Bfield%5D=source.type.id&filter%5Bconditions%5D%5B1%5D%5Bvalue%5D=271&filter%5Bconditions%5D%5B1%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B2%5D%5Bfield%5D=theme.id&filter%5Bconditions%5D%5B2%5D%5Bvalue%5D=4587&filter%5Bconditions%5D%5B2%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B3%5D%5Bfield%5D=format.id&filter%5Bconditions%5D%5B3%5D%5Bvalue%5D=5&filter%5Bconditions%5D%5B3%5D%5Bnegate%5D=0&facets%5B0%5D%5Bfield%5D=source.name&facets%5B0%5D%5Blimit%5D=2000&facets%5B0%5D%5Bsort%5D=value%3Aasc&facets%5B1%5D%5Bfield%5D=theme.name&facets%5B1%5D%5Blimit%5D=2000&facets%5B1%5D%5Bsort%5D=value%3Aasc&facets%5B2%5D%5Bfield%5D=format.name&facets%5B2%5D%5Blimit%5D=2000&facets%5B2%5D%5Bsort%5D=value%3Aasc&facets%5B3%5D%5Bfield%5D=disaster_type&facets%5B3%5D%5Blimit%5D=2000&facets%5B3%5D%5Bsort%5D=value%3Aasc&facets%5B4%5D%5Bfield%5D=language.name&facets%5B4%5D%5Blimit%5D=2000&facets%5B4%5D%5Bsort%5D=value%3Aasc&facets%5B5%5D%5Bfield%5D=date.original&facets%5B5%5D%5Binterval%5D=month&facets%5B6%5D%5Bfield%5D=date.changed&facets%5B6%5D%5Binterval%5D=month&facets%5B7%5D%5Bfield%5D=disaster.name&facets%5B7%5D%5Blimit%5D=2000&facets%5B7%5D%5Bsort%5D=value%3Aasc"
        },
        "prev":
        {
          "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=10&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=country.id&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=13&filter%5Bconditions%5D%5B0%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B1%5D%5Bfield%5D=source.type.id&filter%5Bconditions%5D%5B1%5D%5Bvalue%5D=271&filter%5Bconditions%5D%5B1%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B2%5D%5Bfield%5D=theme.id&filter%5Bconditions%5D%5B2%5D%5Bvalue%5D=4587&filter%5Bconditions%5D%5B2%5D%5Bnegate%5D=0&filter%5Bconditions%5D%5B3%5D%5Bfield%5D=format.id&filter%5Bconditions%5D%5B3%5D%5Bvalue%5D=5&filter%5Bconditions%5D%5B3%5D%5Bnegate%5D=0&facets%5B0%5D%5Bfield%5D=source.name&facets%5B0%5D%5Blimit%5D=2000&facets%5B0%5D%5Bsort%5D=value%3Aasc&facets%5B1%5D%5Bfield%5D=theme.name&facets%5B1%5D%5Blimit%5D=2000&facets%5B1%5D%5Bsort%5D=value%3Aasc&facets%5B2%5D%5Bfield%5D=format.name&facets%5B2%5D%5Blimit%5D=2000&facets%5B2%5D%5Bsort%5D=value%3Aasc&facets%5B3%5D%5Bfield%5D=disaster_type&facets%5B3%5D%5Blimit%5D=2000&facets%5B3%5D%5Bsort%5D=value%3Aasc&facets%5B4%5D%5Bfield%5D=language.name&facets%5B4%5D%5Blimit%5D=2000&facets%5B4%5D%5Bsort%5D=value%3Aasc&facets%5B5%5D%5Bfield%5D=date.original&facets%5B5%5D%5Binterval%5D=month&facets%5B6%5D%5Bfield%5D=date.changed&facets%5B6%5D%5Binterval%5D=month&facets%5B7%5D%5Bfield%5D=disaster.name&facets%5B7%5D%5Blimit%5D=2000&facets%5B7%5D%5Bsort%5D=value%3Aasc"
        }
      },
      "took": 13,
      "totalCount": 9,
      "count": 9,
      "data": [
      {
        "id": "3451813",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2019-12-20T21:06:14+00:00",
            "created": "2018-09-30T00:00:00+00:00",
            "original": "2018-09-30T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "preview":
            {
              "url-thumb": "https:\/\/reliefweb.int\/sites\/default\/files\/styles\/thumbnail\/public\/previews\/21\/2a\/212aacdd-4641-32a5-836b-f7795e3ff2ea.png"
            },
            "filename": "afghanistan_emergency_food_security_assessment_-_december_2018.pdf",
            "description": "",
            "id": "2113275",
            "url": "https:\/\/reliefweb.int\/attachments\/212aacdd-4641-32a5-836b-f7795e3ff2ea\/afghanistan_emergency_food_security_assessment_-_december_2018.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "disaster_type": [
          {
            "name": "Drought"
          }],
          "source": [
          {
            "shortname": "WFP"
          },
          {
            "shortname": "Govt. Afghanistan"
          },
          {
            "shortname": "FAO"
          },
          {
            "shortname": "FSC"
          }],
          "id": 3451813,
          "title": "Afghanistan Emergency Food Security Assessment, August \u2013 September 2018",
          "url": "https:\/\/reliefweb.int\/node\/3451813",
          "body-html": "<p><strong>EXECUTIVE SUMMARY<\/strong><\/p>\n<p>Afghanistan is currently facing a severe drought that has affected up to 20 provinces, mostly in the northern and western parts of the country. During the winter of 2017-2018, extremely poor rain and snow-fall, combined with unseasonably high temperatures, resulted in a shortage of water for rainfed and irrigated agriculture during the critical growing periods for the main wheat crop. Consequently, the harvest was less than 50 percent of the normal level and, in some locations, was the fifth consecutive year with below-average production. Affecting rural areas with high levels of chronic food insecurity and undernutrition, the drought has led to some displacement to urban centres and set back efforts to achieve Zero Hunger by causing acute needs for millions of rural people.<\/p>\n<p>In March 2018, the Ministry of Agriculture, Irrigation, and Livestock (MAIL), WFP, and FAO used data from the 2016\/17 Afghanistan Living Conditions Survey (ALCS) to initially estimate the levels of acute food insecurity caused by the drought. The analysis focused on populations engaged in agricultural activities, as well as the pre-existing levels of food insecurity and estimated that 1.4 million people across 20 provinces would need food-based assistance. After the harvest, this Emergency Food Security Assessment (EFSA) was undertaken to gather actual on-the-ground, field-level data.<\/p>\n<p>The 2018 Emergency Food Security Assessment (EFSA) collected information from more than 16,000 rural households in all 34 provinces across the country. The main objective was to better understand the impact of the drought on household food security and livelihoods and to estimate the number of people who will require humanitarian assistance. Drawing on a range of food security indicators the following was concluded from the analysis:<\/p>\n<ul>\n<li>\n<p>Out of the 17 million rural population residing in the 20 drought affected provinces around 10.5 million people (3.9 million highly and 6.6 million moderately) were affected by drought.<\/p>\n<\/li>\n<li>\n<p>Of the 10.5 million drought-affected, 3.5 million were also found to be highly food insecure after the harvesting time and require emergency food and nutrition assistance through the next harvest in 2019.<\/p>\n<\/li>\n<\/ul>\n<p>According to the multi-sectoral analysis, food insecure households are:<\/p>\n<ul>\n<li>\n<p>More likely to be headed by a woman.<\/p>\n<\/li>\n<li>\n<p>Less likely to use drinking water from an improved source<\/p>\n<\/li>\n<li>\n<p>More likely to have borrowed food or cash in last 3 months<\/p>\n<\/li>\n<li>\n<p>More likely to have increased tensions inside the house.<\/p>\n<\/li>\n<li>\n<p>More likely to rely on non-agricultural wage labour<\/p>\n<\/li>\n<li>\n<p>Less likely to own or access agricultural land and more likely to rely on rainfed agriculture \u2022 More likely to be asset poor<\/p>\n<\/li>\n<li>\n<p>More likely to live on &lt; $1\/person\/day<\/p>\n<\/li>\n<li>\n<p>More likely to cope by selling house, land or household assets; decreased health expenditure, beg, early marriage for daughters, and to migrate.<\/p>\n<\/li>\n<\/ul>\n<p>Rather than repeating the findings from the body of the report, a series of tables below present key findings by province and indicator, using a red (alert), yellow (caution) and green (acceptable) colour scheme. The indicators are explained below:<\/p>\n<ol>\n<li>\n<p>Food security: level of food insecurity of the households in the province, where red is extremely high.<\/p>\n<\/li>\n<li>\n<p>Water: percentage of households accessing drinking water from improved sources.<\/p>\n<\/li>\n<li>\n<p>Coping: level of household coping as per the reduced coping strategies index.<\/p>\n<\/li>\n<li>\n<p>Protection: percentage of households responding that tensions inside the home have increased due to the drought.<\/p>\n<\/li>\n<li>\n<p>Disabled member: percentage of households with at least one mentally or physically disabled member.<\/p>\n<\/li>\n<li>\n<p>Debt: percentage of households who borrowed money in the 3 months prior to the survey.<\/p>\n<\/li>\n<li>\n<p>Shock \u2013 reduced income: percentage of households reporting that reduced income as a shock.<\/p>\n<\/li>\n<li>\n<p>Seeds for next year: percentage of households not having any seed for planting in the next season.<\/p>\n<\/li>\n<\/ol>\n<p>In general the situation is not good across the country but some regions such as central (excluding Kabul) and the southeast regions are doing better than the rest of the country. The most affected regions are the western provinces plus the eastern region.<\/p>\n<p><strong>Recommendations<\/strong><\/p>\n<p>A holistic and strategic response is recommended to improve the food and nutrition security of the\nmost affected households and communities, focusing on the short-, medium-, and longer-term.<\/p>\n<p><strong>Short-term<\/strong><\/p>\n<ol>\n<li>\n<p>The emergency response should focus on provision of food or cash transfers as well as\nnutrition treatment and prevention programmes to up to 3.5 million people until the next\nharvest in mid-2019.<\/p>\n<\/li>\n<li>\n<p>Efforts should be made to improve access to safe and plentiful supplies of water in droughtaffected communities.<\/p>\n<\/li>\n<\/ol>\n<p><strong>Medium-term<\/strong><\/p>\n<ol start=\"3\">\n<li>\n<p>The consolidated response should be framed around a triple nexus approach. The\nhumanitarian response should be linked to development gains such as access to water,\nagriculture and resilience building which will also have longer-term development and peace\nbenefits.<\/p>\n<\/li>\n<li>\n<p>Early warning systems should be strengthened, and early funding mechanisms established to\nfacilitate early response.<\/p>\n<\/li>\n<\/ol>\n<p><strong>Longer-term<\/strong><\/p>\n<ol start=\"5\">\n<li>The Government and international community should work together to establish shock\nresponsive safety nets programmes for the most vulnerable communities in the most\nvulnerable parts of the country.<\/li>\n<\/ol>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/3451813"
      },
      {
        "id": "3007854",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2019-02-24T15:57:11+00:00",
            "created": "2017-05-01T00:00:00+00:00",
            "original": "2017-05-01T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "preview":
            {
              "url-thumb": "https:\/\/reliefweb.int\/sites\/default\/files\/styles\/thumbnail\/public\/previews\/75\/90\/75907130-c788-3e0c-bbc9-4fc76d4a9726.png"
            },
            "filename": "alcs_2017.pdf",
            "description": "392 pages",
            "id": "2043988",
            "url": "https:\/\/reliefweb.int\/attachments\/75907130-c788-3e0c-bbc9-4fc76d4a9726\/alcs_2017.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "Govt. Afghanistan"
          }],
          "id": 3007854,
          "title": "Afghanistan Living Conditions Survey 2016-17",
          "url": "https:\/\/reliefweb.int\/node\/3007854",
          "body-html": "<p>EXECUTIVE SUMMARY<\/p>\n<p><strong>Introduction<\/strong><\/p>\n<p>The Afghanistan Living Conditions Survey (ALCS) is the longest-running and most comprehensive source of information about the social and economic situation of people in Afghanistan. With the results of the six successive surveys, the Central Statistics Organization of Afghanistan has provided the Government of Afghanistan, civil society, researchers and the international community with an increasingly wide array of national indicators and statistics that are required to monitor socio-economic development in Afghanistan. The survey produces information at national and provincial level and the 2016-17 round covered 19,838 households and 155,680 persons across the country. The ALCS is unique in the sense that it includes the nomadic Kuchi population of Afghanistan. Another distinguishing feature of the survey is the continuous data collection during a cycle of 12 months, which captures the seasonal variation in a range of indicators.<\/p>\n<p>Afghanistan was one of the 193 countries to endorse the 2030 Agenda for Sustainable Development in September 2015. This fifteen-year agenda (2015-2030) replaces the Millennium Development Goals (MDG) framework and guides the international community to achieve three main objectives: end extreme poverty, fight inequality and injustice, and protect the planet. The ALCS is the main source for monitoring the implementation of the Sustainable Development Agenda in Afghanistan. The ALCS 2016-17 covered 20 indicators for 12 of the 17 Sustainable Development Goals (SDGs).<\/p>\n<p>Up to the present ALCS 2016-17, the successive surveys have recorded significant improvements in many development indicators (including, education, maternal health, water and sanitation), while other indicators (e.g. employment, poverty and food-security) have fluctuated over time. Alongside a continued improvement on some indicators, the present results indicate stagnation for many others. The most concerning among these are the indicators on education and gender equality. Moreover, the ALCS analysis reveals a continued process of farmland fragmentation, a worrying situation in Afghanistan\u2019s labour market and large increases in food insecurity and poverty, compared to previous ALCS assessments.<\/p>\n<p>The picture of stagnation and deterioration should be seen against the recent worsening of the security situation in the country, the large influx of returnees, the reduction of international presence in and aid to Afghanistan and macro-economic conditions. In addition, more structural factors continue to play a role in impeding development in the country, including the very low participation of women in the economy and in society in general, the low levels of education and skills in the country\u2019s work force and the poor performance of the labour market.<\/p>\n<p>Moreover, the very high fertility and population growth rates generate unsustainable conditions for development in the country. Analysis of the ALCS shows that these factors offset much of government and donor development efforts, and undermine the capacity of many households and individuals \u2013 in particular women and girls \u2013 to escape poverty and poor health. More and more people are reaching working age and entering the labour force, while the capacity of the labour market to provide jobs for them cannot keep up. Similarly, the rapid population growth also puts pressure on the education and health systems and on the amount of available arable land.<\/p>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/3007854"
      },
      {
        "id": "1057046",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2015-06-30T01:20:42+00:00",
            "created": "2015-06-29T03:09:40+00:00",
            "original": "2015-06-07T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "preview":
            {
              "url-thumb": "https:\/\/reliefweb.int\/sites\/default\/files\/styles\/thumbnail\/public\/previews\/59\/0a\/590a3998-129c-388b-b403-1201ae16d22c.png"
            },
            "filename": "Pre-harvest Food Security Assessment \u2013 WFP (June \u2013 2015).pdf",
            "description": "",
            "id": "1807202",
            "url": "https:\/\/reliefweb.int\/attachments\/590a3998-129c-388b-b403-1201ae16d22c\/Pre-harvest%20Food%20Security%20Assessment%20%E2%80%93%20WFP%20%28June%20%E2%80%93%202015%29.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "disaster_type": [
          {
            "name": "Cold Wave"
          },
          {
            "name": "Flash Flood"
          },
          {
            "name": "Flood"
          },
          {
            "name": "Land Slide"
          },
          {
            "name": "Snow Avalanche"
          }],
          "source": [
          {
            "shortname": "FEWS NET"
          },
          {
            "shortname": "WFP"
          },
          {
            "shortname": "Govt. Afghanistan"
          },
          {
            "shortname": "FAO"
          }],
          "id": 1057046,
          "title": "Afghanistan Pre-Harvest Assessment 2015 - Preliminary Findings",
          "url": "https:\/\/reliefweb.int\/node\/1057046",
          "body-html": "<p><strong>Outline<\/strong><\/p>\n<ol>\n<li>Food Security Definition and Dimensions<\/li>\n<li>Overview of Pre-Harvest Assessment 2015: Background, Objectives<\/li>\n<li>Methodology: Sampling, Tools, Key Indicators<\/li>\n<li>Agro-climatic Factors<\/li>\n<li>Regional Context<\/li>\n<li>Regional Findings<\/li>\n<li>Key Messages<\/li>\n<li>Next Steps<\/li>\n<\/ol>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/1057046"
      },
      {
        "id": "421570",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2017-08-06T08:28:54+00:00",
            "created": "2011-06-22T01:03:44+00:00",
            "original": "2011-06-20T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "filename": "Full_Report.pdf",
            "description": "",
            "id": "1934719",
            "url": "https:\/\/reliefweb.int\/attachments\/f2c5c6a0-f252-38e2-a195-9a95bb0ea990\/Full_Report.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "FEWS NET"
          }],
          "id": 421570,
          "title": "Afghanistan Food Security Alert: June 20, 2011",
          "url": "https:\/\/reliefweb.int\/node\/421570",
          "body-html": "<p>Poor rainfed wheat harvest in northern Afghanistan raises need for assistance<\/p>\n<p>Satellite derived rainfall estimates indicate that most of Afghanistan had an untimely and inadequate rain and snow season this year. As a result, there will be heavy losses in rainfed wheat crops, underperforming irrigated wheat crops, poor pasture conditions, and low income earning opportunities in northern Afghanistan and the central highlands this year.<br \/>\nUltimately, households in northern Afghanistan who are dependent on rainfed crops as a primary food source or on-farm agricultural labor as a major income source will be unable to afford essential non-food expenditures during Fall 2011 that are needed to protect livelihoods (e.g., input purchases). Without additional assistance these households will likely face food deficits beginning in the fall and intensifying until the start of the 2012 harvest in May.<\/p>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/421570"
      },
      {
        "id": "332091",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2011-03-21T15:46:57+00:00",
            "created": "2009-10-31T04:00:00+00:00",
            "original": "2009-10-31T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "filename": "732AC034EAF0C654C125766500338844-Full_Report.pdf",
            "description": "",
            "id": "1660390",
            "url": "https:\/\/reliefweb.int\/attachments\/2d151841-98a9-327e-bfe0-22688a5a522c\/732AC034EAF0C654C125766500338844-Full_Report.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "Govt. Afghanistan"
          }],
          "id": 332091,
          "title": "Summary of the National Risk and Vulnerability Assessment 2007\/8 - A profile of Afghanistan",
          "url": "https:\/\/reliefweb.int\/node\/332091",
          "body-html": "<p>Introduction<\/p>\n<p>Kabul, October 2009 - Nearly\nevery second Afghan citizen is under 15 years of age - in numbers: 12\nmillion or 49 percent of the population. Also, more than one out of three\nAfghans - some 9 million people or 36 percent of the population - lives\nin absolute poverty and cannot meet his or her basic needs. On the positive\nside, the proportion of primary-school age children that is attending school\nhas increased from only 37 to 52 percent in just over two years time. These\nare only a few of the numerous figures of the National Risk and Vulnerability\nAssessment (NRVA) 2007\/8 conducted by the Central Statistics Organization\n(CSO) and the Ministry of Rural Rehabilitation and Development (MRRD) of\nthe Islamic Republic of Afghanistan. The assessment is based on statistical\ndata collected during a one-year period from August 2007 through August\n2008 - making it the most extensive statistical venture of its kind in\nAfghanistan. 156 enumerators were involved, male and female interviewers\ntravelled to 395 districts in 34 provinces, collected data from more than\n20.000 households with over 152,000 Afghan citizens.\n<p>Afghanistan is facing fundamental economic\nand social change. To measure progress in social and economic development,\nas well as in poverty reduction, it is imperative that the Government of\nAfghanistan has access to information on the social and economic situation\nof the population. This information will serve to assist the government\nin adapting policy to changing socio-economic conditions, and allow it\nto monitor the impact of such policies on the more vulnerable groups in\nthe country and on the country as a whole.\n<p>The NRVA as it stands today is the only\ncomprehensive nation-wide multi-purpose household survey in Afghanistan,\nenabling a large amount of cross-section analysis. The present 2007\/8 NRVA\nis the third of three successive rounds of surveys, following NRVA 2003\nand 2005. The NRVA 2003 survey was carried out with co-operation of the\nWorld Food Programme (WFP) and supported by the Vulnerability Analysis\nUnit (VAU) of the Ministry of Rural Rehabilitation and Development. The\nsecond NRVA survey was launched in 2005, with CSO and MRRD as the implementing\nagencies. The NRVA 2005 was a significant improvement in terms of sample\ndesign and coverage.\n<p>As a follow-up to the first two surveys,\nthe third NRVA survey was launched in 2007, jointly by MRRD and CSO, and\nwith co-operation and funding from the European Commission. The NRVA 2007\/8\nwas based on a smaller sample of 20,576 households, but with further improvements\nin the questionnaire, sample design and coverage. It was designed to provide\nthe government and other agencies with more robust and up-to-date socio-economic\ndata. \n<p>This brochure provides a summarized overview\nto the National Risk and Vulnerability Assessment 2007\/8. It collects several\nimportant findings and delivers some background information. It also helps\nto interpret complicated contexts - but it does not claim to cover all\nfacts and data, which are collected and compiled in the NRVA 2007\/8 main\nreport. The individual reader with certain fields of interests and requests\nis advised to look for more information in the specific chapters of this\nreport and in the large amount of predefined tables on the website of CSO\n\'www.nrva.cso.gov.af\' to serve the more advanced user. The assessment has\n12 chapters, which cover the fields: population structure and change, labour\nforce characteristics, agriculture, poverty, education, health, housing,\nposition of women, household shocks and community preferences and recommendations.\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/332091"
      },
      {
        "id": "295934",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2011-03-21T15:27:53+00:00",
            "created": "2009-01-31T05:00:00+00:00",
            "original": "2009-01-31T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "filename": "2D2822B02DBACB39852575510061EAD1-Full_Report.pdf",
            "description": "",
            "id": "1654348",
            "url": "https:\/\/reliefweb.int\/attachments\/70084121-e91d-35b6-bea0-eb8618da6807\/2D2822B02DBACB39852575510061EAD1-Full_Report.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "Govt. Afghanistan"
          },
          {
            "shortname": "UNODC"
          }],
          "id": 295934,
          "title": "Afghanistan Opium Winter Assessment Jan 2009",
          "url": "https:\/\/reliefweb.int\/node\/295934",
          "body-html": "<ol>\n<li>FINDINGS<\/li>\n<\/ol>\n<p>1.1. GENERAL FINDINGS\n<p>Opium poppy cultivation trends\n<p>The 2009 Opium Winter Rapid Assessment\nis based on a small sample of villages and the results are meant to be\nindicative. The main findings are summarised below (see also Table 1):\nFollowing the 19 % reduction in opium cultivation in 2008 (157,000 ha),\nthe 2009 Opium Winter Rapid Assessment (ORA) anticipates a further decrease\nin opium cultivation.\n<p>- There are no provinces which are likely\nto show an increase in opium cultivation.\n<p>- The eighteen provinces reported to\nbe poppy-free in 2008, are likely to remain poppyfree in 2009. ORA results\nindicate that fourteen are confirmed as likely to remain poppyfree while\nthe other four provinces, Nangarhar Ghor, Samangan, and Sari Pul, could\nnot yet be ascertained. Nangarhar is likely to be almost poppy free but\nmore data is needed for confirmation. The results for Ghor, Samangan and\nSari Pul provinces could not be assessed since cultivation in these provinces\ntakes place during the spring season (March\/April).\n<p>- A strong decrease in opium cultivation\nis expected in Baghlan and Hirat provinces and opium elimination activities\ncan make these provinces poppy-free.\n<p>- A decrease in opium cultivation is\nexpected in seven provinces: Badakhshan, Badghis, Faryab, Kabul, Kapisa,\nKunar and Laghman. Badakhshan and Faryab provinces have the potential to\nbecome poppy-free if opium cultivation in spring is controlled. The level\nof opium cultivation in other provinces will remain significantly low.\n<p>- The seven provinces in the south and\nsouth-west region (Day Kundi, Farah, Hilmand, Kandahar, Nimroz, Uruzgan\nand Zabul) which accounted for 98% of Afghanistan\'s opium cultivation in\n2008, are likely to show a decrease in 2009. It is expected that this region\nwill still remain the most significant with over 90% cultivation in Afghanistan.\n<p>- Overall, the cultivation of opium in\nAfghanistan is likely to decrease in 2009 and the number of poppy-free\nprovinces may increase to twenty-two if timely and appropriate poppy eradication\nmeasures are implemented in Baghlan, Hirat, Badakhshan and Faryab provinces.\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/295934"
      },
      {
        "id": "245838",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2021-08-31T09:41:03+00:00",
            "created": "2007-10-05T04:00:00+00:00",
            "original": "2007-10-05T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "preview":
            {
              "url-thumb": "https:\/\/reliefweb.int\/sites\/default\/files\/styles\/thumbnail\/public\/previews\/b6\/03\/b603720b-725d-3847-96e1-a315ce5403e2.png"
            },
            "filename": "FEWS NET_Northern Wheat Trader Survey and Afghan Food Security_Aug 2007_En_0.pdf",
            "description": "",
            "id": "2249638",
            "url": "https:\/\/reliefweb.int\/attachments\/b603720b-725d-3847-96e1-a315ce5403e2\/FEWS%20NET_Northern%20Wheat%20Trader%20Survey%20and%20Afghan%20Food%20Security_Aug%202007_En_0.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "FEWS NET"
          }],
          "id": 245838,
          "title": "Northern wheat trader survey and Afghan food security",
          "url": "https:\/\/reliefweb.int\/node\/245838",
          "body-html": "<p>A special report by the Famine Early\nWarning Systems Network (FEWS NET)<\/p>\n<p>EXECUTIVE SUMMARY\n<p>This study is part of the &quot;Central\nAsian Regional Wheat Markets and Afghan Food Security Initiative.&quot;\nIt is one of five complementary activities that build on and expand the\ncurrent knowledge base, and aim to clarify critical issues surrounding\nCentral Asian regional wheat markets and their relationship to Afghan food\nsecurity.\n<p>In Afghanistan, bread is the staple food\nand on average accounts for over half of the calories in the diet. Approximately\none-fourth to one-third of the wheat(1) and flour used for making bread\nis imported, mainly from Pakistan, but also from Uzbekistan and Kazakhstan.\nImports are increasingly important, especially for urban areas. Imports\nwere also found to account for a significant portion of the markets in\nrural areas, even in the &quot;breadbasket&quot; provinces of the north.\n<p>The increasingly high levels of imports\nare the combination of many factors:\n<p>- Overall, national wheat production\ndoes not meet total requirements due to primitive agricultural practices,\nharsh growing conditions, and limited irrigation.\n<p>- Most wheat is consumed on-farm. Marketable\nsupplies of wheat are highly limited - on a national scale, less than\n10 percent of production reaches the markets.\n<p>- Markets are inundated by relatively\nlow priced flour from neighboring countries, notably Pakistan, which subsidizes\nits wheat industry.\n<p>- Consumers have acquired increasing\npreference for imported flour.\n<p>Based on the survey and a review of customs\ndata, total wheat and flour imports are estimated at one million metric\ntons for 007\/08, down slightly from 1. million metric tons\nlast year. Imports by origin are estimated as follows: Pakistan 600,000\nMT, Uzbekistan 00,000 MT, Kazakhstan 150,000 MT, Iran 5,000(2)\nMT and other 25,000 MT. Food aid for 2007 is estimated to be 100,000 MT,\na similar quantity as 2006. Nearly all of the commercial imports (over\n90 percent) will be imported as flour. Nearly all of the imported commercial\nwheat is from Kazakhstan.\n<p>Interviews were carried out in early\nJune 2007 with about a dozen traders and millers in Hirat and Mazar-i-Sharif\nwith brief visits to the border points at Tourghundy, Termez, Andkhoi and\nto the markets in Shibirghan and Kabul. Market participants cited limited\ncredit and storage facilities as their major business impediments. Security\nwas also an important concern. The majority of traders rent or lease readily\navailable transport. The greatly improved road situation has stimulated\nthe transport industry and the number of highway checkpoints, have been\ngreatly reduced.\n<p>This season, the Hirat wheat crop of\nover 400,000 MT is estimated to be the largest of any province, but it\nis mostly consumed on-farm. The survey found that 60-70 percent of the\nflour in Hirat markets originates from Pakistan and another 20 to 30 percent\nfrom Kazakhstan. The balance (roughly 5 to 10 percent) is Iranian, Uzbek,\nand local flour. Hirat is filled with consumer goods and other commodities\nfrom nearby Iran. However, there is little flour or wheat due to Iran\'s\ntrade barriers. Relatively minor quantities of wheat and some flour from\nKazakhstan arrive via rail at Tourghundy at the border directly north of\nHirat. Tourghundy could serve once again, as it did in the past, as an\nalternative route during future food emergency operations. Wheat and flour\nin the Hirat markets are largely consumed in the city itself, but important\nflows also move into nearby Badghis and Ghor provinces.\n<p>Mazar is one of the key wheat and flour\nmarkets in the country, and has four operational industrial flour mills.\nIt is in the heart of the &quot;breadbasket&quot; and located near the\nstrategic border crossing at Hairatan \/ Termez. In the past Hairatan was\na major storage and supply hub for WFP. Wheat and flour from the North\nand from Central Asia is temporarily stored in Mazar before being transshipped\nmainly to the north and central regions and but also to Kabul.\n<p>There are no formal systems of grades\nand standards, or effective phytosanitary or health controls for wheat\nand flour. Most transactions occur after the buyer or his agent physically\nand subjectively inspect the goods. Flour is often sold based on established\nbrands, but alteration and counterfeiting occur. Afghanistan\'s shattered\neconomy serves as a dumping ground for low-quality wheat and flour from\nneighboring countries.\n<p>The main obstacles to trade in the region\nsurrounding Afghanistan include transportation bottlenecks, exchange controls,\nand cumbersome customs procedures. However, these obstacles are not a major\nproblem in the case of wheat and flour, with the exception of exports from\nIran. Well-established formal and informal arrangements allow for ample\ncommerce in wheat and flour. Import tariffs are only 3.5 percent, and hence\ncontraband is not a major issue.\n<p>Key aspects of regional wheat and\nflour markets include the following:\n<p>- Afghanistan has replaced Iran as the\nregion\'s largest wheat importer (mostly in the form of flour).\n<p>- The leading exporters (and most important\nsources of supplies for Afghanistan) are Pakistan, Kazakhstan, and Uzbekistan.\n<p>- Pakistan accounts for 500,000 to 600,000\nMT (60 percent) of flour imports to Afghanistan. These imports are almost\nentirely as flour. Pakistan\'s huge surpluses are normally a stable source\nof supply for Afghanistan, although occasional interruptions occur.\n<p>- Iran is an important and uncertain\n&quot;swing factor&quot; in the region. Iran has gone from among the world\'s\nlargest wheat importers to self-sufficiency. Iran may soon open up exports\nto Afghanistan.\n<p>- Uzbekistan has steadily shifted large\nportions of its massive irrigation schemes from cotton to wheat. It is\nAfghanistan\'s second largest supplier of flour, upwards of 200,000 MT.\nAfghanistan is perhaps the most important export market for Uzbek flour.\n<p>- Kazakhstan is a major player in world\nwheat markets with huge surpluses from a bumper crop of over 13.5 million\nMT. Kazakhstan normally has by far the largest exportable supplies of wheat\nand flour in the region, over seven million MT. However, production is\nentirely rain fed and subject to frequent drought. Still, with Iran now\nout of the market for Kazakh wheat, ample supplies will likely be available\nfor shipment to Afghanistan for the next several years.\n<p>- Turkmenistan, Tajikistan, and the Kyrgyz\nRepublic do not currently have major affects on Afghan food security situation\neither as suppliers or potential competitors.\n<p>The nearly-completed national &quot;Ring\nRoad,&quot; along with thousands of kilometers of improved secondary roads,\nhas already had huge benefits to the overall Afghan economy. An important\nfinding of this survey is that in recent years, imported flour has increasingly\nmoved further into the remote districts. This has enhanced overall food\nsecurity but has likely had detrimental market affects on farmers. These\nare major changes in the rural economy and food security situation, and\nmerit further study.\n<p>A new railroad from Iran to Hirat is\nscheduled to be completed by the end of 2008 will greatly facilitate trade.\nIn addition, two major projects which will improve ports on the Gulf of\nOman and connect with highways into Afghanistan will open new alternative\ntrade routes to India, Pakistan, and other international markets, and will\ntherefore help enhance food security in Afghanistan. In addition, a couple\nof additional future factors that could affect Afghan food security include\nthe massive transport infrastructure projects which will facilitate local\nand regional trade are among the most significant on the positive side\nand the on-going civil strife and political uncertainty on the negative\nside.\n<p>The survey confirmed that the principal\nwheat and flour markets in the northern and western areas (and, in fact,\nin much of the country) are closely integrated with markets within the\nregion, and act to stabilize supplies and prices. Still, with an important\nportion of the urban population highly dependent on these imports, any\nmajor shock to the market and transport systems could cause substantial\nprice increases.\n<p>The following are some of the many\n&quot;information gaps&quot; which require further attention:\n<p>- Aspects of wheat and flour markets\nin neighboring countries as some of these countries are largely closed\nto the outside world.\n<p>- Present and future food security impacts\nof the Ring Road and other massive transport projects on the wheat industry,\nfarmers, and consumers.\n<p>- Dynamics between urban and rural markets\nfor wheat and flour.\n<p>- Basic statistical and market information\nincluding crop production, imports, population, and prices. The information\nis either lacking and\/or highly unreliable.\n<p>RECOMMENDATIONS\n<p>1. Given the vital role of imported flour\nin Afghanistan, early warning activities must include regular monitoring\nand analysis of wheat and flour markets in the broader region.\n<p>2. The highest priority should be placed\non establishing a system of exchange of food security and market information\nbetween interested parties in Afghanistan and Pakistan. WFP could play\nan important role as a facilitator and\/or participant.\n<p>3. As a prerequisite for monitoring and\nanalyzing regional markets, FEWS NET and WFP should take steps towards\ngaining deeper knowledge and understanding of these markets going beyond\nthe initial &quot;snapshots&quot; of this study. The focus should be on\npractical, &quot;real world&quot; insights that will assist early warning\nand food security analysis and programming, including food aid.\n<p>4. A systematic, in-depth analysis of\nAfghanistan\'s food security situation in the context of Central Asian regional\nmarkets should be carried out in May or June, just prior to the beginning\nof each marketing year, with quarterly updates.\n<p>5. Early warning activities must include\nregular monitoring and analysis of the wheat and flour markets within Afghanistan\nitself. This should include the active and mutually-beneficial involvement\nof a small select group of key informants (i.e. merchants, traders, millers,\netc.).\n<p>6. FEWS NET and WFP should explore other\npossible activities that might be of mutual benefit involving information\ncollection and market monitoring such as improved market price collection\nand regular reporting.\n<p>7. WFP should make every effort to purchase\nflour, wheat, and other foodstuffs (including fortified biscuits) from\nlocal sources in Afghanistan. This will have an important impact on the\nfledgling milling industry and will also help farmers and the economy in\ngeneral.\n<p>8. Given increased insecurity and limited\navailability of local wheat, WFP should further explore sourcing wheat\nfrom Uzbekistan and Kazakhstan. Flour from both of these countries could\nbe brought into Hirat and Mazar taking advantage of regular rail transport\nand ample storage at the border points of Tourghundy and Hairatan. Iran\nshould also be considered.\n<p>9. WFP could continue to play an important\nrole in food security by supporting Food for Work projects focused on improved\nwheat production, storage, and transport.\n<p>10. Follow up is recommended on issues\noutlined in this survey under &quot;Information Gaps.&quot; For example,\nan examination of the impacts on food security of the massive transformation\nof the road systems.\n<p>Notes:\n<p>(1) Throughout this report &quot;wheat&quot;\nrefers to wheat grain and &quot;flour&quot; is wheat flour.\n<p>(2) Imports from Iran may well become\nmuch higher since export controls are likely to be relaxed.\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/245838"
      },
      {
        "id": "327936",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2011-03-21T15:44:46+00:00",
            "created": "2005-12-31T05:00:00+00:00",
            "original": "2005-12-31T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "filename": "BF0DA64F0CCA5CE4C1257648004551AC-Full_Report.pdf",
            "description": "",
            "id": "1659636",
            "url": "https:\/\/reliefweb.int\/attachments\/0b3a4d84-42ff-3556-adbd-6bcdd62d0219\/BF0DA64F0CCA5CE4C1257648004551AC-Full_Report.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "Govt. Afghanistan"
          }],
          "id": 327936,
          "title": "Afghanistan: National risk and vulnerability assessment 2005",
          "url": "https:\/\/reliefweb.int\/node\/327936",
          "body-html": "<p>Key Findings and Recommendations<\/p>\n<p>The NRVA 2005 was the second national\nexercise in data collection on risk and vulnerability factors that affect\nthe Afghan population. The main objective of NRVA 2005 was to gather information\nto update and guide policy-making decisions in development programmes and\nto improve the efficacy of sectoral interventions. Between June and August\nof 2005, a national survey was carried out with a sample of 30,822 households\nin 34 provinces (1,735 Kuchi, 23,220 rural and 5,867 urban).\n<p>Data shows that the female to male ratio\nstarts to decline above 24 years of age. There are higher mortality rates\nfor women above 24 years of age compared to those rates of men in the same\nage groups. This appears to be related to the cumulative effect of disadvantageous\nconditions for women; such as lack of health facilities and practices,\npoor nutrition and frequency of marriages of girls under 15 years of age.\nIn contrast to its neighbours, Afghanistan presents a gender gap that favours\nmale survivals. This situation prevails, even after years of war in which\nmale mortality would typically be higher than female mortality. Access\nto education, provision of health facilities and professional attention\nin rural areas deserve a high priority to rectify this situation. A demographic\nand public health study should assess these findings as soon as possible.\n<p>Surprisingly, only 2% of the rural and\nurban households reported having disputes about property rights. Further\ninvestigation is required to clarify this finding considering that contested\nproperty rights are expected in post-conflicts. Clear property rights are\nnecessary, but not sufficient by themselves, for sustainable resource management.\n<p>Seventy-three percent of the households\nin Afghanistan perceive that they are in a comparable or worse situation\nwith respect to one year prior to the survey. Twentyfour percent perceive\nbeing slightly better off and only 2% perceive a clear improvement. The\nurban households had 5% to 6% more optimistic perceptions compared to rural\nand Kuchi households, respectively.\n<p>Forty-four percent of the Afghan households\nperceive themselves as food insecure to different degrees, 28% of the urban\nhouseholds perceive themselves to be food insecure while in contrast, 40%\nof the Kuchi households and 48% of the rural households perceive this condition.\nThese perceptions are in agreement with other findings. Out of the largest\nloans granted to the households during the year prior to the survey, 45%\nof the urban households used them to purchase food, and about 65% of both\nKuchi and rural households also used them to cope with food insecurity.\nFurther research is recommended to assess food insecurity and vulnerability\nof different groups and locations.\n<p>Fifty percent of the participating households\nin cash for work programmes in Uruzgan acquired income generating skills\nand to a lesser extent in Balkh, Kandahar, Takhar and Nangarhar. These\ncases, clearly aimed towards financial sustainability, could be extended\nor intensified.\n<p>Further work is required to assess the\nrural-urban, rural-Kuchi, and urban-Kuchi gap in terms of intake and quality\nof diet. The gap between urban vis-\u00e0-vis Kuchi and rural households is\ndramatic: more than 53% for maternal health, more than 36% in access to\nsafe drinking water and more than 25% in improved sanitation. This does\nnot mean that urban well-being should be taken for granted, but these gaps,\nand others estimated in the report, should be used to prioritize development\nactions.\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/327936"
      },
      {
        "id": "119957",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2003-02-27T05:00:00+00:00",
            "created": "2003-02-27T05:00:00+00:00",
            "original": "2003-02-27T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "disaster_type": [
          {
            "name": "Drought"
          }],
          "source": [
          {
            "shortname": "Govt. USA"
          }],
          "id": 119957,
          "title": "USDA: Afghanistan - Heavy rains arrive but more needed",
          "url": "https:\/\/reliefweb.int\/node\/119957",
          "body-html": "<p><br>\nHeavy rain and snow fell from February 14 through February 17, 2003 providing widespread relief to Afghanistan and causing flooding in some areas. Additional rainfall this past week was concentrated in the northeast portion of the country. However, estimated seasonal precipitation remains far below normal but better than last year for much of Afghanistan. Regional rainfall deficits are greatest in the southern and western regions where long-term drought persists.<br><a href=\"http:\/\/www.reliefweb.int\/w\/map.nsf\/wPreview\/F08CD970239818E385256CDA007B36E9?Opendocument\">MAP: Afghanistan: Estimated Seasonal Rainfall (Sep 1, 2002 - Jan 20, 2003)<\/a><\/p>\n<p class=\"c2\">Seasonal Cumulative Precipitation by Region<\/p>\n<p><br><\/p>\n<div class=\"c3\"><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_0.gif\" width=\"415\" height=\"281\"><\/div>\n<div class=\"c3\"><\/div>\n<p><br><div class=\"c3\"><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_1.gif\" width=\"414\" height=\"280\"><\/div><\/p>\n<div class=\"c3\"><\/div>\n<p><br><div class=\"c3\"><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_2.gif\" width=\"383\" height=\"260\"><\/div><\/p>\n<p><br><div class=\"c3\"><\/div><\/p>\n<p><br><div class=\"c3\"><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_3.gif\" width=\"381\" height=\"255\"><\/div><\/p>\n<div class=\"c3\"><\/div>\n<p><br><div class=\"c3\"><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_4.gif\" width=\"412\" height=\"282\"><\/div><\/p>\n<div class=\"c3\">\n<p><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_5.gif\" width=\"415\" height=\"285\"><\/p>\n<\/div>\n<p><br><div class=\"c3\"><\/p>\n<p><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_6.gif\" width=\"420\" height=\"281\"><\/p>\n<p><img src=\"https:\/\/reliefweb.int\/sites\/reliefweb.int\/files\/resources\/00ACACA1BA3BB04C85256CDA00726C29_7.gif\" width=\"391\" height=\"262\"><\/p>\n<\/div>\n<p><br><p class=\"c2\">Estimated Seasonal Precipitation (Sep 1, 2002 - Feb 20, 2003)<\/p><\/p>\n<p>The growing season started in September with some optimism due in part to last year\u2019s improved weather in the north and recovery in wheat production following long-term drought. In the months prior to the recent storm, there had been very little rainfall. January precipitation was less than half of normal throughout the country. During the month of February, only the East and Central regions have received near normal rainfall.<\/p>\n<p>The areas that benefited most from the recent precipitation are the North-East, East and Central regions of the country. These regions combined produce one-third of the country\u2019s wheat. Approximately 80 percent of the wheat is irrigated.<\/p>\n<p>More rain is needed to increase moisture reserves for winter crops and recharge irrigation supplies. Wheat output will depend largely on moisture availability during the critical reproductive to grain-filling stage in the spring. The crop will be harvested from June through July.<\/p>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/119957"
      }],
      "embedded":
      {
        "facets":
        {
          "date.changed":
          {
            "type": "date",
            "data": [
            {
              "value": "2003-02-01T00:00:00+00:00",
              "epoch_ms": 1044057600000,
              "count": 1
            },
            {
              "value": "2003-03-01T00:00:00+00:00",
              "epoch_ms": 1046476800000,
              "count": 0
            },
            {
              "value": "2003-04-01T00:00:00+00:00",
              "epoch_ms": 1049155200000,
              "count": 0
            },
            {
              "value": "2003-05-01T00:00:00+00:00",
              "epoch_ms": 1051747200000,
              "count": 0
            },
            {
              "value": "2003-06-01T00:00:00+00:00",
              "epoch_ms": 1054425600000,
              "count": 0
            },
            {
              "value": "2003-07-01T00:00:00+00:00",
              "epoch_ms": 1057017600000,
              "count": 0
            },
            {
              "value": "2003-08-01T00:00:00+00:00",
              "epoch_ms": 1059696000000,
              "count": 0
            },
            {
              "value": "2003-09-01T00:00:00+00:00",
              "epoch_ms": 1062374400000,
              "count": 0
            },
            {
              "value": "2003-10-01T00:00:00+00:00",
              "epoch_ms": 1064966400000,
              "count": 0
            },
            {
              "value": "2003-11-01T00:00:00+00:00",
              "epoch_ms": 1067644800000,
              "count": 0
            },
            {
              "value": "2003-12-01T00:00:00+00:00",
              "epoch_ms": 1070236800000,
              "count": 0
            },
            {
              "value": "2004-01-01T00:00:00+00:00",
              "epoch_ms": 1072915200000,
              "count": 0
            },
            {
              "value": "2004-02-01T00:00:00+00:00",
              "epoch_ms": 1075593600000,
              "count": 0
            },
            {
              "value": "2004-03-01T00:00:00+00:00",
              "epoch_ms": 1078099200000,
              "count": 0
            },
            {
              "value": "2004-04-01T00:00:00+00:00",
              "epoch_ms": 1080777600000,
              "count": 0
            },
            {
              "value": "2004-05-01T00:00:00+00:00",
              "epoch_ms": 1083369600000,
              "count": 0
            },
            {
              "value": "2004-06-01T00:00:00+00:00",
              "epoch_ms": 1086048000000,
              "count": 0
            },
            {
              "value": "2004-07-01T00:00:00+00:00",
              "epoch_ms": 1088640000000,
              "count": 0
            },
            {
              "value": "2004-08-01T00:00:00+00:00",
              "epoch_ms": 1091318400000,
              "count": 0
            },
            {
              "value": "2004-09-01T00:00:00+00:00",
              "epoch_ms": 1093996800000,
              "count": 0
            },
            {
              "value": "2004-10-01T00:00:00+00:00",
              "epoch_ms": 1096588800000,
              "count": 0
            },
            {
              "value": "2004-11-01T00:00:00+00:00",
              "epoch_ms": 1099267200000,
              "count": 0
            },
            {
              "value": "2004-12-01T00:00:00+00:00",
              "epoch_ms": 1101859200000,
              "count": 0
            },
            {
              "value": "2005-01-01T00:00:00+00:00",
              "epoch_ms": 1104537600000,
              "count": 0
            },
            {
              "value": "2005-02-01T00:00:00+00:00",
              "epoch_ms": 1107216000000,
              "count": 0
            },
            {
              "value": "2005-03-01T00:00:00+00:00",
              "epoch_ms": 1109635200000,
              "count": 0
            },
            {
              "value": "2005-04-01T00:00:00+00:00",
              "epoch_ms": 1112313600000,
              "count": 0
            },
            {
              "value": "2005-05-01T00:00:00+00:00",
              "epoch_ms": 1114905600000,
              "count": 0
            },
            {
              "value": "2005-06-01T00:00:00+00:00",
              "epoch_ms": 1117584000000,
              "count": 0
            },
            {
              "value": "2005-07-01T00:00:00+00:00",
              "epoch_ms": 1120176000000,
              "count": 0
            },
            {
              "value": "2005-08-01T00:00:00+00:00",
              "epoch_ms": 1122854400000,
              "count": 0
            },
            {
              "value": "2005-09-01T00:00:00+00:00",
              "epoch_ms": 1125532800000,
              "count": 0
            },
            {
              "value": "2005-10-01T00:00:00+00:00",
              "epoch_ms": 1128124800000,
              "count": 0
            },
            {
              "value": "2005-11-01T00:00:00+00:00",
              "epoch_ms": 1130803200000,
              "count": 0
            },
            {
              "value": "2005-12-01T00:00:00+00:00",
              "epoch_ms": 1133395200000,
              "count": 0
            },
            {
              "value": "2006-01-01T00:00:00+00:00",
              "epoch_ms": 1136073600000,
              "count": 0
            },
            {
              "value": "2006-02-01T00:00:00+00:00",
              "epoch_ms": 1138752000000,
              "count": 0
            },
            {
              "value": "2006-03-01T00:00:00+00:00",
              "epoch_ms": 1141171200000,
              "count": 0
            },
            {
              "value": "2006-04-01T00:00:00+00:00",
              "epoch_ms": 1143849600000,
              "count": 0
            },
            {
              "value": "2006-05-01T00:00:00+00:00",
              "epoch_ms": 1146441600000,
              "count": 0
            },
            {
              "value": "2006-06-01T00:00:00+00:00",
              "epoch_ms": 1149120000000,
              "count": 0
            },
            {
              "value": "2006-07-01T00:00:00+00:00",
              "epoch_ms": 1151712000000,
              "count": 0
            },
            {
              "value": "2006-08-01T00:00:00+00:00",
              "epoch_ms": 1154390400000,
              "count": 0
            },
            {
              "value": "2006-09-01T00:00:00+00:00",
              "epoch_ms": 1157068800000,
              "count": 0
            },
            {
              "value": "2006-10-01T00:00:00+00:00",
              "epoch_ms": 1159660800000,
              "count": 0
            },
            {
              "value": "2006-11-01T00:00:00+00:00",
              "epoch_ms": 1162339200000,
              "count": 0
            },
            {
              "value": "2006-12-01T00:00:00+00:00",
              "epoch_ms": 1164931200000,
              "count": 0
            },
            {
              "value": "2007-01-01T00:00:00+00:00",
              "epoch_ms": 1167609600000,
              "count": 0
            },
            {
              "value": "2007-02-01T00:00:00+00:00",
              "epoch_ms": 1170288000000,
              "count": 0
            },
            {
              "value": "2007-03-01T00:00:00+00:00",
              "epoch_ms": 1172707200000,
              "count": 0
            },
            {
              "value": "2007-04-01T00:00:00+00:00",
              "epoch_ms": 1175385600000,
              "count": 0
            },
            {
              "value": "2007-05-01T00:00:00+00:00",
              "epoch_ms": 1177977600000,
              "count": 0
            },
            {
              "value": "2007-06-01T00:00:00+00:00",
              "epoch_ms": 1180656000000,
              "count": 0
            },
            {
              "value": "2007-07-01T00:00:00+00:00",
              "epoch_ms": 1183248000000,
              "count": 0
            },
            {
              "value": "2007-08-01T00:00:00+00:00",
              "epoch_ms": 1185926400000,
              "count": 0
            },
            {
              "value": "2007-09-01T00:00:00+00:00",
              "epoch_ms": 1188604800000,
              "count": 0
            },
            {
              "value": "2007-10-01T00:00:00+00:00",
              "epoch_ms": 1191196800000,
              "count": 0
            },
            {
              "value": "2007-11-01T00:00:00+00:00",
              "epoch_ms": 1193875200000,
              "count": 0
            },
            {
              "value": "2007-12-01T00:00:00+00:00",
              "epoch_ms": 1196467200000,
              "count": 0
            },
            {
              "value": "2008-01-01T00:00:00+00:00",
              "epoch_ms": 1199145600000,
              "count": 0
            },
            {
              "value": "2008-02-01T00:00:00+00:00",
              "epoch_ms": 1201824000000,
              "count": 0
            },
            {
              "value": "2008-03-01T00:00:00+00:00",
              "epoch_ms": 1204329600000,
              "count": 0
            },
            {
              "value": "2008-04-01T00:00:00+00:00",
              "epoch_ms": 1207008000000,
              "count": 0
            },
            {
              "value": "2008-05-01T00:00:00+00:00",
              "epoch_ms": 1209600000000,
              "count": 0
            },
            {
              "value": "2008-06-01T00:00:00+00:00",
              "epoch_ms": 1212278400000,
              "count": 0
            },
            {
              "value": "2008-07-01T00:00:00+00:00",
              "epoch_ms": 1214870400000,
              "count": 0
            },
            {
              "value": "2008-08-01T00:00:00+00:00",
              "epoch_ms": 1217548800000,
              "count": 0
            },
            {
              "value": "2008-09-01T00:00:00+00:00",
              "epoch_ms": 1220227200000,
              "count": 0
            },
            {
              "value": "2008-10-01T00:00:00+00:00",
              "epoch_ms": 1222819200000,
              "count": 0
            },
            {
              "value": "2008-11-01T00:00:00+00:00",
              "epoch_ms": 1225497600000,
              "count": 0
            },
            {
              "value": "2008-12-01T00:00:00+00:00",
              "epoch_ms": 1228089600000,
              "count": 0
            },
            {
              "value": "2009-01-01T00:00:00+00:00",
              "epoch_ms": 1230768000000,
              "count": 0
            },
            {
              "value": "2009-02-01T00:00:00+00:00",
              "epoch_ms": 1233446400000,
              "count": 0
            },
            {
              "value": "2009-03-01T00:00:00+00:00",
              "epoch_ms": 1235865600000,
              "count": 0
            },
            {
              "value": "2009-04-01T00:00:00+00:00",
              "epoch_ms": 1238544000000,
              "count": 0
            },
            {
              "value": "2009-05-01T00:00:00+00:00",
              "epoch_ms": 1241136000000,
              "count": 0
            },
            {
              "value": "2009-06-01T00:00:00+00:00",
              "epoch_ms": 1243814400000,
              "count": 0
            },
            {
              "value": "2009-07-01T00:00:00+00:00",
              "epoch_ms": 1246406400000,
              "count": 0
            },
            {
              "value": "2009-08-01T00:00:00+00:00",
              "epoch_ms": 1249084800000,
              "count": 0
            },
            {
              "value": "2009-09-01T00:00:00+00:00",
              "epoch_ms": 1251763200000,
              "count": 0
            },
            {
              "value": "2009-10-01T00:00:00+00:00",
              "epoch_ms": 1254355200000,
              "count": 0
            },
            {
              "value": "2009-11-01T00:00:00+00:00",
              "epoch_ms": 1257033600000,
              "count": 0
            },
            {
              "value": "2009-12-01T00:00:00+00:00",
              "epoch_ms": 1259625600000,
              "count": 0
            },
            {
              "value": "2010-01-01T00:00:00+00:00",
              "epoch_ms": 1262304000000,
              "count": 0
            },
            {
              "value": "2010-02-01T00:00:00+00:00",
              "epoch_ms": 1264982400000,
              "count": 0
            },
            {
              "value": "2010-03-01T00:00:00+00:00",
              "epoch_ms": 1267401600000,
              "count": 0
            },
            {
              "value": "2010-04-01T00:00:00+00:00",
              "epoch_ms": 1270080000000,
              "count": 0
            },
            {
              "value": "2010-05-01T00:00:00+00:00",
              "epoch_ms": 1272672000000,
              "count": 0
            },
            {
              "value": "2010-06-01T00:00:00+00:00",
              "epoch_ms": 1275350400000,
              "count": 0
            },
            {
              "value": "2010-07-01T00:00:00+00:00",
              "epoch_ms": 1277942400000,
              "count": 0
            },
            {
              "value": "2010-08-01T00:00:00+00:00",
              "epoch_ms": 1280620800000,
              "count": 0
            },
            {
              "value": "2010-09-01T00:00:00+00:00",
              "epoch_ms": 1283299200000,
              "count": 0
            },
            {
              "value": "2010-10-01T00:00:00+00:00",
              "epoch_ms": 1285891200000,
              "count": 0
            },
            {
              "value": "2010-11-01T00:00:00+00:00",
              "epoch_ms": 1288569600000,
              "count": 0
            },
            {
              "value": "2010-12-01T00:00:00+00:00",
              "epoch_ms": 1291161600000,
              "count": 0
            },
            {
              "value": "2011-01-01T00:00:00+00:00",
              "epoch_ms": 1293840000000,
              "count": 0
            },
            {
              "value": "2011-02-01T00:00:00+00:00",
              "epoch_ms": 1296518400000,
              "count": 0
            },
            {
              "value": "2011-03-01T00:00:00+00:00",
              "epoch_ms": 1298937600000,
              "count": 3
            },
            {
              "value": "2011-04-01T00:00:00+00:00",
              "epoch_ms": 1301616000000,
              "count": 0
            },
            {
              "value": "2011-05-01T00:00:00+00:00",
              "epoch_ms": 1304208000000,
              "count": 0
            },
            {
              "value": "2011-06-01T00:00:00+00:00",
              "epoch_ms": 1306886400000,
              "count": 0
            },
            {
              "value": "2011-07-01T00:00:00+00:00",
              "epoch_ms": 1309478400000,
              "count": 0
            },
            {
              "value": "2011-08-01T00:00:00+00:00",
              "epoch_ms": 1312156800000,
              "count": 0
            },
            {
              "value": "2011-09-01T00:00:00+00:00",
              "epoch_ms": 1314835200000,
              "count": 0
            },
            {
              "value": "2011-10-01T00:00:00+00:00",
              "epoch_ms": 1317427200000,
              "count": 0
            },
            {
              "value": "2011-11-01T00:00:00+00:00",
              "epoch_ms": 1320105600000,
              "count": 0
            },
            {
              "value": "2011-12-01T00:00:00+00:00",
              "epoch_ms": 1322697600000,
              "count": 0
            },
            {
              "value": "2012-01-01T00:00:00+00:00",
              "epoch_ms": 1325376000000,
              "count": 0
            },
            {
              "value": "2012-02-01T00:00:00+00:00",
              "epoch_ms": 1328054400000,
              "count": 0
            },
            {
              "value": "2012-03-01T00:00:00+00:00",
              "epoch_ms": 1330560000000,
              "count": 0
            },
            {
              "value": "2012-04-01T00:00:00+00:00",
              "epoch_ms": 1333238400000,
              "count": 0
            },
            {
              "value": "2012-05-01T00:00:00+00:00",
              "epoch_ms": 1335830400000,
              "count": 0
            },
            {
              "value": "2012-06-01T00:00:00+00:00",
              "epoch_ms": 1338508800000,
              "count": 0
            },
            {
              "value": "2012-07-01T00:00:00+00:00",
              "epoch_ms": 1341100800000,
              "count": 0
            },
            {
              "value": "2012-08-01T00:00:00+00:00",
              "epoch_ms": 1343779200000,
              "count": 0
            },
            {
              "value": "2012-09-01T00:00:00+00:00",
              "epoch_ms": 1346457600000,
              "count": 0
            },
            {
              "value": "2012-10-01T00:00:00+00:00",
              "epoch_ms": 1349049600000,
              "count": 0
            },
            {
              "value": "2012-11-01T00:00:00+00:00",
              "epoch_ms": 1351728000000,
              "count": 0
            },
            {
              "value": "2012-12-01T00:00:00+00:00",
              "epoch_ms": 1354320000000,
              "count": 0
            },
            {
              "value": "2013-01-01T00:00:00+00:00",
              "epoch_ms": 1356998400000,
              "count": 0
            },
            {
              "value": "2013-02-01T00:00:00+00:00",
              "epoch_ms": 1359676800000,
              "count": 0
            },
            {
              "value": "2013-03-01T00:00:00+00:00",
              "epoch_ms": 1362096000000,
              "count": 0
            },
            {
              "value": "2013-04-01T00:00:00+00:00",
              "epoch_ms": 1364774400000,
              "count": 0
            },
            {
              "value": "2013-05-01T00:00:00+00:00",
              "epoch_ms": 1367366400000,
              "count": 0
            },
            {
              "value": "2013-06-01T00:00:00+00:00",
              "epoch_ms": 1370044800000,
              "count": 0
            },
            {
              "value": "2013-07-01T00:00:00+00:00",
              "epoch_ms": 1372636800000,
              "count": 0
            },
            {
              "value": "2013-08-01T00:00:00+00:00",
              "epoch_ms": 1375315200000,
              "count": 0
            },
            {
              "value": "2013-09-01T00:00:00+00:00",
              "epoch_ms": 1377993600000,
              "count": 0
            },
            {
              "value": "2013-10-01T00:00:00+00:00",
              "epoch_ms": 1380585600000,
              "count": 0
            },
            {
              "value": "2013-11-01T00:00:00+00:00",
              "epoch_ms": 1383264000000,
              "count": 0
            },
            {
              "value": "2013-12-01T00:00:00+00:00",
              "epoch_ms": 1385856000000,
              "count": 0
            },
            {
              "value": "2014-01-01T00:00:00+00:00",
              "epoch_ms": 1388534400000,
              "count": 0
            },
            {
              "value": "2014-02-01T00:00:00+00:00",
              "epoch_ms": 1391212800000,
              "count": 0
            },
            {
              "value": "2014-03-01T00:00:00+00:00",
              "epoch_ms": 1393632000000,
              "count": 0
            },
            {
              "value": "2014-04-01T00:00:00+00:00",
              "epoch_ms": 1396310400000,
              "count": 0
            },
            {
              "value": "2014-05-01T00:00:00+00:00",
              "epoch_ms": 1398902400000,
              "count": 0
            },
            {
              "value": "2014-06-01T00:00:00+00:00",
              "epoch_ms": 1401580800000,
              "count": 0
            },
            {
              "value": "2014-07-01T00:00:00+00:00",
              "epoch_ms": 1404172800000,
              "count": 0
            },
            {
              "value": "2014-08-01T00:00:00+00:00",
              "epoch_ms": 1406851200000,
              "count": 0
            },
            {
              "value": "2014-09-01T00:00:00+00:00",
              "epoch_ms": 1409529600000,
              "count": 0
            },
            {
              "value": "2014-10-01T00:00:00+00:00",
              "epoch_ms": 1412121600000,
              "count": 0
            },
            {
              "value": "2014-11-01T00:00:00+00:00",
              "epoch_ms": 1414800000000,
              "count": 0
            },
            {
              "value": "2014-12-01T00:00:00+00:00",
              "epoch_ms": 1417392000000,
              "count": 0
            },
            {
              "value": "2015-01-01T00:00:00+00:00",
              "epoch_ms": 1420070400000,
              "count": 0
            },
            {
              "value": "2015-02-01T00:00:00+00:00",
              "epoch_ms": 1422748800000,
              "count": 0
            },
            {
              "value": "2015-03-01T00:00:00+00:00",
              "epoch_ms": 1425168000000,
              "count": 0
            },
            {
              "value": "2015-04-01T00:00:00+00:00",
              "epoch_ms": 1427846400000,
              "count": 0
            },
            {
              "value": "2015-05-01T00:00:00+00:00",
              "epoch_ms": 1430438400000,
              "count": 0
            },
            {
              "value": "2015-06-01T00:00:00+00:00",
              "epoch_ms": 1433116800000,
              "count": 1
            },
            {
              "value": "2015-07-01T00:00:00+00:00",
              "epoch_ms": 1435708800000,
              "count": 0
            },
            {
              "value": "2015-08-01T00:00:00+00:00",
              "epoch_ms": 1438387200000,
              "count": 0
            },
            {
              "value": "2015-09-01T00:00:00+00:00",
              "epoch_ms": 1441065600000,
              "count": 0
            },
            {
              "value": "2015-10-01T00:00:00+00:00",
              "epoch_ms": 1443657600000,
              "count": 0
            },
            {
              "value": "2015-11-01T00:00:00+00:00",
              "epoch_ms": 1446336000000,
              "count": 0
            },
            {
              "value": "2015-12-01T00:00:00+00:00",
              "epoch_ms": 1448928000000,
              "count": 0
            },
            {
              "value": "2016-01-01T00:00:00+00:00",
              "epoch_ms": 1451606400000,
              "count": 0
            },
            {
              "value": "2016-02-01T00:00:00+00:00",
              "epoch_ms": 1454284800000,
              "count": 0
            },
            {
              "value": "2016-03-01T00:00:00+00:00",
              "epoch_ms": 1456790400000,
              "count": 0
            },
            {
              "value": "2016-04-01T00:00:00+00:00",
              "epoch_ms": 1459468800000,
              "count": 0
            },
            {
              "value": "2016-05-01T00:00:00+00:00",
              "epoch_ms": 1462060800000,
              "count": 0
            },
            {
              "value": "2016-06-01T00:00:00+00:00",
              "epoch_ms": 1464739200000,
              "count": 0
            },
            {
              "value": "2016-07-01T00:00:00+00:00",
              "epoch_ms": 1467331200000,
              "count": 0
            },
            {
              "value": "2016-08-01T00:00:00+00:00",
              "epoch_ms": 1470009600000,
              "count": 0
            },
            {
              "value": "2016-09-01T00:00:00+00:00",
              "epoch_ms": 1472688000000,
              "count": 0
            },
            {
              "value": "2016-10-01T00:00:00+00:00",
              "epoch_ms": 1475280000000,
              "count": 0
            },
            {
              "value": "2016-11-01T00:00:00+00:00",
              "epoch_ms": 1477958400000,
              "count": 0
            },
            {
              "value": "2016-12-01T00:00:00+00:00",
              "epoch_ms": 1480550400000,
              "count": 0
            },
            {
              "value": "2017-01-01T00:00:00+00:00",
              "epoch_ms": 1483228800000,
              "count": 0
            },
            {
              "value": "2017-02-01T00:00:00+00:00",
              "epoch_ms": 1485907200000,
              "count": 0
            },
            {
              "value": "2017-03-01T00:00:00+00:00",
              "epoch_ms": 1488326400000,
              "count": 0
            },
            {
              "value": "2017-04-01T00:00:00+00:00",
              "epoch_ms": 1491004800000,
              "count": 0
            },
            {
              "value": "2017-05-01T00:00:00+00:00",
              "epoch_ms": 1493596800000,
              "count": 0
            },
            {
              "value": "2017-06-01T00:00:00+00:00",
              "epoch_ms": 1496275200000,
              "count": 0
            },
            {
              "value": "2017-07-01T00:00:00+00:00",
              "epoch_ms": 1498867200000,
              "count": 0
            },
            {
              "value": "2017-08-01T00:00:00+00:00",
              "epoch_ms": 1501545600000,
              "count": 1
            },
            {
              "value": "2017-09-01T00:00:00+00:00",
              "epoch_ms": 1504224000000,
              "count": 0
            },
            {
              "value": "2017-10-01T00:00:00+00:00",
              "epoch_ms": 1506816000000,
              "count": 0
            },
            {
              "value": "2017-11-01T00:00:00+00:00",
              "epoch_ms": 1509494400000,
              "count": 0
            },
            {
              "value": "2017-12-01T00:00:00+00:00",
              "epoch_ms": 1512086400000,
              "count": 0
            },
            {
              "value": "2018-01-01T00:00:00+00:00",
              "epoch_ms": 1514764800000,
              "count": 0
            },
            {
              "value": "2018-02-01T00:00:00+00:00",
              "epoch_ms": 1517443200000,
              "count": 0
            },
            {
              "value": "2018-03-01T00:00:00+00:00",
              "epoch_ms": 1519862400000,
              "count": 0
            },
            {
              "value": "2018-04-01T00:00:00+00:00",
              "epoch_ms": 1522540800000,
              "count": 0
            },
            {
              "value": "2018-05-01T00:00:00+00:00",
              "epoch_ms": 1525132800000,
              "count": 0
            },
            {
              "value": "2018-06-01T00:00:00+00:00",
              "epoch_ms": 1527811200000,
              "count": 0
            },
            {
              "value": "2018-07-01T00:00:00+00:00",
              "epoch_ms": 1530403200000,
              "count": 0
            },
            {
              "value": "2018-08-01T00:00:00+00:00",
              "epoch_ms": 1533081600000,
              "count": 0
            },
            {
              "value": "2018-09-01T00:00:00+00:00",
              "epoch_ms": 1535760000000,
              "count": 0
            },
            {
              "value": "2018-10-01T00:00:00+00:00",
              "epoch_ms": 1538352000000,
              "count": 0
            },
            {
              "value": "2018-11-01T00:00:00+00:00",
              "epoch_ms": 1541030400000,
              "count": 0
            },
            {
              "value": "2018-12-01T00:00:00+00:00",
              "epoch_ms": 1543622400000,
              "count": 0
            },
            {
              "value": "2019-01-01T00:00:00+00:00",
              "epoch_ms": 1546300800000,
              "count": 0
            },
            {
              "value": "2019-02-01T00:00:00+00:00",
              "epoch_ms": 1548979200000,
              "count": 1
            },
            {
              "value": "2019-03-01T00:00:00+00:00",
              "epoch_ms": 1551398400000,
              "count": 0
            },
            {
              "value": "2019-04-01T00:00:00+00:00",
              "epoch_ms": 1554076800000,
              "count": 0
            },
            {
              "value": "2019-05-01T00:00:00+00:00",
              "epoch_ms": 1556668800000,
              "count": 0
            },
            {
              "value": "2019-06-01T00:00:00+00:00",
              "epoch_ms": 1559347200000,
              "count": 0
            },
            {
              "value": "2019-07-01T00:00:00+00:00",
              "epoch_ms": 1561939200000,
              "count": 0
            },
            {
              "value": "2019-08-01T00:00:00+00:00",
              "epoch_ms": 1564617600000,
              "count": 0
            },
            {
              "value": "2019-09-01T00:00:00+00:00",
              "epoch_ms": 1567296000000,
              "count": 0
            },
            {
              "value": "2019-10-01T00:00:00+00:00",
              "epoch_ms": 1569888000000,
              "count": 0
            },
            {
              "value": "2019-11-01T00:00:00+00:00",
              "epoch_ms": 1572566400000,
              "count": 0
            },
            {
              "value": "2019-12-01T00:00:00+00:00",
              "epoch_ms": 1575158400000,
              "count": 1
            },
            {
              "value": "2020-01-01T00:00:00+00:00",
              "epoch_ms": 1577836800000,
              "count": 0
            },
            {
              "value": "2020-02-01T00:00:00+00:00",
              "epoch_ms": 1580515200000,
              "count": 0
            },
            {
              "value": "2020-03-01T00:00:00+00:00",
              "epoch_ms": 1583020800000,
              "count": 0
            },
            {
              "value": "2020-04-01T00:00:00+00:00",
              "epoch_ms": 1585699200000,
              "count": 0
            },
            {
              "value": "2020-05-01T00:00:00+00:00",
              "epoch_ms": 1588291200000,
              "count": 0
            },
            {
              "value": "2020-06-01T00:00:00+00:00",
              "epoch_ms": 1590969600000,
              "count": 0
            },
            {
              "value": "2020-07-01T00:00:00+00:00",
              "epoch_ms": 1593561600000,
              "count": 0
            },
            {
              "value": "2020-08-01T00:00:00+00:00",
              "epoch_ms": 1596240000000,
              "count": 0
            },
            {
              "value": "2020-09-01T00:00:00+00:00",
              "epoch_ms": 1598918400000,
              "count": 0
            },
            {
              "value": "2020-10-01T00:00:00+00:00",
              "epoch_ms": 1601510400000,
              "count": 0
            },
            {
              "value": "2020-11-01T00:00:00+00:00",
              "epoch_ms": 1604188800000,
              "count": 0
            },
            {
              "value": "2020-12-01T00:00:00+00:00",
              "epoch_ms": 1606780800000,
              "count": 0
            },
            {
              "value": "2021-01-01T00:00:00+00:00",
              "epoch_ms": 1609459200000,
              "count": 0
            },
            {
              "value": "2021-02-01T00:00:00+00:00",
              "epoch_ms": 1612137600000,
              "count": 0
            },
            {
              "value": "2021-03-01T00:00:00+00:00",
              "epoch_ms": 1614556800000,
              "count": 0
            },
            {
              "value": "2021-04-01T00:00:00+00:00",
              "epoch_ms": 1617235200000,
              "count": 0
            },
            {
              "value": "2021-05-01T00:00:00+00:00",
              "epoch_ms": 1619827200000,
              "count": 0
            },
            {
              "value": "2021-06-01T00:00:00+00:00",
              "epoch_ms": 1622505600000,
              "count": 0
            },
            {
              "value": "2021-07-01T00:00:00+00:00",
              "epoch_ms": 1625097600000,
              "count": 0
            },
            {
              "value": "2021-08-01T00:00:00+00:00",
              "epoch_ms": 1627776000000,
              "count": 1
            }],
            "missing": 0,
            "more": false
          },
          "source.name":
          {
            "type": "term",
            "data": [
            {
              "value": "Famine Early Warning System Network",
              "count": 3
            },
            {
              "value": "Food Security Cluster",
              "count": 1
            },
            {
              "value": "Food and Agriculture Organization of the United Nations",
              "count": 2
            },
            {
              "value": "Government of Afghanistan",
              "count": 6
            },
            {
              "value": "Government of the United States of America",
              "count": 1
            },
            {
              "value": "UN Office on Drugs and Crime",
              "count": 1
            },
            {
              "value": "World Food Programme",
              "count": 2
            }],
            "missing": 0,
            "more": false
          },
          "disaster.name":
          {
            "type": "term",
            "data": [
            {
              "value": "Afghanistan: Avalanches, Floods and Landslides - Feb 2015",
              "count": 1
            },
            {
              "value": "Afghanistan: Drought - 2018-2019",
              "count": 1
            },
            {
              "value": "Afghanistan: Drought - Apr 2000",
              "count": 1
            }],
            "missing": 6,
            "more": false
          },
          "date.original":
          {
            "type": "date",
            "data": [
            {
              "value": "2003-02-01T00:00:00+00:00",
              "epoch_ms": 1044057600000,
              "count": 1
            },
            {
              "value": "2003-03-01T00:00:00+00:00",
              "epoch_ms": 1046476800000,
              "count": 0
            },
            {
              "value": "2003-04-01T00:00:00+00:00",
              "epoch_ms": 1049155200000,
              "count": 0
            },
            {
              "value": "2003-05-01T00:00:00+00:00",
              "epoch_ms": 1051747200000,
              "count": 0
            },
            {
              "value": "2003-06-01T00:00:00+00:00",
              "epoch_ms": 1054425600000,
              "count": 0
            },
            {
              "value": "2003-07-01T00:00:00+00:00",
              "epoch_ms": 1057017600000,
              "count": 0
            },
            {
              "value": "2003-08-01T00:00:00+00:00",
              "epoch_ms": 1059696000000,
              "count": 0
            },
            {
              "value": "2003-09-01T00:00:00+00:00",
              "epoch_ms": 1062374400000,
              "count": 0
            },
            {
              "value": "2003-10-01T00:00:00+00:00",
              "epoch_ms": 1064966400000,
              "count": 0
            },
            {
              "value": "2003-11-01T00:00:00+00:00",
              "epoch_ms": 1067644800000,
              "count": 0
            },
            {
              "value": "2003-12-01T00:00:00+00:00",
              "epoch_ms": 1070236800000,
              "count": 0
            },
            {
              "value": "2004-01-01T00:00:00+00:00",
              "epoch_ms": 1072915200000,
              "count": 0
            },
            {
              "value": "2004-02-01T00:00:00+00:00",
              "epoch_ms": 1075593600000,
              "count": 0
            },
            {
              "value": "2004-03-01T00:00:00+00:00",
              "epoch_ms": 1078099200000,
              "count": 0
            },
            {
              "value": "2004-04-01T00:00:00+00:00",
              "epoch_ms": 1080777600000,
              "count": 0
            },
            {
              "value": "2004-05-01T00:00:00+00:00",
              "epoch_ms": 1083369600000,
              "count": 0
            },
            {
              "value": "2004-06-01T00:00:00+00:00",
              "epoch_ms": 1086048000000,
              "count": 0
            },
            {
              "value": "2004-07-01T00:00:00+00:00",
              "epoch_ms": 1088640000000,
              "count": 0
            },
            {
              "value": "2004-08-01T00:00:00+00:00",
              "epoch_ms": 1091318400000,
              "count": 0
            },
            {
              "value": "2004-09-01T00:00:00+00:00",
              "epoch_ms": 1093996800000,
              "count": 0
            },
            {
              "value": "2004-10-01T00:00:00+00:00",
              "epoch_ms": 1096588800000,
              "count": 0
            },
            {
              "value": "2004-11-01T00:00:00+00:00",
              "epoch_ms": 1099267200000,
              "count": 0
            },
            {
              "value": "2004-12-01T00:00:00+00:00",
              "epoch_ms": 1101859200000,
              "count": 0
            },
            {
              "value": "2005-01-01T00:00:00+00:00",
              "epoch_ms": 1104537600000,
              "count": 0
            },
            {
              "value": "2005-02-01T00:00:00+00:00",
              "epoch_ms": 1107216000000,
              "count": 0
            },
            {
              "value": "2005-03-01T00:00:00+00:00",
              "epoch_ms": 1109635200000,
              "count": 0
            },
            {
              "value": "2005-04-01T00:00:00+00:00",
              "epoch_ms": 1112313600000,
              "count": 0
            },
            {
              "value": "2005-05-01T00:00:00+00:00",
              "epoch_ms": 1114905600000,
              "count": 0
            },
            {
              "value": "2005-06-01T00:00:00+00:00",
              "epoch_ms": 1117584000000,
              "count": 0
            },
            {
              "value": "2005-07-01T00:00:00+00:00",
              "epoch_ms": 1120176000000,
              "count": 0
            },
            {
              "value": "2005-08-01T00:00:00+00:00",
              "epoch_ms": 1122854400000,
              "count": 0
            },
            {
              "value": "2005-09-01T00:00:00+00:00",
              "epoch_ms": 1125532800000,
              "count": 0
            },
            {
              "value": "2005-10-01T00:00:00+00:00",
              "epoch_ms": 1128124800000,
              "count": 0
            },
            {
              "value": "2005-11-01T00:00:00+00:00",
              "epoch_ms": 1130803200000,
              "count": 0
            },
            {
              "value": "2005-12-01T00:00:00+00:00",
              "epoch_ms": 1133395200000,
              "count": 1
            },
            {
              "value": "2006-01-01T00:00:00+00:00",
              "epoch_ms": 1136073600000,
              "count": 0
            },
            {
              "value": "2006-02-01T00:00:00+00:00",
              "epoch_ms": 1138752000000,
              "count": 0
            },
            {
              "value": "2006-03-01T00:00:00+00:00",
              "epoch_ms": 1141171200000,
              "count": 0
            },
            {
              "value": "2006-04-01T00:00:00+00:00",
              "epoch_ms": 1143849600000,
              "count": 0
            },
            {
              "value": "2006-05-01T00:00:00+00:00",
              "epoch_ms": 1146441600000,
              "count": 0
            },
            {
              "value": "2006-06-01T00:00:00+00:00",
              "epoch_ms": 1149120000000,
              "count": 0
            },
            {
              "value": "2006-07-01T00:00:00+00:00",
              "epoch_ms": 1151712000000,
              "count": 0
            },
            {
              "value": "2006-08-01T00:00:00+00:00",
              "epoch_ms": 1154390400000,
              "count": 0
            },
            {
              "value": "2006-09-01T00:00:00+00:00",
              "epoch_ms": 1157068800000,
              "count": 0
            },
            {
              "value": "2006-10-01T00:00:00+00:00",
              "epoch_ms": 1159660800000,
              "count": 0
            },
            {
              "value": "2006-11-01T00:00:00+00:00",
              "epoch_ms": 1162339200000,
              "count": 0
            },
            {
              "value": "2006-12-01T00:00:00+00:00",
              "epoch_ms": 1164931200000,
              "count": 0
            },
            {
              "value": "2007-01-01T00:00:00+00:00",
              "epoch_ms": 1167609600000,
              "count": 0
            },
            {
              "value": "2007-02-01T00:00:00+00:00",
              "epoch_ms": 1170288000000,
              "count": 0
            },
            {
              "value": "2007-03-01T00:00:00+00:00",
              "epoch_ms": 1172707200000,
              "count": 0
            },
            {
              "value": "2007-04-01T00:00:00+00:00",
              "epoch_ms": 1175385600000,
              "count": 0
            },
            {
              "value": "2007-05-01T00:00:00+00:00",
              "epoch_ms": 1177977600000,
              "count": 0
            },
            {
              "value": "2007-06-01T00:00:00+00:00",
              "epoch_ms": 1180656000000,
              "count": 0
            },
            {
              "value": "2007-07-01T00:00:00+00:00",
              "epoch_ms": 1183248000000,
              "count": 0
            },
            {
              "value": "2007-08-01T00:00:00+00:00",
              "epoch_ms": 1185926400000,
              "count": 0
            },
            {
              "value": "2007-09-01T00:00:00+00:00",
              "epoch_ms": 1188604800000,
              "count": 0
            },
            {
              "value": "2007-10-01T00:00:00+00:00",
              "epoch_ms": 1191196800000,
              "count": 1
            },
            {
              "value": "2007-11-01T00:00:00+00:00",
              "epoch_ms": 1193875200000,
              "count": 0
            },
            {
              "value": "2007-12-01T00:00:00+00:00",
              "epoch_ms": 1196467200000,
              "count": 0
            },
            {
              "value": "2008-01-01T00:00:00+00:00",
              "epoch_ms": 1199145600000,
              "count": 0
            },
            {
              "value": "2008-02-01T00:00:00+00:00",
              "epoch_ms": 1201824000000,
              "count": 0
            },
            {
              "value": "2008-03-01T00:00:00+00:00",
              "epoch_ms": 1204329600000,
              "count": 0
            },
            {
              "value": "2008-04-01T00:00:00+00:00",
              "epoch_ms": 1207008000000,
              "count": 0
            },
            {
              "value": "2008-05-01T00:00:00+00:00",
              "epoch_ms": 1209600000000,
              "count": 0
            },
            {
              "value": "2008-06-01T00:00:00+00:00",
              "epoch_ms": 1212278400000,
              "count": 0
            },
            {
              "value": "2008-07-01T00:00:00+00:00",
              "epoch_ms": 1214870400000,
              "count": 0
            },
            {
              "value": "2008-08-01T00:00:00+00:00",
              "epoch_ms": 1217548800000,
              "count": 0
            },
            {
              "value": "2008-09-01T00:00:00+00:00",
              "epoch_ms": 1220227200000,
              "count": 0
            },
            {
              "value": "2008-10-01T00:00:00+00:00",
              "epoch_ms": 1222819200000,
              "count": 0
            },
            {
              "value": "2008-11-01T00:00:00+00:00",
              "epoch_ms": 1225497600000,
              "count": 0
            },
            {
              "value": "2008-12-01T00:00:00+00:00",
              "epoch_ms": 1228089600000,
              "count": 0
            },
            {
              "value": "2009-01-01T00:00:00+00:00",
              "epoch_ms": 1230768000000,
              "count": 1
            },
            {
              "value": "2009-02-01T00:00:00+00:00",
              "epoch_ms": 1233446400000,
              "count": 0
            },
            {
              "value": "2009-03-01T00:00:00+00:00",
              "epoch_ms": 1235865600000,
              "count": 0
            },
            {
              "value": "2009-04-01T00:00:00+00:00",
              "epoch_ms": 1238544000000,
              "count": 0
            },
            {
              "value": "2009-05-01T00:00:00+00:00",
              "epoch_ms": 1241136000000,
              "count": 0
            },
            {
              "value": "2009-06-01T00:00:00+00:00",
              "epoch_ms": 1243814400000,
              "count": 0
            },
            {
              "value": "2009-07-01T00:00:00+00:00",
              "epoch_ms": 1246406400000,
              "count": 0
            },
            {
              "value": "2009-08-01T00:00:00+00:00",
              "epoch_ms": 1249084800000,
              "count": 0
            },
            {
              "value": "2009-09-01T00:00:00+00:00",
              "epoch_ms": 1251763200000,
              "count": 0
            },
            {
              "value": "2009-10-01T00:00:00+00:00",
              "epoch_ms": 1254355200000,
              "count": 1
            },
            {
              "value": "2009-11-01T00:00:00+00:00",
              "epoch_ms": 1257033600000,
              "count": 0
            },
            {
              "value": "2009-12-01T00:00:00+00:00",
              "epoch_ms": 1259625600000,
              "count": 0
            },
            {
              "value": "2010-01-01T00:00:00+00:00",
              "epoch_ms": 1262304000000,
              "count": 0
            },
            {
              "value": "2010-02-01T00:00:00+00:00",
              "epoch_ms": 1264982400000,
              "count": 0
            },
            {
              "value": "2010-03-01T00:00:00+00:00",
              "epoch_ms": 1267401600000,
              "count": 0
            },
            {
              "value": "2010-04-01T00:00:00+00:00",
              "epoch_ms": 1270080000000,
              "count": 0
            },
            {
              "value": "2010-05-01T00:00:00+00:00",
              "epoch_ms": 1272672000000,
              "count": 0
            },
            {
              "value": "2010-06-01T00:00:00+00:00",
              "epoch_ms": 1275350400000,
              "count": 0
            },
            {
              "value": "2010-07-01T00:00:00+00:00",
              "epoch_ms": 1277942400000,
              "count": 0
            },
            {
              "value": "2010-08-01T00:00:00+00:00",
              "epoch_ms": 1280620800000,
              "count": 0
            },
            {
              "value": "2010-09-01T00:00:00+00:00",
              "epoch_ms": 1283299200000,
              "count": 0
            },
            {
              "value": "2010-10-01T00:00:00+00:00",
              "epoch_ms": 1285891200000,
              "count": 0
            },
            {
              "value": "2010-11-01T00:00:00+00:00",
              "epoch_ms": 1288569600000,
              "count": 0
            },
            {
              "value": "2010-12-01T00:00:00+00:00",
              "epoch_ms": 1291161600000,
              "count": 0
            },
            {
              "value": "2011-01-01T00:00:00+00:00",
              "epoch_ms": 1293840000000,
              "count": 0
            },
            {
              "value": "2011-02-01T00:00:00+00:00",
              "epoch_ms": 1296518400000,
              "count": 0
            },
            {
              "value": "2011-03-01T00:00:00+00:00",
              "epoch_ms": 1298937600000,
              "count": 0
            },
            {
              "value": "2011-04-01T00:00:00+00:00",
              "epoch_ms": 1301616000000,
              "count": 0
            },
            {
              "value": "2011-05-01T00:00:00+00:00",
              "epoch_ms": 1304208000000,
              "count": 0
            },
            {
              "value": "2011-06-01T00:00:00+00:00",
              "epoch_ms": 1306886400000,
              "count": 1
            },
            {
              "value": "2011-07-01T00:00:00+00:00",
              "epoch_ms": 1309478400000,
              "count": 0
            },
            {
              "value": "2011-08-01T00:00:00+00:00",
              "epoch_ms": 1312156800000,
              "count": 0
            },
            {
              "value": "2011-09-01T00:00:00+00:00",
              "epoch_ms": 1314835200000,
              "count": 0
            },
            {
              "value": "2011-10-01T00:00:00+00:00",
              "epoch_ms": 1317427200000,
              "count": 0
            },
            {
              "value": "2011-11-01T00:00:00+00:00",
              "epoch_ms": 1320105600000,
              "count": 0
            },
            {
              "value": "2011-12-01T00:00:00+00:00",
              "epoch_ms": 1322697600000,
              "count": 0
            },
            {
              "value": "2012-01-01T00:00:00+00:00",
              "epoch_ms": 1325376000000,
              "count": 0
            },
            {
              "value": "2012-02-01T00:00:00+00:00",
              "epoch_ms": 1328054400000,
              "count": 0
            },
            {
              "value": "2012-03-01T00:00:00+00:00",
              "epoch_ms": 1330560000000,
              "count": 0
            },
            {
              "value": "2012-04-01T00:00:00+00:00",
              "epoch_ms": 1333238400000,
              "count": 0
            },
            {
              "value": "2012-05-01T00:00:00+00:00",
              "epoch_ms": 1335830400000,
              "count": 0
            },
            {
              "value": "2012-06-01T00:00:00+00:00",
              "epoch_ms": 1338508800000,
              "count": 0
            },
            {
              "value": "2012-07-01T00:00:00+00:00",
              "epoch_ms": 1341100800000,
              "count": 0
            },
            {
              "value": "2012-08-01T00:00:00+00:00",
              "epoch_ms": 1343779200000,
              "count": 0
            },
            {
              "value": "2012-09-01T00:00:00+00:00",
              "epoch_ms": 1346457600000,
              "count": 0
            },
            {
              "value": "2012-10-01T00:00:00+00:00",
              "epoch_ms": 1349049600000,
              "count": 0
            },
            {
              "value": "2012-11-01T00:00:00+00:00",
              "epoch_ms": 1351728000000,
              "count": 0
            },
            {
              "value": "2012-12-01T00:00:00+00:00",
              "epoch_ms": 1354320000000,
              "count": 0
            },
            {
              "value": "2013-01-01T00:00:00+00:00",
              "epoch_ms": 1356998400000,
              "count": 0
            },
            {
              "value": "2013-02-01T00:00:00+00:00",
              "epoch_ms": 1359676800000,
              "count": 0
            },
            {
              "value": "2013-03-01T00:00:00+00:00",
              "epoch_ms": 1362096000000,
              "count": 0
            },
            {
              "value": "2013-04-01T00:00:00+00:00",
              "epoch_ms": 1364774400000,
              "count": 0
            },
            {
              "value": "2013-05-01T00:00:00+00:00",
              "epoch_ms": 1367366400000,
              "count": 0
            },
            {
              "value": "2013-06-01T00:00:00+00:00",
              "epoch_ms": 1370044800000,
              "count": 0
            },
            {
              "value": "2013-07-01T00:00:00+00:00",
              "epoch_ms": 1372636800000,
              "count": 0
            },
            {
              "value": "2013-08-01T00:00:00+00:00",
              "epoch_ms": 1375315200000,
              "count": 0
            },
            {
              "value": "2013-09-01T00:00:00+00:00",
              "epoch_ms": 1377993600000,
              "count": 0
            },
            {
              "value": "2013-10-01T00:00:00+00:00",
              "epoch_ms": 1380585600000,
              "count": 0
            },
            {
              "value": "2013-11-01T00:00:00+00:00",
              "epoch_ms": 1383264000000,
              "count": 0
            },
            {
              "value": "2013-12-01T00:00:00+00:00",
              "epoch_ms": 1385856000000,
              "count": 0
            },
            {
              "value": "2014-01-01T00:00:00+00:00",
              "epoch_ms": 1388534400000,
              "count": 0
            },
            {
              "value": "2014-02-01T00:00:00+00:00",
              "epoch_ms": 1391212800000,
              "count": 0
            },
            {
              "value": "2014-03-01T00:00:00+00:00",
              "epoch_ms": 1393632000000,
              "count": 0
            },
            {
              "value": "2014-04-01T00:00:00+00:00",
              "epoch_ms": 1396310400000,
              "count": 0
            },
            {
              "value": "2014-05-01T00:00:00+00:00",
              "epoch_ms": 1398902400000,
              "count": 0
            },
            {
              "value": "2014-06-01T00:00:00+00:00",
              "epoch_ms": 1401580800000,
              "count": 0
            },
            {
              "value": "2014-07-01T00:00:00+00:00",
              "epoch_ms": 1404172800000,
              "count": 0
            },
            {
              "value": "2014-08-01T00:00:00+00:00",
              "epoch_ms": 1406851200000,
              "count": 0
            },
            {
              "value": "2014-09-01T00:00:00+00:00",
              "epoch_ms": 1409529600000,
              "count": 0
            },
            {
              "value": "2014-10-01T00:00:00+00:00",
              "epoch_ms": 1412121600000,
              "count": 0
            },
            {
              "value": "2014-11-01T00:00:00+00:00",
              "epoch_ms": 1414800000000,
              "count": 0
            },
            {
              "value": "2014-12-01T00:00:00+00:00",
              "epoch_ms": 1417392000000,
              "count": 0
            },
            {
              "value": "2015-01-01T00:00:00+00:00",
              "epoch_ms": 1420070400000,
              "count": 0
            },
            {
              "value": "2015-02-01T00:00:00+00:00",
              "epoch_ms": 1422748800000,
              "count": 0
            },
            {
              "value": "2015-03-01T00:00:00+00:00",
              "epoch_ms": 1425168000000,
              "count": 0
            },
            {
              "value": "2015-04-01T00:00:00+00:00",
              "epoch_ms": 1427846400000,
              "count": 0
            },
            {
              "value": "2015-05-01T00:00:00+00:00",
              "epoch_ms": 1430438400000,
              "count": 0
            },
            {
              "value": "2015-06-01T00:00:00+00:00",
              "epoch_ms": 1433116800000,
              "count": 1
            },
            {
              "value": "2015-07-01T00:00:00+00:00",
              "epoch_ms": 1435708800000,
              "count": 0
            },
            {
              "value": "2015-08-01T00:00:00+00:00",
              "epoch_ms": 1438387200000,
              "count": 0
            },
            {
              "value": "2015-09-01T00:00:00+00:00",
              "epoch_ms": 1441065600000,
              "count": 0
            },
            {
              "value": "2015-10-01T00:00:00+00:00",
              "epoch_ms": 1443657600000,
              "count": 0
            },
            {
              "value": "2015-11-01T00:00:00+00:00",
              "epoch_ms": 1446336000000,
              "count": 0
            },
            {
              "value": "2015-12-01T00:00:00+00:00",
              "epoch_ms": 1448928000000,
              "count": 0
            },
            {
              "value": "2016-01-01T00:00:00+00:00",
              "epoch_ms": 1451606400000,
              "count": 0
            },
            {
              "value": "2016-02-01T00:00:00+00:00",
              "epoch_ms": 1454284800000,
              "count": 0
            },
            {
              "value": "2016-03-01T00:00:00+00:00",
              "epoch_ms": 1456790400000,
              "count": 0
            },
            {
              "value": "2016-04-01T00:00:00+00:00",
              "epoch_ms": 1459468800000,
              "count": 0
            },
            {
              "value": "2016-05-01T00:00:00+00:00",
              "epoch_ms": 1462060800000,
              "count": 0
            },
            {
              "value": "2016-06-01T00:00:00+00:00",
              "epoch_ms": 1464739200000,
              "count": 0
            },
            {
              "value": "2016-07-01T00:00:00+00:00",
              "epoch_ms": 1467331200000,
              "count": 0
            },
            {
              "value": "2016-08-01T00:00:00+00:00",
              "epoch_ms": 1470009600000,
              "count": 0
            },
            {
              "value": "2016-09-01T00:00:00+00:00",
              "epoch_ms": 1472688000000,
              "count": 0
            },
            {
              "value": "2016-10-01T00:00:00+00:00",
              "epoch_ms": 1475280000000,
              "count": 0
            },
            {
              "value": "2016-11-01T00:00:00+00:00",
              "epoch_ms": 1477958400000,
              "count": 0
            },
            {
              "value": "2016-12-01T00:00:00+00:00",
              "epoch_ms": 1480550400000,
              "count": 0
            },
            {
              "value": "2017-01-01T00:00:00+00:00",
              "epoch_ms": 1483228800000,
              "count": 0
            },
            {
              "value": "2017-02-01T00:00:00+00:00",
              "epoch_ms": 1485907200000,
              "count": 0
            },
            {
              "value": "2017-03-01T00:00:00+00:00",
              "epoch_ms": 1488326400000,
              "count": 0
            },
            {
              "value": "2017-04-01T00:00:00+00:00",
              "epoch_ms": 1491004800000,
              "count": 0
            },
            {
              "value": "2017-05-01T00:00:00+00:00",
              "epoch_ms": 1493596800000,
              "count": 1
            },
            {
              "value": "2017-06-01T00:00:00+00:00",
              "epoch_ms": 1496275200000,
              "count": 0
            },
            {
              "value": "2017-07-01T00:00:00+00:00",
              "epoch_ms": 1498867200000,
              "count": 0
            },
            {
              "value": "2017-08-01T00:00:00+00:00",
              "epoch_ms": 1501545600000,
              "count": 0
            },
            {
              "value": "2017-09-01T00:00:00+00:00",
              "epoch_ms": 1504224000000,
              "count": 0
            },
            {
              "value": "2017-10-01T00:00:00+00:00",
              "epoch_ms": 1506816000000,
              "count": 0
            },
            {
              "value": "2017-11-01T00:00:00+00:00",
              "epoch_ms": 1509494400000,
              "count": 0
            },
            {
              "value": "2017-12-01T00:00:00+00:00",
              "epoch_ms": 1512086400000,
              "count": 0
            },
            {
              "value": "2018-01-01T00:00:00+00:00",
              "epoch_ms": 1514764800000,
              "count": 0
            },
            {
              "value": "2018-02-01T00:00:00+00:00",
              "epoch_ms": 1517443200000,
              "count": 0
            },
            {
              "value": "2018-03-01T00:00:00+00:00",
              "epoch_ms": 1519862400000,
              "count": 0
            },
            {
              "value": "2018-04-01T00:00:00+00:00",
              "epoch_ms": 1522540800000,
              "count": 0
            },
            {
              "value": "2018-05-01T00:00:00+00:00",
              "epoch_ms": 1525132800000,
              "count": 0
            },
            {
              "value": "2018-06-01T00:00:00+00:00",
              "epoch_ms": 1527811200000,
              "count": 0
            },
            {
              "value": "2018-07-01T00:00:00+00:00",
              "epoch_ms": 1530403200000,
              "count": 0
            },
            {
              "value": "2018-08-01T00:00:00+00:00",
              "epoch_ms": 1533081600000,
              "count": 0
            },
            {
              "value": "2018-09-01T00:00:00+00:00",
              "epoch_ms": 1535760000000,
              "count": 1
            }],
            "missing": 0,
            "more": false
          },
          "language.name":
          {
            "type": "term",
            "data": [
            {
              "value": "English",
              "count": 9
            }],
            "missing": 0,
            "more": false
          },
          "theme.name":
          {
            "type": "term",
            "data": [
            {
              "value": "Agriculture",
              "count": 9
            },
            {
              "value": "Disaster Management",
              "count": 1
            },
            {
              "value": "Education",
              "count": 3
            },
            {
              "value": "Food and Nutrition",
              "count": 5
            },
            {
              "value": "Health",
              "count": 2
            },
            {
              "value": "Protection and Human Rights",
              "count": 2
            },
            {
              "value": "Recovery and Reconstruction",
              "count": 2
            },
            {
              "value": "Shelter and Non-Food Items",
              "count": 4
            },
            {
              "value": "Water Sanitation Hygiene",
              "count": 3
            }],
            "missing": 0,
            "more": false
          },
          "disaster_type":
          {
            "type": "term",
            "data": [
            {
              "value": "Cold Wave",
              "count": 1
            },
            {
              "value": "Drought",
              "count": 2
            },
            {
              "value": "Flash Flood",
              "count": 1
            },
            {
              "value": "Flood",
              "count": 1
            },
            {
              "value": "Land Slide",
              "count": 1
            },
            {
              "value": "Snow Avalanche",
              "count": 1
            }],
            "missing": 6,
            "more": false
          },
          "format.name":
          {
            "type": "term",
            "data": [
            {
              "value": "Assessment",
              "count": 9
            }],
            "missing": 0,
            "more": false
          }
        }
      }
    }';
  }

  private function getTestRw2() {
    return '{
      "time": 12,
      "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=1&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=url_alias&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=https:\/\/reliefweb.int\/report\/afghanistan\/afghanistan-food-security-alert-june-20-2011",
      "links":
      {
        "self":
        {
          "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=1&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=url_alias&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=https%3A%2F%2Freliefweb.int%2Freport%2Fafghanistan%2Fafghanistan-food-security-alert-june-20-2011"
        },
        "next":
        {
          "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=1&limit=1&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=url_alias&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=https%3A%2F%2Freliefweb.int%2Freport%2Fafghanistan%2Fafghanistan-food-security-alert-june-20-2011"
        },
        "prev":
        {
          "href": "' . static::API_ENDPOINT . '?appname=response-test-suite&offset=0&limit=1&preset=latest&fields%5Binclude%5D%5B0%5D=id&fields%5Binclude%5D%5B1%5D=disaster_type.name&fields%5Binclude%5D%5B2%5D=url&fields%5Binclude%5D%5B3%5D=title&fields%5Binclude%5D%5B4%5D=body-html&fields%5Binclude%5D%5B5%5D=date.changed&fields%5Binclude%5D%5B6%5D=date.created&fields%5Binclude%5D%5B7%5D=date.original&fields%5Binclude%5D%5B8%5D=source.shortname&fields%5Binclude%5D%5B9%5D=country.name&fields%5Binclude%5D%5B10%5D=primary_country.name&fields%5Binclude%5D%5B11%5D=file.id&fields%5Binclude%5D%5B12%5D=file.url&fields%5Binclude%5D%5B13%5D=file.preview.url-thumb&fields%5Binclude%5D%5B14%5D=file.description&fields%5Binclude%5D%5B15%5D=file.filename&fields%5Binclude%5D%5B16%5D=format.name&filter%5Boperator%5D=AND&filter%5Bconditions%5D%5B0%5D%5Bfield%5D=url_alias&filter%5Bconditions%5D%5B0%5D%5Bvalue%5D=https%3A%2F%2Freliefweb.int%2Freport%2Fafghanistan%2Fafghanistan-food-security-alert-june-20-2011"
        }
      },
      "took": 4,
      "totalCount": 1,
      "count": 1,
      "data": [
      {
        "id": "421570",
        "score": 1,
        "fields":
        {
          "date":
          {
            "changed": "2017-08-06T08:28:54+00:00",
            "created": "2011-06-22T01:03:44+00:00",
            "original": "2011-06-20T00:00:00+00:00"
          },
          "country": [
          {
            "name": "Afghanistan"
          }],
          "file": [
          {
            "filename": "Full_Report.pdf",
            "description": "",
            "id": "1934719",
            "url": "https:\/\/reliefweb.int\/attachments\/f2c5c6a0-f252-38e2-a195-9a95bb0ea990\/Full_Report.pdf"
          }],
          "primary_country":
          {
            "name": "Afghanistan"
          },
          "format": [
          {
            "name": "Assessment"
          }],
          "source": [
          {
            "shortname": "FEWS NET"
          }],
          "id": 421570,
          "title": "Afghanistan Food Security Alert: June 20, 2011",
          "url": "https:\/\/reliefweb.int\/node\/421570",
          "body-html": "<p>Poor rainfed wheat harvest in northern Afghanistan raises need for assistance<\/p>\n<p>Satellite derived rainfall estimates indicate that most of Afghanistan had an untimely and inadequate rain and snow season this year. As a result, there will be heavy losses in rainfed wheat crops, underperforming irrigated wheat crops, poor pasture conditions, and low income earning opportunities in northern Afghanistan and the central highlands this year.<br \/>\nUltimately, households in northern Afghanistan who are dependent on rainfed crops as a primary food source or on-farm agricultural labor as a major income source will be unable to afford essential non-food expenditures during Fall 2011 that are needed to protect livelihoods (e.g., input purchases). Without additional assistance these households will likely face food deficits beginning in the fall and intensifying until the start of the 2012 harvest in May.<\/p>\n"
        },
        "href": "' . static::API_ENDPOINT . '\/reports\/421570"
      }]
    }';
  }
}
