var userCookie;
$(function(){
	//init();
	var navLi = $(".menu .nav li");
	navLi.hover(function(){
		$(this).siblings().find("a[class*='current']").removeClass("current");
		$(this).find("a").addClass("current");
		$(this).find(".subnav").slideDown();
	},function(){
		$(this).find(".subnav").slideUp();
		if($(this).index()!=$(this).parent().children().length-1){ 
			$(this).find("a").removeClass("current");
			$(this).parent().children(":last").find("a").addClass("current");
		}
		
	});
	if ($("#loginsubmit").html()!=null)
		{		
		$("#loginsubmit").css('display','');
		}
});
function init(){
	userCookie=GetCookie('user');
}
// 处理登录
function login() {
	// action='<?php echo base_url();?>index.php/user/signin'
	$('#loginsubmit').text('正在登录...');
	var user = $('#user').val();
	var pass = $('#password').val();
	if($.trim(user) == '') {
		$("#loginsubmit").html("登&nbsp;&nbsp; &nbsp; 录");
		showMesInfo('请输入帐户名', 'error');		
		return;
	}
	if ($.trim(pass)==''){
		$("#loginsubmit").html("登&nbsp;&nbsp; &nbsp; 录");
		showMesInfo('请输入密码', 'error');		
		return;
	}else{
		var url = "../index.php/user/login";
		$.ajax( {
			 url: url,
	         type:"post",
	         dataType: "json",
	         data:{
	             user:user,
	             password:pass
	         },
	         // data: $("#formlogin").serialize(),
	            error: function () {
	        	 	$("#loginsubmit").html("登&nbsp;&nbsp; &nbsp; 录");
	                showMesInfo("网络超时，请稍后再试","error");
	                return;
	            },
	         success:function(json) {
				if (json.fail != 0) {
					$('#user').val("");
					$('#password').val("");
					 $("#loginsubmit").html("登&nbsp;&nbsp; &nbsp; 录");
					 showMesInfo(json.mess, 'error');	
					 return;
				} else {
					location.href = 'index.html';
					setSigned(json);
				}
			}
		});
	}
}
// 处理全民付
function umspay() {
	// action='<?php echo base_url();?>index.php/user/signin'
	if (user.username.length<11){
		scscms_alert("请先登录", "warn", function() {location.href = 'login.html'}, 3);
	}
	var url = "../index.php/umspay/umspay";
	$.ajax( {
		 url: url,
         type:"post",
         dataType: "json",
         data:{
             user:user,
         },
         data: $("#formlogin").serialize(),
            error: function () {
	        	 scscms_alert("网络超时，请稍后再试", "error");
	             return;
            },
         success:function(json) {
            	return;
		}
	});	         
}
// 登录状态
function setSigned(json)
{
	if (userCookie!=null&&json!=null){
		DelCookie("user");	
	}
	SetCookie("user", JSON.stringify(json));
	var signedStr="<a href='personal_info.html'>"+json.username+"</a><a onclick='loginOut()'>|&nbsp;&nbsp;退出</a>";
	$('.txtindent50 span').html(signedStr);
	$('.login_user').html(json.username);
	$('#div_login').css('display','none');
	$('#div_signed').css('display','');
}
function showMesInfo(msg, type) {
	$('.msg-wrap').empty();
	if (type == 'warn') {
		var info = '<div class="msg-warn"><b></b>' + msg + '</div>';
		$('.msg-wrap').append(info);
	}
	if (type == 'error') {
		var info = '<div class="msg-error"><b></b>' + msg + '</div>';
		$('.msg-wrap').append(info);
	}
}
//两个参数，一个是cookie的名子，一个是值
function SetCookie(name, value, days) {
    var seconds = arguments[2] ? arguments[2] : 1000000;
    var exp = new Date();
    exp.setTime(exp.getTime() + seconds * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

//取cookies函数
function GetCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) return unescape(arr[2]); return null;
}

//删除cookie
function DelCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = GetCookie(name);
    if (cval != null) document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}