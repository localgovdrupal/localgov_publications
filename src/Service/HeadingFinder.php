<?php

namespace Drupal\localgov_publications\Service;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Heading finder.
 */
class HeadingFinder implements HeadingFinderInterface {

  /**
   * {@inheritDoc}
   */
  public function searchMarkup(string $markup): array {

    $links = [];
    $headings = [];

    // Look for headings in the text.
    if (preg_match_all('#<h2([^>]+)>([^<]+)</h2>#', $markup, $matches)) {

      // Format the results into $headings under the 'attributes' and 'text'
      // keys. As we scanned rendered and escaped markup, we'll need to decode
      // the title to avoid double escaping.
      foreach ($matches[1] as $key => $value) {
        $headings[] = [
          'attributes' => $value,
          'text' => html_entity_decode($matches[2][$key]),
        ];
      }
    }

    foreach ($headings as $heading) {
      $attributes = explode(' ', $heading['attributes']);

      $fragment = '';

      // Find the id attribute if there is one.
      foreach ($attributes as $attribute) {
        if (str_starts_with($attribute, 'id=')) {
          // Trim off 'id='.
          $fragment = substr($attribute, 3);
          // Trim off quotes if they were used.
          $fragment = trim($fragment, '\'"');
        }
      }

      // If we didn't find a fragment to link to, don't include this result.
      if (strlen($fragment) > 0) {
        $links[] = Link::fromTextAndUrl($heading['text'], Url::fromUserInput('#' . $fragment));
      }
    }

    return $links;
  }

}
