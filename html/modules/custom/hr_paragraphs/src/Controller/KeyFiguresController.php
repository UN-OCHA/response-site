<?php

namespace Drupal\hr_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page controller for Key Figures.
 */
class KeyFiguresController extends ControllerBase {

  /**
   * The HTTP client to fetch the files with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Fetch Key Figures.
   *
   * @param string $iso3
   *   ISO3 of the country we want Key Figures for.
   *
   * @return array<string, mixed>
   *   Raw results.
   */
  public function getKeyFigures(string $iso3) : array {
    $endpoint = 'https://raw.githubusercontent.com/reliefweb/crisis-app-data/v2/edition/world/countries/';

    // Construct full URL.
    $fullUrl = $endpoint . $iso3 . '/figures.json';

    try {
      $response = $this->httpClient->request(
        'GET',
        $fullUrl,
      );
    }
    catch (RequestException $exception) {
      if ($exception->getCode() === 404) {
        throw new NotFoundHttpException();
      }
      else {
        throw $exception;
      }
    }

    $body = $response->getBody() . '';
    $results = json_decode($body, TRUE);

    return $results;
  }

  /**
   * Build reliefweb objects.
   *
   * @param array<string, mixed> $results
   *   Raw results from API.
   * @param int $max
   *   Number of items to return.
   *
   * @return array<string, mixed>
   *   Results.
   */
  public function buildKeyFigures(array $results, int $max) : array {
    $data = [];

    foreach ($results as $row) {
      $title = $row['name'];
      // Basic info for Key Figure.
      $data[$title] = [
        'title' => $title,
        'date' => $row['date'],
        'url' => $row['url'],
        'value' => $row['value'],
        'source' => $row['source'],
      ];

      // Sparklines can also be constructed using the `values` array.
      // @see https://github.com/UN-OCHA/rwint9-site/blob/develop/html/modules/custom/reliefweb_key_figures/src/Services/KeyFiguresClient.php#L249-L254
    }

    // Limit to the number specified by Editor, then return.
    return array_slice($data, 0, $max);
  }

}
