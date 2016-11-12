
$(function() {

	// 个人中心首页导航
	$(".grzx_nav a").click(
			function() {
				$(this).addClass("grzx_sy_current").siblings().removeClass(
						"grzx_sy_current");

			})

	// 个人中心首页-签到赚积分
	$("#Sign").click(function() {
		$(this).addClass("sign_current").html("已签到")
	})

	// 交易记录
	$("#jyjl_tab_ul_li li").click(
			function() {
				$("#jyjl_tab_ul_li li").eq($(this).index()).addClass(
						"jyjl_tab_current").siblings().removeClass(
						'jyjl_tab_current');
				$("#jyjl_tab").children().hide().eq($(this).index()).show();
			});

	// 账户中心
	$(".grzx_cot_lf_ul a").click(
			function() {
				$(this).addClass("grzx_lf_ul_li_current").parentsUntil(
						".grzx_cot_lf_ul").siblings().find("a").removeClass(
						"grzx_lf_ul_li_current");
			});

	$("#grzx_cz").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_cz.html");
	});
	$("#grzx_tx").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_tx.html");
	});
	$("#grzx_jf").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_cz.html");
	});
	$("#grzx_zfsz").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_tx.html");
	});
	$("#grzx_smrz").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_smrz.html");
	});
	$("#grzx_yhk").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_yhkbd.html");
	});

	$("#grzx_mmxg").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_mmxg.html");
	});
	$("#grzx_grzl").bind("click", function() {
		$("#Iframe_grzx").attr("src", "grzx_zhzx_grzl.html");
	});

	$("#bank_tab li").click(
			function() {
				$("#bank_tab li").eq($(this).index()).addClass(
						"grzx_gtable_current").siblings().removeClass(
						'grzx_gtable_current');
				$("#grzx_bank_cot").children().hide().eq($(this).index())
						.show();
			});

	// 我的服务
	$("#wdfw_left_tab li").click(
			function() {
				$("#wdfw_left_tab li").eq($(this).index()).addClass("current")
						.siblings().removeClass('current');
				$("#wdfw_right_tab").children().hide().eq($(this).index())
						.show();
			});

});
function paySubmit() {
	if ($("#money").val() > 0) {
		var url = $('input[name="pay_radio"]:checked').val();
		if (url != null) {
//			window.parent.payConfirm_alert(function() {
//				window.parent.location.href = "/UserCenter/charge#chongzhi"
//			})
			$('#formPay').attr('action',url);     
			$("#formPay").submit();
		}

	}
}
