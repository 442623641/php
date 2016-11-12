var numTimer;
var phonetimer = null;
$(function() {
	var navLi = $(".menu .nav li");
	navLi.hover(function() {
		$(this).siblings().find("a[class*='current']").removeClass("current");
		$(this).find("a").addClass("current");
		$(this).find(".subnav").slideDown();
	}, function() {
		$(this).find(".subnav").slideUp();
		if ($(this).index() != $(this).parent().children().length - 1) {
			$(this).find("a").removeClass("current");
			$(this).parent().children(":last").find("a").addClass("current");
		}

	});
	initReg();
});
// 发送短信验证码
function code() {
	if ($("#fontCode").attr("t") == "0") {
		$("#fontCode").attr("t", "1").removeClass("get_yzm");
		var user = $("#txtPhone").val();
		$.ajax( {
			type : "POST",
			dataType : "json",
			url : "../../index.php/user/code",
			data : {
				user : user
			},
			success : function(json) {
				if (json.fail == 0) {
					scscms_alert(json.code, "ok",'',3);// 测试
					numTimer = 60;
					if (phonetimer == null) {
						phonetimer = setInterval(TimeAddSecond, 1000);
					}
				} else {
					$("#fontCode").attr("t", "0");
					scscms_alert(json.mess, "error");
				}
			}
		});
	}
}
// 注册
function register() {
	var postdata = {};
	postdata.user = $("#txtPhone").val();
	if (postdata.user.length > 0) {
		if (!regPhone(postdata.user)) {
			$("#spPhone").html("*手机号码格式错误");
			return;
		}
		postdata.code = $("#txtCode").val();
		if (postdata.code.length == 0) {
			$("#spCode").html("*短信验证码不能为空");
			return;
		}
	}
	else{
			$("#spPhone").html("*手机号码格式错误");
			return;
	}
	postdata.password = $("#txtPassword").val();
	if (postdata.password.length < 6) {
		$("#spPassword").html("*密码长度不足6位");
		return;
	}
	postdata.repassword = $("#txtPassword").val();
	if (postdata.repassword != postdata.password) {
		$("#spRePasswd").html("*两次输入密码不一致");
		return;
	}
	postdata.repassword = $.md5(postdata.repassword).toUpperCase();
	postdata.password = $.md5(postdata.password).toUpperCase();
	var url = "../../index.php/user/register";
	$.ajax( {
		url : url,
		type : "post",
		dataType : "json",
		data : postdata,
		error : function() {
			scscms_alert("网络超时，请稍后再试", "error");
			return;
		},
		success : function(json) {
			if (json.fail != 0) {
				scscms_alert(json.mess, "error");
				return;
			} else {
				scscms_alert("恭喜您，注册成功", "ok", function() {
					location.href = 'index.html'
				}, 3);
			}
		}
	});

}
function regPhone(pho) {
	if (pho.length != 11)
		return false;
	var phone = /^(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/;
	return phone.test(pho);

}

function initReg() {
	$("#txtPhone").keyup(function() {
		var obj = $("#spPhone");
		var objSend = $("#fontCode");
		var objcode = $("#txtCode");
		this.value = this.value.replace(/[^\d]/g, '');
		if (!regPhone(this.value)) {
			obj.html("*手机号码格式错误");
			if (objSend.hasClass("get_yzm")) {
				objSend.removeClass("get_yzm");
			}
			objcode.disabled = "disabled";
			objSend.attr("t", "1");
		} else {
			obj.html("");
			objSend.removeClass("send_yzm");
			objSend.addClass("get_yzm");
			objcode.disabled = "";
			objSend.attr("t", "0");
		}
	});
	$("#txtPassword").keyup(function() {
		var obj = $("#spPassword");
		var rs = /^[A-Za-z0-9]+$/;
		// this.value = this.value.replace(/[^\d]/g, '');
		if (!rs.test(this.value)) {
			obj.html("*密码只能输入字母和数字");
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
	var objSend = $("#fontCode");
	if (numTimer <= 0) {
		window.clearInterval(phonetimer);
		phonetimer = null;
		if (!objSend.hasClass("get_yzm")) {
			objSend.addClass("get_yzm");
		}
		objSend.disabled= "";
		objSend.html("获取验证码");
	} else {
		objSend.html("重新获取(" + numTimer + ")");
	}
}