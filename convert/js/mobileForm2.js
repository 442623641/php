function hasCookie() {
	if (!navigator.cookieEnabled) {
		alert('您的手机浏览器不支持或已经禁止使用cookie，无法正常登录，请开启或更换其他浏览器');
	}
}

function bindEvents() {

	user = $('input[name="user"]');
	passwd = $('input[name="passwd"]');
	passwdCf = $('input[name="passwdCf"]');
	telCode = $('input[name="telCode"]');
	loginBtn = $('.login_btn');
	loginBtn.css({background:"#CA805C"});

	$(".left_narrow").on('click', function() {
		window.history.back();
	});

	_len_user = user.on('input', function() {
		_len_user = this.value.length;
		enableCode()
		enableLogin();
	}).val().length;

	_len_passwd = passwd.on('input', function() {
		_len_passwd = this.value.length;
		enableLogin();
	}).val().length;

	_len_passwdCf = passwdCf.on('input', function() {
		_len_passwdCf = this.value.length;
		enableLogin();
	}).val().length;

	_len_telCode = telCode.on('input', function() {
		_len_telCode = this.value.length;
		enableLogin();
	}).val().length;
}

function enableCode(){
	if (_len_user) {
		$(".send_ms").attr({"t":"0"});
		$(".send_ms").addClass("get_yzm");
	} else {
		$(".send_ms").attr({"t":"1"});
		$(".send_ms").removeClass("get_yzm");
	}
}

function enableLogin() {
	if (_len_user && _len_passwd && _len_passwdCf && _len_telCode) {
		loginBtn.attr("disabled", false);
		loginBtn.css({background:"#cb4d11"});
	} else {
		loginBtn.attr("disabled", true);
		loginBtn.css({background:"#CA805C"});
	}
}