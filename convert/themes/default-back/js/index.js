$(function(){
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
		
	})
});
//$(document).ready(function (){
//	
//	if ($('#autologin').attr("checked")==true)
//		{
//			$('.msg-wrap').css('display','block');		
//		}
//	else
//		{
//			$('.msg-wrap').css('display','none');
//		}
//});
//处理登录
function login() {
	// action='<?php echo base_url();?>index.php/user/signin'
	$('#loginsubmit').text('正在登录...');
	var user = $('#user').val();
	var pass = $('#password').val();
	if($.trim(user) == '') {
		$("#loginsubmit").html("登&nbsp;&nbsp;&nbsp; 录");
		showMesInfo('请输入帐户名', 'error');		
		return;
	}
	if ($.trim(pass)==''){
		$("#loginsubmit").html("登&nbsp;&nbsp;&nbsp; 录");
		showMesInfo('请输入密码', 'error');		
		return;
	}else{
		var url = "../../index.php/user/login";
		$.ajax( {
			 url: url,
	         type:"post",
	         dataType: "json",
	         data:{
	             user:user,
	             password:pass
	         },
	         data: $("#formlogin").serialize(),
	            error: function () {
	        	 	$("#loginsubmit").html("登&nbsp;&nbsp;&nbsp; 录");
	                showMesInfo("网络超时，请稍后再试","error");
	                return;
	            },
	         success:function(json) {
				if (json.fail != 0) {
					$('#user').val("");
					$('#password').val("");
					 $("#loginsubmit").html("登&nbsp;&nbsp;&nbsp; 录");
					 showMesInfo(json.mess, 'error');	
					 return;
				} else {
					location.href ='index.html'
				}
			}
		});
	}
};

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