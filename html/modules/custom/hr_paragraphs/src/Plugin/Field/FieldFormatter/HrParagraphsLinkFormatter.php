<?php

namespace Drupal\hr_paragraphs\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "hr_paragraphs_link",
 *   label = @Translation("HR Paragraphs Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class HrParagraphsLinkFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'local_domains' => '',
      'force_relative' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['local_domains'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of domains considered local'),
      '#default_value' => $this->getSetting('local_domains'),
      '#description' => $this->t('Domain names on separate lines.'),
    ];
    $elements['force_relative'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Force relative URLs'),
      '#default_value' => $this->getSetting('force_relative'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $settings = $this->getSettings();

    if (!empty($settings['force_relative'])) {
      $summary[] = $this->t('Local domains will be made relative');
    }

    return $summary;
  }

  /**
   * Builds the \Drupal\Core\Url object for a link field item.
   *
   * @param \Drupal\link\LinkItemInterface $item
   *   The link field item being rendered.
   *
   * @return \Drupal\Core\Url
   *   A Url object.
   */
  protected function buildUrl(LinkItemInterface $item) {
    try {
      $url = $item->getUrl();
    }
    catch (\InvalidArgumentException $e) {
      // @todo Add logging here in https://www.drupal.org/project/drupal/issues/3348020
      $url = Url::fromRoute('<none>');
    }

    $settings = $this->getSettings();
    $options = $item->options;
    $options += $url->getOptions();

    // Convert URL to relative if needed.
    if (isset($settings['force_relative']) && !empty($settings['force_relative'])) {
      $local_domains = $settings['local_domains'];
      if (!empty($local_domains)) {
        if (!$url->isRouted()) {
          $domain = parse_url($url->getUri(), PHP_URL_HOST);
          $local_domains = explode("\r\n", $local_domains);

          if (in_array($domain, $local_domains)) {
            $uri = substr($url->getUri(), strpos($url->getUri(), $domain) + strlen($domain));
            $url = $url->fromUserInput($uri);
            $options['external'] = FALSE;
            $url->setAbsolute(FALSE);
          }
        }
      }
    }

    // Add optional 'rel' attribute to link options.
    if (!empty($settings['rel'])) {
      $options['attributes']['rel'] = $settings['rel'];
    }
    // Add optional 'target' attribute to link options.
    if (!empty($settings['target'])) {
      $options['attributes']['target'] = $settings['target'];
    }
    $url->setOptions($options);

    return $url;
  }

}
