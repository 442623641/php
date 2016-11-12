/**
 * yulei 2015-06-23
 * 等待重构
 */
var baseurl = "http://www.xkbm.com/"
var url = baseurl+"UserApi/records";
var searchUrl = baseurl+"UserApi/search";

//搜索
function search(obj){
	var total, curPage, pageSize, totalPage;
	$("#jyjl_tab_ul_li li[val="+obj.search_type+"]").addClass("jyjl_tab_current").siblings().removeClass("jyjl_tab_current");

	$.ajax({
		url: searchUrl,
		type: 'GET',
		dataType: 'JSON',
		data: {
			pageNum: obj.page-1,
			type: obj.search_type,
			start: obj.start_time,
			end: obj.end_time,
		},
		success:function(json){
			$("#jyjl_tab div[style!='display: none;'] tbody").empty();
			total = json.total; //总记录数
			pageSize = json.pageSize; //每页记录数
			curPage = obj.page; //当前页
			totalPage = json.totalPage; //总页数
			var tr = "";
			var list = json.list;
			$.each(list, function(index, array){//遍历json数据列
				var status = "";
				switch (list[index].state_rcs){
					case '0':
						status = '交易失败';
						break;
					case '1':
						status = "正在交易";
						break;
					case '2':
						status = "交易成功";
						break;
				}

				tr += "<tr><td>"+ list[index].finish_date + " " + list[index].finish_time +"</td>"+
				"<td>"+list[index].app + "</td>"+
				"<td>"+list[index].orderID +"</td>"+
				"<td>"+list[index].shop +"</td>"
				+"<td class='org'>"+list[index].cost+"</td>"
				+"<td class='org'>"+status+"</td>"
				+"<td><a href='#' class='blue'>详情</a></td>";
			});
			pageSize = list.length; //每页记录数

			$("#jyjl_tab div tbody").append(tr);
		},
		complete:function(){ //生成分页条
			getPageBar(curPage, totalPage, total, pageSize);
		},
		error:function(){
			alert('数据加载失败');
		}
	});
	
}

//获得分页数据
function getData(page, url, type){
	var total, curPage, pageSize, totalPage;
	$.ajax({
		type:'POST',
		url: url,
		data: {
			'pageNum':page-1,
			'type': type,
		},
		dataType: 'json',
		success:function(json){
			$("#jyjl_tab div[style!='display: none;'] tbody").empty();
			total = json.total; //总记录数
			curPage = page; //当前页
			totalPage = json.totalPage; //总页数
			var tr = "";
			var list = json.list;
			$.each(list, function(index, array){//遍历json数据列
				var status = "";
				switch (list[index].state_rcs){
					case '0':
						status = '交易失败';
						break;
					case '1':
						status = "正在交易";
						break;
					case '2':
						status = "交易成功";
						break;
				}
				var time = new Date(list[index]['charge_time'] * 1000);

				tr += "<tr><td>"+ list[index].finish_date + " " + list[index].finish_time +"</td>"+
				"<td>"+list[index].app+"</td>"+
				"<td>"+list[index].orderID+"</td>"+
				"<td>"+list[index].shop+"</td>"
				+"<td class='org'>"+list[index].cost+"</td>"
				+"<td class='org'>"+status+"</td>"
				+"<td><a href='javascript:void(0);' class='blue'>详情</a></td>";
			});
			pageSize = list.length; //每页记录数

			$("#jyjl_tab div tbody").append(tr);
		},
		complete:function(){ //生成分页条
			getPageBar(curPage, totalPage, total, pageSize);
		},
		error:function(){
			alert('数据加载失败');
		}
	});
}

//分页条
function getPageBar(curPage, totalPage, total, pageSize){
	$("#jyjl_tab div[style!='display: none;'] tfoot").empty();
	//页码大于最大页数
	if (curPage > totalPage) {
		curPage = totalPage;
	}

	//页码小于1
	pageStr = '<tr><td colspan="7"><span class="fn-left">共'+total+'条交易纪录 当前页'+pageSize+'条 '+curPage+'/'+totalPage+'</span><div class="fn-right jyjl_table_bot">';
	if (curPage < 1) curPage = 1;
	//第一页
	if (curPage == 1) {
		pageStr += "<a href='javascript:void(0)'>首页</a> &nbsp; &nbsp; <a href='javascript:void(0)'>上一页</a> &nbsp; &nbsp;";
	} else {
		pageStr += "<a href='javascript:void(0)'  rel='1'>首页</a> &nbsp; &nbsp; <a href='javascript:void(0)'  rel='"+(parseInt(curPage)-1)+"'>上一页</a> &nbsp; &nbsp;";

	}

	//最后页
	if (curPage >= totalPage) {
		pageStr += "<a href='javascript:void(0)'>下一页</a> &nbsp; &nbsp;  <a href='javascript:void(0)'>尾页</a> &nbsp; &nbsp; &nbsp; &nbsp;  ";
	} else {
		pageStr += "<a href='javascript:void(0)'  rel='"+(parseInt(curPage)+1)+"'>下一页</a> &nbsp; &nbsp;  <a href='javascript:void(0)'  rel='"+totalPage+"'>尾页</a> &nbsp; &nbsp; &nbsp; &nbsp;  ";
	}

	pageStr += '转至第 <input class="jyjl_table_bot_input" type="text"/> 页&nbsp;<a class="jyjl_table_bot_go" href="javascript:void(0);">GO</a></div><div class="clearfix"></div></td></tr>';

	$("#jyjl_tab div[style!='display: none;'] tfoot").html(pageStr);
}


$(function($){
	var lurl = window.location.href;
	if (lurl.indexOf("#") > -1) {

		var mods = {
			chongzhi: "1",
			tixian: "2",

		};

		var arrUrl = lurl.split("#");
		var mod = arrUrl[arrUrl.length-1];

		for (i in mods) {
			if (mod.indexOf(i) > -1) {
				flag = true;
				type = mods[i];
				$("#jyjl_tab_ul_li li[val="+type+"]").addClass("jyjl_tab_current").siblings().removeClass("jyjl_tab_current");
				getData(1, url, type);
			}
		}


	} else {
		
		//获得数据类型,所有记录...
		var type = $(".jyjl_tab_current").attr('val');

		//记录显示,默认是所有记录
		getData(1, url, type);

	}

	//搜索或不是搜索
	var flag = true;


	//点击不同tab显示对应记录
	$("#jyjl_tab_ul_li li").click(function(){
		flag = true;
		type = $(this).attr('val');
		getData(1, url, type);
	});

	//搜索和重置
	var obj = {};
	$('.jyjl_tit_search').click(function(){
		obj.page = 1;
		obj.search_type = $('select[name="search_type"] option:selected').attr('value');
		obj.start_time = $("input[name='start_time']").val();
		obj.end_time = $("input[name='end_time']").val();
		search(obj);

    // var date =  new Date(newstr); 
    // var time_str = date.getTime().toString();
    // return time_str.substr(0, 10);

	});

	//go跳转
	$(".jyjl_table_bot_go").live('click', function(event) {
		var go = parseInt($.trim($(".jyjl_table_bot_input").val()));
		if (isNaN(go)) {
			alert('请正确输入数字');
			return false;
		}

		if (go) {
			if (flag) {
				getData(go, url, type);
			} else {
				obj.page = go;
				search(obj);
			}
		}
	});

	$(".jyjl_table_bot a").live("click", function() {
		var rel = $(this).attr("rel");
		if (rel) {
			if (flag) {
				getData(rel, url, type);
			} else {
				obj.page = rel;
				search(obj);
			}
		}
	});



	$('.jyjl_tit_reset').click(function(){
		$(".jyjl_tit input").val('');

		//使select第一条选中
		$("select[name='search_type']").find("option[value='1']").attr("selected", true);
	});

});


