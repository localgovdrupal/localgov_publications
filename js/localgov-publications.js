(($, Drupal) => {
  Drupal.behaviors.publicationDetailsSummaries = {
    attach: function attach(context) {
      $(context)
        .find('.book-outline-form')
        .drupalSetSummary(() => {
          const $select = $(context).find('.book-title-select');
          const val = $select[0].value;
          if (val === '0') {
            return Drupal.t('Not in publication');
          }
          if (val === 'new') {
            return Drupal.t('New publication');
          }
          return Drupal.checkPlain($select.find(':selected')[0].textContent);
        });
    },
  };
})(jQuery, Drupal);
