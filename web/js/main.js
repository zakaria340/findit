/**
 * Created by zaelh on 21/03/17.
 */

$(document).ready(function() {
  new Masonry('ol.annonces', {
    itemSelector: '.group'
  });
  $('.advanced-filterss li.more').on('click', function () {
    if ($(this).hasClass('active hover')) {
      $('.advanced-filterss li.more').removeClass('active hover');
    }
    else {
      $('.advanced-filterss li.more').removeClass('active hover');
      $(this).addClass('active hover');
    }
  })
})