(function($) {
  $(function() {
    $('.autocomplete').autocomplete({
      minLength: 2,
      source: '/index.php?section=finances&subsection=2&action=autocomplete'
    });
  });
})(jQuery);