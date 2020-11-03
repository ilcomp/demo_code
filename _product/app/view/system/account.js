var timeout_is_login;

function is_login() {
  clearTimeout(timeout_is_login);

  timeout = setTimeout(function(){
    $.ajax({
        url: getURLPath() + '?route=account/account/is_login',
        cache: false,
        complete: function(xhr, ajaxOptions, thrownError) {
          if (xhr.status < 200 || xhr.status > 300)
            location.reload();
        }
    });
  }, 100);
}

var param_name = false, event_name;
if (typeof document.hidden!='undefined') {
    param_name='hidden';
    event_name='visibilitychange';
} else if (typeof document.mozHidden!='undefined') {
    param_name='mozHidden';
    event_name='mozvisibilitychange';
} else if (typeof document.msHidden!='undefined') {
    param_name='msHidden';
    event_name='msvisibilitychange';
} else if (typeof document.webkitHidden!='undefined') {
    param_name='webkitHidden';
    event_name='webkitvisibilitychange';
}

window.addEventListener('focus', is_login, false);
if (param_name) {
    document.addEventListener(event_name, function() {
        if (!document[param_name]) is_login();
    }, false);
}