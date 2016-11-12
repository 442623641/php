function hasCookie() {
	if (!navigator.cookieEnabled) {
		alert('您的手机浏览器不支持或已经禁止使用cookie，无法正常登录，请开启或更换其他浏览器');
	}
}

function bindEvents() {

	user = $('input[name="user"]');
	passwd = $('input[name="passwd"]');
	loginBtn = $('.login_btn');
	loginBtn.css({background:"#CA805C"});

	$(".left_narrow").on('click', function() {
		window.history.back();
	});

	_len_user = user.on('input', function() {
		_len_user = this.value.length;
		enableLogin();
	}).val().length;

	_len_passwd = passwd.on('input', function() {
		_len_passwd = this.value.length;
		enableLogin();
	}).val().length;
}

function enableLogin() {
	if (_len_user && _len_passwd) {
		loginBtn.attr("disabled", false);
		loginBtn.css({background:"#cb4d11"});
	} else {
		loginBtn.attr("disabled", true);
		loginBtn.css({background:"#CA805C"});
	}
}