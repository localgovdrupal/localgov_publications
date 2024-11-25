(function ($, Drupal) {
  Drupal.behaviors.publicationDetailsSummaries = {
    attach: function attach(context) {
      $(context).find('.book-outline-form').drupalSetSummary(function (context) {
        var $select = $(context).find('.book-title-select');
        var val = $select[0].value;
        if (val === '0') {
          return Drupal.t('Not in publication');
        }
        if (val === 'new') {
          return Drupal.t('New publication');
        }
        return Drupal.checkPlain($select.find(':selected')[0].textContent);
      });
    }
  };
})(jQuery, Drupal);

/**
   * Collapse able menu for Publication page
   *
   * @param {object} context
   */
Drupal.behaviors.publicationMenuToggle = {
  attach: function(context) {
    const menuCollapseBreakpoint = 992;

    var headers = [
      $('.bfc-publication-navigation__content-header', context),
      $('.bfc-publication-tableofcontent__content-header', context)
    ];
    var menus = [
      $('#block-bfc-publicationnavigation ul.list--no-style', context),
      $('#block-bfc-publicationstableofcontentsblock .publication-content-block', context)
    ];
    function toggleMenuVisibilityAndIcon(header, menu) {
      header.on('click', function () {
        menu.toggleClass('is-hidden');
        header.toggleClass('up-icon down-icon');
      });
    }

    let previousWidth = -1;

    function initializeStateForMenus() {
      const previousCollapseState = previousWidth < menuCollapseBreakpoint;
      const newCollapseState = window.innerWidth < menuCollapseBreakpoint;

      if (previousCollapseState === newCollapseState && !(previousWidth === -1)) {
        return;
      }
      previousWidth = window.innerWidth;

      headers.forEach(function(header, index) {
        menus[index].toggleClass('is-hidden', newCollapseState);
        header.toggleClass('up-icon', !newCollapseState).toggleClass('down-icon', newCollapseState);
      });
    }
    headers.forEach(function(header, index) {
      if (!header.data('menuToggleAttached')) {
        toggleMenuVisibilityAndIcon(header, menus[index]);
        header.data('menuToggleAttached', true);
      }
    });
    initializeStateForMenus();
    $(window).resize(initializeStateForMenus);
  }
};