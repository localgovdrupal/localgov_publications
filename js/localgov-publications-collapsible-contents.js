(function ($, Drupal, drupalSettings) {
  /**
   * Collapseable menu for Publication page
   *
   * @param {object} context
   */
  Drupal.behaviors.publicationMenuToggle = {
    attach: function (context) {

      if (!drupalSettings.hasOwnProperty('localgov_publications')) {
        return;
      }

      var headers = [
        $('.lgd-publication-navigation__content-header', context),
        $('.lgd-publication-tableofcontent__content-header', context)
      ];
      var menus = [
        $('#block-lgd-publicationnavigation ul.list--no-style', context),
        $('#block-lgd-publicationstableofcontentsblock .publication-content-block', context)
      ];

      function toggleMenuVisibilityAndIcon(header, menu) {
        header.on('click', function () {
          menu.toggleClass('is-hidden');
          header.toggleClass('up-icon down-icon');
        });
      }

      let previousWidth = -1;

      function initializeStateForMenus(menuCollapseBreakpoint) {

        return function () {
          const previousCollapseState = previousWidth < menuCollapseBreakpoint;
          const newCollapseState = window.innerWidth < menuCollapseBreakpoint;

          if (previousCollapseState === newCollapseState && !(previousWidth === -1)) {
            return;
          }
          previousWidth = window.innerWidth;

          headers.forEach(function (header, index) {
            menus[index].toggleClass('is-hidden', newCollapseState);
            header.toggleClass('up-icon', !newCollapseState).toggleClass('down-icon', newCollapseState);
          });
          headers.forEach(function (header, index) {
            if (!header.data('menuToggleAttached')) {
              toggleMenuVisibilityAndIcon(header, menus[index]);
              header.data('menuToggleAttached', true);
            }
          });
        }
      }

      const resizeCallback = initializeStateForMenus(drupalSettings.localgov_publications.foo.collapse_width);

      if (drupalSettings.localgov_publications.foo.collapsible) {
        resizeCallback();
        $(window).resize(resizeCallback);
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
