/// Check user exists

var checkuser_info_div = 0;
var checkuser_text_elem = 0;
var checkuser_event_proc = 0;
var checkuser_event_proc2 = 0;
var do_start = 0;
var start_pause = 1;
var check_val = "";


function checkuser_change(obj, info, eventproc, eventproc2) {
  checkuser_text_elem = obj;
  if (obj.value == check_val) {
    obj.style.background = "#fcc";
    return;
  }
  check_val = obj.value;
  if (checkuser_info_div == 0) {
    checkuser_info_div = document.getElementById(info);
  }

  checkuser_event_proc = eventproc;
  checkuser_event_proc2 = eventproc2;

//  if (checkuser_info_div) {
    var reg = /^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}/
//
    checkuser_event_proc(-1);
//    console.log();
    if (!isValidEmail(obj.value)) {
      obj.style.background = "#fcc";
//      checkuser_info_div.innerHTML = "некорректный адрес";
      do_start = 0;
      start_pause = 1;
    } else {
      obj.style.background = "#fff";
//      checkuser_info_div.innerHTML = "проверка логина...";
      do_start = 1;
      start_pause = 1;
    }
//  }
}


function timer_start_check() {
  if (do_start) {
    if (start_pause)
      start_pause--;
    else {
      do_start = 0;
      start_pause = 1;

      jQuery.post("ajax.php", {action: "check_email", email: check_val}, function (ans) {

        if (ans == 0) {
//          checkuser_info_div.innerText = "логин свободен";
//          checkuser_text_elem.style.background = "#dfd";
          checkuser_event_proc(0);
          if (checkuser_event_proc2) checkuser_event_proc2(1);
        } else if (ans == -1) {
//          checkuser_info_div.innerText = "некорректный адрес";
          checkuser_text_elem.style.background = "#fcc";
          checkuser_event_proc(-1);
          if (checkuser_event_proc2) checkuser_event_proc2(-1);
        } else {
          document.location.reload();
//					checkuser_info_div.innerText = "логин существует";
//					checkuser_text_elem.style.background="#ffc";
//					checkuser_event_proc(1);
//					if (checkuser_event_proc2) checkuser_event_proc2(0);
        }
      });

    }
  }
  setTimeout("timer_start_check()", 1000);
}

setTimeout("timer_start_check()", 1000);

function show_pwd_part(show) {
  if (show == 1)
    jQuery("#mkorder_user_loginbox_pwd").show();
  else
    jQuery("#mkorder_user_loginbox_pwd").hide();
}

function show_lgn_part(show) {
  if (show == 1) {
    jQuery("#login_user_reg").show();
    jQuery("#login_button").hide();
  } else {
    jQuery("#login_user_reg").hide();
    jQuery("#login_button").show();
  }
}

function checkuser_change_zak(obj) {
  checkuser_change(obj, "checkuser_info_text", show_pwd_part, false);
}

function checkuser_change_lgn(obj) {
  checkuser_change(obj, "checkuser_info_text", show_pwd_part, show_lgn_part);
}

////////////////////////////////////////////////////

function mkorder_auth_user() {
  // check login and password
  var l = jQuery("#user_email").val();
  var p = jQuery("#user_password").val();

  if (isValidEmail(l) && (p.length > 4)) {

    jQuery.post("/frame/?type=form", {ajax_mode: true, forma_zakaz: true, zf_user_login: l, zf_user_password: p}, function (ans) {

      if (ans == "1") {
        document.location.reload();
      }
      else
        alert("Неправильный пароль");
    });

  }
}

// mkorder
function mkorder_liketel_change(val) {
  var x = document.getElementById("mkorder_teltime_block");
  if (x) {
    if (val)
      x.style.display = "block";
    else
      x.style.display = "none";
  }
}

function mkorder_onchange_work_type(obj) {
  if (obj.value == 255)
    jQuery("#mkorder_order_workvid_user").show();
  else
    jQuery("#mkorder_order_workvid_user").hide();
}

function mkorder_oform_change(val) {
  var g = document.getElementById("oform_gost");
  var u = document.getElementById("oform_user");

  if (g && u) {
    if (val) {
      u.style.display = "none";
      g.style.display = "block";
    } else {
      g.style.display = "none";
      u.style.display = "block";
    }
  }
}

function mkorder_add_file_change(obj) {

  var f = document.getElementById('zakaz_form');
  var d = document.getElementById('add_file_field');


  if (!f || !d) {
    alert('Ошибка');
    return;
  }

  f.submit();

  d.innerText = "Файл загружается...";

  var i;
  for (i = 0; i < f.elements.length; i++) {
    if ("disabled" in f.elements[i]) f.elements[i].disabled = true;
  }
}

function mkorder_delete_upl_file(num) {
  var p = document.getElementById('zf_file_to_del');
  if (p) {
    p.value = num;
    var f = document.getElementById('zakaz_form');
    if (f) f.submit();
  }
}

// Login
function login_auth_user() {
  // check login and password
  var l = jQuery("#user_email").val();
  var p = jQuery("#user_password").val();

  if (isValidEmail(l) && (p.length > 4)) {

    jQuery.post("/frame/?type=form", {ajax_mode: true, forma_zakaz: true, zf_user_login: l, zf_user_password: p}, function (ans) {

      if (ans == "1") {
        document.location.reload();
      }
      else
        alert("Неправильный пароль");
    });

  }
}

function login_pwd_key(e) {
  var p = jQuery("#user_password").val();
  if ((p.length > 4) && (e.which == 13)) {
    login_auth_user();
    return false;
  }
}

