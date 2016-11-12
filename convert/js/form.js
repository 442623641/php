($.form = function(){
	var user = $('input[name="user"]');
	var passwd = $('input[name="passwd"]');
	var passwdCf = $('input[name="passwdCf"]');
	var telCode = $('input[name="telCode"]');

	if ($.trim(user.val()) == "") {
		alert('请填写手机号码或邮箱!');
		user.focus();
		return false;
	}

	if($.trim(user.val()) != "") {
		var reg_tel = /^1[3|4|5|8][0-9]\d{4,8}$/;
		var reg_email = /^\w{3,}@\w+(\.\w+)+$/;
		if(!reg_tel.test($.trim(user.val())) && !reg_email.test($.trim(user.val())))
		{
			alert("手机号码或邮箱不合法!");
			user.val('');
			user.focus();
			return false;
		}
	}

	if ($.trim(passwd.val()) == '') {
		alert('请输入密码');
		passwd.focus();
		return false;
	}

	if (typeof(passwdCf.val()) != "undefined"){
		if ($.trim(passwdCf.val()) == '') {
			alert('请输入确认密码');
			passwdCf.focus();
			return false;
		}

		if ($.trim(passwd.val()) != $.trim(passwdCf.val())) {
			alert('两次输入密码不一致');
			passwdCf.focus();
			return false;
		}
	}

	if (typeof(telCode.val()) != "undefined") {
		if ($.trim(telCode.val()) == "") {
			alert('请输入验证码');
			telCode.focus();
			return false;
		}
	}
});

 
 
 