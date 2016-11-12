// JavaScript Document
$(function(){
	$('.flexslider').flexslider({
		directionNav: true,
		pauseOnAction: false
	});
});
$(function(){
	var navli=$(".menu .nav li");
	var navlia=$(".menu .nav li").children("a");
	var index=navli.siblings().children("a[class='current']").index();
	var index=navlia.index($(".current"));
//	alert(index);
	navlia.hover(function(){
		navli.siblings().children("a[class*='current']").removeClass("current");
		navli.eq(index).addClass("current");
		var sl=$(this).attr("class");
		$(this).addClass("current");
//		alert(sl);
		if(sl=="tri"|| sl=="tri current" ){
			navli.find(".subnav").slideDown(100);	
			}
			else{
			navli.find(".subnav").slideUp(100);
			};
		});
		navlia.mouseout(function(){
			if($('.subnav').css('display')==''){
			navli.find(".subnav").slideUp(100);
			}
			
			else{
			navli.siblings().children("a[class*='current']").removeClass("current");
			navlia.eq(index).addClass("current");
			return false;
				}
			});
			$(".subnav").hover(function(){
						navli.siblings().children("a[class*='current']").removeClass("current");

				$(".menu .nav li .tri").addClass("current");
				});
		$(".subnav").mouseleave(function(){
			navli.find(".subnav").slideUp(100);
			if(navli.eq(2).hasClass("current")){
			return false;
			}else{
				navli.siblings().children("a[class*='current']").removeClass("current");
			navlia.eq(index).addClass("current");
				}

			});
	});



