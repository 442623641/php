var numTimer;
var phonetimer = null;
$(function(){
	initReg();
});
function regPhone(pho) {
	if (pho.length != 11)
		return false;
	var phone = /^(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/;
	return phone.test(pho);

}

function initReg() {
	$("#txtPhone").keyup(function() {
		var obj = $("#spPhone");
		//var objSend = $("#fontCode");
		var objSend = $(".send_yzm");
		var objcode = $("#txtCode");
		this.value = this.value.replace(/[^\d]/g, '');
		if (!regPhone(this.value)) {
			obj.html("*手机号码格式错误");
			if (objSend.hasClass("get_yzm")) {
				objSend.removeClass("get_yzm");
			}
			objcode.attr("disabled", true);
			objSend.attr("t", "1");
		} else {
			obj.html("");
			//objSend.removeClass("send_yzm");
			objSend.addClass("get_yzm");
			objcode.attr("disabled", false);
			objSend.attr("t", "0");
		}
	});
	$("#txtPassword").keyup(function() {
		var obj = $("#spPassword");
		var rs = /^[A-Za-z0-9]+$/;
		// this.value = this.value.replace(/[^\d]/g, '');
		if (!rs.test(this.value)) {
			obj.html("*密码只能是字母数字");
		} else if (this.value.length < 6) {
			obj.html("*密码长度不足6位");
		} else {
			obj.html("");
		}
	});
	$("#txtCode").keyup(function() {
		this.value = this.value.replace(/[^\d]/g, '');
		var obj = $("#spCode");
		if (this.value.length == 0) {
			obj.html("*短信验证码不能为空");
		} else {
			obj.html("");
		}
	});

	$("#txtRePassword").keyup(function() {
		var obj = $("#spRePassword");
		var pas = $("#txtPassword").val();
		if (pas != this.value) {
			obj.html("*两次输入密码不一致");
		} else {
			obj.html("");
		}
	});
}

function TimeAddSecond() {
    numTimer--;
    var objSend = $(".send_yzm");
    if (numTimer <= 0) {
        window.clearInterval(phonetimer);
        phonetimer = null;
        if (!objSend.hasClass("get_yzm")) {
            objSend.addClass("get_yzm");
        }
        objSend.attr("disabled", false).html("获取验证码");
    }
    else {
        objSend.html(numTimer+"秒后可重新获取");
    }
}