(function ($, Drupal) {
  Drupal.behaviors.shorten = {
    attach: function (context, settings) {
      var domain = document.domain;
      var shorten_link = $(".shorten_link").attr('href');
      if (shorten_link) {
        var qr_text = domain + shorten_link;
        $('#qrcodeCanvas').html('');
        $('#qrcodeCanvas').qrcode({
          text  : qr_text,
          width: 150,
          height: 150
        }); 
      }
    }
  };
}(jQuery, Drupal, drupalSettings));
