<?php

namespace Drupal\hr_paragraphs\Plugin\Filter;

use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to display any HTML as plain text.
 *
 * @Filter(
 *   id = "filter_markdown",
 *   title = @Translation("Convert a markdown text to HTML"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = -20
 * )
 */
class Markdown extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    $help = '<h4>Markdown reference - obsolete</h4>';

    $tips = [
      '#type' => 'container',
    ];

    $tips[] = [
      '#markup' => Markup::create($help),
    ];

    // Documentation link.
    $url = Url::fromUri('https://commonmark.org/help', [
      'attributes' => ['target' => '_blank', 'rel' => 'noopener noreferrer'],
    ]);
    $tips[] = [
      '#markup' => $this->t('For complete details on the Markdown syntax, see the @link', [
        '@link' => Link::fromTextAndUrl($this->t('Markdown documentation'), $url)->toString(),
      ]),
    ];

    return \Drupal::service('renderer')->render($tips);
  }

}
