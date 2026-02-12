(($, Drupal, drupalSettings) => {
  /**
   * Collapsible menu for Publication page
   */
  Drupal.behaviors.publicationMenuToggle = {
    attach: (context) => {
      if (!drupalSettings.hasOwnProperty('localgov_publications')) {
        return;
      }

      for (const [blockPluginID, values] of Object.entries(drupalSettings.localgov_publications)) {
        const blockID = 'block-' + blockPluginID.replaceAll('_', '-');
        if (values.collapsible) {
          setUpBlock(context, blockID, values.collapse_width);
        }
      }
    },
  };

  /**
   * Handles window resize.
   */
  function handleResize($title, $content, menuCollapseBreakpoint) {
    // Default the menus to hidden if we're below the mobile breakpoint width.
    if (window.innerWidth > menuCollapseBreakpoint) {
      $title.removeClass('expand collapse');
      $content.show();
    }
    else {
      $title.addClass('expand');
      $content.hide();
    }
  }

  /**
   * Sets up a single block to be collapsible.
   */
  function setUpBlock(context, blockID, collapseWidth) {

    const $block = $('.' + blockID, context);
    const $title = $block.find('h2').first();
    const $content = $block.find('ul').first();

    handleResize($title, $content, collapseWidth);

    $title.on('click', function () {

      if (window.innerWidth > collapseWidth) {
        return;
      }

      if ($title.hasClass('collapse')) {
        $content.hide();
      }
      else {
        $content.show();
      }

      $title
        .toggleClass('expand')
        .toggleClass('collapse');
    });

    $(window).resize(() => {
      handleResize($title, $content, collapseWidth);
    });
  }

})(jQuery, Drupal, drupalSettings);
