/**
 * Created by zaelh on 21/03/17.
 */

$(document).ready(function() {
  new Masonry('ol.annonces', {
    itemSelector: '.group'
  });

  new Masonry('ol.advanced-filters-collections', {
    itemSelector: '.singleAnnonce'
  });
})