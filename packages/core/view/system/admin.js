function getURLPath() {
	return String(document.location.pathname);
}

function getURLVar(key) {
	var value = [];

	var part = String(document.location.search).substring(1).split('&');

	for (i = 0; i < part.length; i++) {
		var data = part[i].split('=');

		if (data[0] && data[1] && data[0] == key)
			return data[1];
	}

	return '';
}

// Is Login
var timeout_is_login;

function is_login() {
	clearTimeout(timeout_is_login);

	timeout_is_login = setTimeout(function(){
		fetch(getURLPath() + '?route=common/login/is_login&user_token=' + getURLVar('user_token'),{
			credentials: 'include',
			cache: 'no-cache'
		})
		.then(function(r){
			if (r.status == 401)
				location.reload();
		})
		.catch(function(error) {
			console.error(error);
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

// Translit
var timeout_is_translit;

function setKeyword(srcVal, dst) {
  clearTimeout(timeout_is_translit);

  timeout_is_translit = setTimeout(function(){
    var data = new FormData();

    data.append('str', srcVal);
    data.append('language', dst.data('language'));

    fetch(getURLPath() + '?route=tool/translit&user_token=' + getURLVar('user_token'),{
      credentials: 'include',
      cache: 'no-cache',
      method: 'post',
      body: data
    })
    .then(function(r){
      if (r.status == 200)
        return r.text();
    })
    .then(function(text){
      $(dst).val(text);
    })
    .catch(function(error) {
      console.error(error);
    });
  }, 100);
}