<?php

namespace Drupal\localgov_publications\Service;

/**
 * Heading Finder Interface.
 */
interface HeadingFinderInterface {

  /**
   * Searches given markup for headings to make into links.
   *
   * @param string $markup
   *   HTML to scan for links.
   *
   * @return \Drupal\Core\Link[]
   *   Array of found links.
   */
  public function searchMarkup(string $markup): array;

}
