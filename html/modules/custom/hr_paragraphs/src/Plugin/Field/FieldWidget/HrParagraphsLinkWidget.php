<?php

namespace Drupal\hr_paragraphs\Plugin\Field\FieldWidget;

use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "hr_paragraphs_link_default",
 *   label = @Translation("HR Paragraphs Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class HrParagraphsLinkWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  protected static function getUserEnteredStringAsUri($string) {
    $uri = parent::getUserEnteredStringAsUri($string);

    global $base_url;
    if (parse_url($base_url, PHP_URL_HOST) == parse_url($uri, PHP_URL_HOST)) {
      $uri = substr($uri, strpos($uri, $base_url) + strlen($base_url));
    }

    return $uri;
  }

}
