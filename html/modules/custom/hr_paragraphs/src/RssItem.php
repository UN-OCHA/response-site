<?php

namespace Drupal\hr_paragraphs;

/**
 * RSS Item.
 */
class RssItem {

  /**
   * Title of item.
   *
   * @var string
   */
  public $title;

  /**
   * Link to item.
   *
   * @var string
   */
  public $link;

  /**
   * Description of item.
   *
   * @var string
   */
  public $description;

  /**
   * Timestamp of item.
   *
   * @var int|false
   */
  public $date;

  /**
   * Constructor.
   */
  public function __construct(string $title, string $link, string $description, int $date) {
    $this->title = $title;
    $this->link = $link;
    $this->description = $description;
    $this->date = $date;
  }

}
