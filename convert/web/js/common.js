$(function(){
	//页面导航菜单
	var menus = ["index", "charge", "server", "account"];

	var navUrl = window.location.href;
	var arrUrl = navUrl.split("/");
	var menu = arrUrl[arrUrl.length-1];

	for (var i = 0; i < menus.length; i++) {
		if (menu.indexOf(menus[i]) > -1){
			menu = menus[i];
			break;
		}
	}

	$("."+menu).addClass('current').siblings().removeClass('current');

	//签到
	

});