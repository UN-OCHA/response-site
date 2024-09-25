<?php

namespace Drupal\rwr_sitrep\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page controller for tabs.
 */
class PdfController extends ControllerBase {

  /**
   * Access check for updates.
   */
  public function isSitrep(Node $node) : AccessResult {
    return AccessResult::allowedIf($node->get('field_is_sitrep')->value == 1);
  }

  /**
   * Return PDF.
   */
  public function getPdf(Node $node) {
    // @todo count chars in the Title and start inserting line breaks when the
    // length exceeds a certain threshold.
    return $this->buildPdf($node->toUrl('canonical', [
      'absolute' => TRUE,
    ])->toString(), $node->label(), $node->getChangedTime());
  }

  /**
   * Generate a PDF.
   */
  private function buildPdf($url, $title, $updated) {
    // Build filename based on the label.
    $filename = $title;
    $filename = Html::cleanCssIdentifier($filename) . '.pdf';

    // Add custom CSS.
    $css = $this->config('ocha_snap.settings')->get('css');
    $css = '<style type="text/css">' . $css . '</style>';

    // Generate PDF.
    $params = [
      'pdfMarginTop' => '142',
      'pdfMarginBottom' => '82',
      'pdfMarginLeft' => '24',
      'pdfMarginRight' => '24',
      'pdfMarginUnit' => 'px',
      'media' => 'print',
      'logo' => 'ocha',
      'pdfHeader' => implode('', [
        '<header class="pdf-header">',
        '<div class="pdf-header__meta">',
        '<div class="pdf-header__title">' . $title . '</div>',
        '<div class="pdf-header__description">' . $this->t('Last updated: @date', [
          '@date' => date('j F Y', $updated),
        ]),
        '</div>',
        '</div>',
        '<div class="pdf-header__logo-wrapper">',
        '<img src="__LOGO_SRC__" width="__LOGO_WIDTH__" height="__LOGO_HEIGHT__" alt="logo" class="pdf-header__logo">',
        '</div>',
        '</header>',
      ]) . $css,
      'pdfFooter' => implode('', [
        '</div><footer class="pdf-footer">',
        '<div class="pdf-footer__left">',
        $this->t('Page @num of @total', [
          '@num' => new FormattableMarkup('<span class="pageNumber"></span>', []),
          '@total' => new FormattableMarkup('<span class="totalPages"></span>', []),
        ]),
        '</div>',
        '<div class="pdf-footer__right">',
        '<span class="url" dir="ltr"></span><br>',
        '<span>' . $this->t('Downloaded: @date', [
          '@date' => date('j F Y'),
        ]) . '</span><br>',
        '</div>',
        '</footer>',
      ]) . $css,
    ];

    // Try to build the PDF.
    $pdf = ocha_snap_generate($url, $params);

    // Show an error and return to original page if no PDF is generated.
    if (empty($pdf)) {
      $this->messenger()->addWarning($this->t('There was a problem creating a pdf for this page.'));
      return new RedirectResponse($url);
    }

    /** @var string $pdf */
    $response = new Response();

    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Content-type', 'application/pdf; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Cache-control', 'private');
    $response->headers->set('Content-length', strlen($pdf));

    $response->setContent($pdf);

    $response->send();

    return $response;
  }

}