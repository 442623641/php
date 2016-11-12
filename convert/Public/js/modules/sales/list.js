/**
 * 订单记录
 * yulei<464001224@qq.com>
 * 2015-7-15
 */
define(['jquery'], function($){
	function List(){
		this.conf = {
			url: 'UserApi/order',
			page: 1,
			pageSize: 10,
			pageBar: 8,
			base_url: 'http://' + location.host + '/sales.php/',
			handler: null,
		}
	}

	List.prototype = {

		//获取数据
		getData: function(conf){
			var conf = $.extend(this.conf, conf),
				url = conf.base_url + conf.url;
			$.ajax({
				url: url,
				type: 'GET',
				dataType: 'json',
				data: {
					page: conf.page,
					pageSize: conf.pageSize,
				},
				success: function(json){
					if (json.fail == 0) {
						var list = new List();
						var target = $(".list");
						target.empty();
						list.rendData(json.data, target);

						var pageBar = $(".page");
						pageBar.empty();
						list.pageBar(conf, json.total, json.pageSize, url, pageBar);
					} else if (json.fail == 2){
						//显示暂无数据
					} else {
						//提示错误
					}
				}
			})
		},

		//数据渲染
		rendData: function(data, target){
			var tr = '';
			$.each(data, function(index, el){
				tr += '<tr class="tbg"><td>' + el.user + '</td>' +
              			'<td>' + el.app + '</td>' +
              			'<td>' + el.shop + '</td>' + 
              			'<td>' + el.id + '</td>' +
              			'<td> <a href="dpyj.html">查看业绩</a></td>' + 
              		'</tr>';
			});

			$(tr).appendTo(target);
		},

		//分页条
		pageBar: function(conf, total, pagesize, url, target){
			var page_num = Math.ceil(total/conf.pageSize),
				curpage = (conf.page > page_num) ? page_num : (conf.page),
				pagems = '<div class="fl">共' + total + '条经销商信息 每页' + pagesize + '条  共' + page_num + '页  当前'+ curpage + '/' + page_num + '</div>';
			if (conf.page < 1) conf.page = 1;
			if (conf.page == 1) {
				pagems += '<div class="fr">' +
		            			'<a href="javascript:void(0);" class="split_page" val="first" rel="1">首页</a>' +
		                		'<a href="javascript:void(0);" class="split_page" val="prev" rel="1"><span class="gray">上一页</span></a>';
		    } else {
		    	pagems += '<div class="fr">' +
		            			'<a href="javascript:void(0);" class="split_page" val="first" rel="1">首页</a>' +
		                		'<a href="javascript:void(0);" class="split_page" val="prev"  rel="' + (parseInt(conf.page)-1) + '"><span class="gray">上一页</span></a>';
		    }

	     
        	var pageLast = ((conf.page+conf.pageBar-1) > page_num) ? page_num : (conf.page+conf.pageBar-1);
        		i = pageLast - conf.pageBar + 1,
        		i = i > 0 ?  i : 1;
        	for (; i <= pageLast; i++) {
        		pagems += '<a class="split_page" href="javascript:void(0);" val="' + i + '" rel="'+ i +'">'+ i +'</a>';
        	}

	        if (conf.page > page_num) conf.page = page_num;

	        if (conf.page == page_num) {
	        	pagems += '<a href="javascript:void(0);" class="split_page" val="next" rel="' + page_num + '">下一页</a>' +
	                '<a href="javascript:void(0);" class="split_page" val="last" rel="' + page_num + '">尾页</a>';
	        } else {
	        	pagems += '<a href="javascript:void(0);" class="split_page" val="next" rel="' + (parseInt(conf.page)+1) + '">下一页</a>' +
	                '<a href="javascript:void(0);" class="split_page" val="last" rel="' + page_num + '">尾页</a>';
	        }
	        
	        pagems += '</div>' +
	                '<div class="clear"></div>';

			$(pagems).appendTo(target);
			conf.handler && conf.handler();
		}
	}

	return {
		List: List
	}
});

              
               