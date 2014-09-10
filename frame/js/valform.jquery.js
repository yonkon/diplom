/* v2.0 */

function isValidEmail(email, strict) {
  if (!strict) email = email.replace(/^\s+|\s+$/g, '');
  return (/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i).test(email);
}

function isValidTel(tel) {
  var re = /^[0-9\-\+\(\) ]{5,}$/;
  if (!re.test(tel)) return false;

  tel = tel.replace(/[^0-9]+/g, '');

  if (tel.length < 5) return false;

  return true;
}

(function ($) {

  function gotoElem(e) {
    $('html, body').stop().animate({scrollLeft: 0, scrollTop: e.offset().top - 30}, 100);
  }

  function alertAnim(e) {
    e.blur();
    e.css("background-color", "#faa");
    /*
     for (var i=1; i<10; i++)
     {
     setTimeout(function(){ e.css("background-color", "#fff"); }, 200*i);
     setTimeout(function(){ e.css("background-color", "#faa"); }, 200*i + 100);
     }
     */
  }

  $.fn.valform = function () {

    return this.each(function () {
      var f = $(this);

      var iflds = f.find("input,select");
      for (var i = 0; i < iflds.length; i++) {
        var fld = $(iflds[i]);
        fld.css("background-color", "#fff");

        fld.bind("focus", function () {
          $(this).css("background-color", "#fff");
        });

      }

      f.bind("submit", function () {
        var ok = true;

        var inps = f.find("input,select");
        for (var i = 0; i < inps.length; i++) {
          var e = $(inps[i]);

          if (e.is(":hidden")) continue;

          if (e.hasClass("required") && !e.val().length) {
            gotoElem(e);
            alertAnim(e);
            ok = false;
            break;
          }

          if (e.hasClass("email") && e.val().length && !isValidEmail(e.val())) {
            gotoElem(e);
            alertAnim(e);
            ok = false;
            break;
          }

          if (e.hasClass("telephone") && e.val().length && !isValidTel(e.val())) {
            gotoElem(e);
            alertAnim(e);
            ok = false;
            break;
          }

          if (e.hasClass("nozero") && (e.val() == "0")) {
            gotoElem(e);
            alertAnim(e);
            ok = false;
            break;
          }

        }

        return ok;
      });

    });

  }

})(jQuery);