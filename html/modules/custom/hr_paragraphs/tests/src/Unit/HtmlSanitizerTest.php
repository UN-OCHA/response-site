<?php

namespace Drupal\Tests\hr_paragraphs\Unit;

// @ignore-file
use Drupal\hr_paragraphs\Helpers\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests html sanitizer.
 */
#[CoversClass(HtmlSanitizer::class)]
class HtmlSanitizerTest extends UnitTestCase {

  /**
   * Tests cleaning HTML.
   */
  #[DataProvider('htmlProvider')]
  public function testCleanHtml($text, $expected) {
    // Call the cleaning function from hr_paragraphs.module.
    $text = HtmlSanitizer::sanitize($text);
    $this->assertEquals($expected, $text);
  }

  /**
   * Provides test cases for HTML cleaning.
   */
  public static function htmlProvider() {
    return [
          [
            '<p></p>',
            '',
          ],
          [
            '<div><span>   </span></div>',
            '',
          ],
          [
            '<p>Hello <strong>World</strong></p>',
            '<p>Hello <strong>World</strong></p>',
          ],
          [
            '<div><p>Nested <em>tags</em></p></div>',
            '<div><p>Nested <em>tags</em></p></div>',
          ],
          [
            '<p>Text with <br> line break</p>',
            '<p>Text with <br> line break</p>',
          ],
          [
            '<p>   </p><div><span></span></div>',
            '',
          ],
          [
            '<section><article><h1>Title</h1><p>Content</p></article></section>',
            '<section><article><h1>Title</h1><p>Content</p></article></section>',
          ],
    ];
  }

}
