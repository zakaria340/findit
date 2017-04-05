/**
 * Created by zaelh on 21/03/17.
 */

$(document).ready(function () {
  if ($("ol.annonces .group").length) {
    new Masonry('ol.annonces', {
      itemSelector: '.group'
    });
  }
  $('.advanced-filterss li.more').on('click', function () {
    if ($(this).hasClass('active hover')) {
      $('.advanced-filterss li.more').removeClass('active hover');
    }
    else {
      $('.advanced-filterss li.more').removeClass('active hover');
      $(this).addClass('active hover');
    }
  })

  if ($("ol.annonces").length) {
    new Masonry('ol.annonces', {
      itemSelector: '.group'
    });
  }

  if ($("overlay-content").length) {
    $("body").click(function () {
      window.location.href = $('.close-overlay').attr('href');
    });

    $(".overlay-content").click(function (e) {
      e.stopPropagation();
    });

    $(".close-overlay").click(function (e) {
      e.stopPropagation();
    });
  }

})