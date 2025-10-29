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
    $text = HtmlSanitizer::sanitize($text, TRUE);
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
          // Remove script tags and their content.
          [
            '<p>Safe</p><script>alert("x")</script>',
            '<p>Safe</p>',
          ],
          // Remove style tags and their content.
          [
            '<style>body{color:red;}</style><div>Styled</div>',
            '<div>Styled</div>',
          ],
          // Remove HTML comments.
          [
            '<p>Text<!-- comment --></p>',
            '<p>Text</p>',
          ],
          // Remove event attributes.
          [
            '<span onclick="evil()">Click</span>',
            '<span>Click</span>',
          ],
          // Style attributes is allowed.
          [
            '<span style="color:red">Red</span>',
            '<span style="color:red">Red</span>',
          ],
          // Remove unallowed attributes.
          [
            '<a href="/" data-evil="1">Link</a>',
            '<a href="/">Link</a>',
          ],
          // Convert <b> and <i> to <strong> and <em>.
          [
            '<b>Bold</b> <i>Italic</i>',
            '<strong>Bold</strong> <em>Italic</em>',
          ],
          // Deeply nested tags.
          [
            '<div><div><div><span>Deep</span></div></div></div>',
            '<div><div><div><span>Deep</span></div></div></div>',
          ],
          // Non-breaking spaces.
          [
            '<p>&nbsp;Test&nbsp;</p>',
            '<p> Test </p>',
          ],
          // Orphan list item.
          [
            '<li>Item</li>',
            '<ul><li>Item</li></ul>',
          ],
          // Table gets wrapped.
          [
            '<table><tr><td>Cell</td></tr></table>',
            '<div class="table-wrapper"><table><tr><td>Cell</td></tr></table></div>',
          ],
          // Iframe gets wrapped and attributes sanitized.
          [
            '<iframe src="video" width="400" height="200" style="border:0"></iframe>',
            '<div class="iframe-wrapper" style="padding-top:50%"><iframe src="video" sandbox="allow-same-origin allow-scripts allow-popups" target="_blank"></iframe></div>',
          ],
          // Image gets alt attribute.
          [
            '<img src="x.png">',
            '<img src="x.png" alt="">',
          ],
          // Heading keeps id and style.
          [
            '<h3 id="foo" style="color:red" class="bar">Title</h3>',
            '<h3 id="foo" style="color:red">Title</h3>',
          ],
          // Link with target _blank gets rel.
          [
            '<a href="/" target="_blank">Blank</a>',
            '<a href="/" target="_blank" rel="noreferrer noopener">Blank</a>',
          ],
          // Font tag is stripped.
          [
            '<font color="red">Red</font>',
            'Red',
          ],
          // Invalid HTML.
          [
            '<div><span>Broken',
            '<div><span>Broken</span></div>',
          ],
          // Empty string.
          [
            '',
            '',
          ],
          // Non-string input.
          [
            123,
            '',
          ],
    ];
  }

  /**
   * Test tag conversion: <b>, <i>, <big> to <strong>, <em>.
   */
  public function testTagConversion() {
    $input = '<b><span style="color:red">Bold</span></b> <i>Italic</i> <big>Big</big> <strong>Strong</strong> <em>Em</em>';
    $expected = '<strong><span style="color:red">Bold</span></strong> <em>Italic</em> <strong>Big</strong> <strong>Strong</strong> <em>Em</em>';
    $output = HtmlSanitizer::sanitize($input, TRUE);
    $this->assertEquals($expected, $output);
  }

  /**
   * Test Ukraine page.
   */
  public function testUkrainePage() {
    $input = file_get_contents(__DIR__ . '/fixtures/ukraine_page.in.html');
    $expected = file_get_contents(__DIR__ . '/fixtures/ukraine_page.out.html');

    $output = HtmlSanitizer::sanitize($input);
    $this->assertEquals($this->normalizeHtml($expected), $this->normalizeHtml($output));
  }

  /**
   * Remove line breaks for easier comparison in tests.
   */
  protected function normalizeHtml($html) {
    return str_replace(["\n", "\r"], '', $html);
  }

}
