/**
 * Leo 2015-07-14 店铺订单处理
 */
$(function() {
	   initdatepicker_cn();
       $('.datepicker').datepicker({
           inline: true
       });
    $("#checkall").click(
    	    function(){
    	    	$(".checkbox").prop("checked", this.checked);
        	    });	
});

function scscms_confirm(msg,url,id){
	parent.scscms_alert(msg,"confirm",function(){location.href=url+"\/"+id},3);
}
/**
 * 分页初始化,form提交;设置分页条id=page，表单提交请先设置form id=queryForm， pageIndex 当前页面 totalPage
 * 总页数 url 页面连接
 */
function pageInitial(pageIndex, totalPage,url) {
	$("#page").myPagination(
			{
				currPage : pageIndex == null ? 1 : pageIndex,
				pageCount : totalPage == null ? 1 : totalPage,
				cssStyle :'msdn',
				ajax : {
					// 自动传入当前点击页数
					onClick : function(page) {
						url=url==null? $('.input_sel').children('option:selected').val():url;
						// 开启提示代码,需导入msgbox.js文件与样式.
						ZENG.msgbox.show(" 正在加载" + page + "页，请稍后...", 6, 1000);
						var varParams = "\/" + page ;
						// 如果是多条件查询，则需序列化表单，或自己组装参数，以下提供一示例。
						if ($("#queryForm").length > 0) {
							var formData = $("#queryForm").serialize(); // 序列化表单
							formData = decodeURIComponent(formData, true); // 解码
							varParams +="?";
							varParams += formData;
						}						
						location.href = url + varParams;
					}
				}
			});
}
function submit() {
	ZENG.msgbox.show(" 正在加载"
			+ $('.input_sel').children('option:selected').html() + "订单，请稍后...",
			6, 1000);
	var url = $('.input_sel').children('option:selected').val();
	$('#queryForm').attr("action", url);
	$('#queryForm').submit();
}
function submit_money() {
	ZENG.msgbox.show(" 正在加载"
			+ $('.input_sel').children('option:selected').html() + "列表，请稍后...",
			6, 1000);
	$('#queryForm').submit();
}
