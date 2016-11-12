/**
 * 经销商
 * @type {Object}
 */
require.config({
	paths: {
		jquery: '../../jquery/jquery-1.8.0.min',
		jquery_ui: '//cdn.bootcss.com/jqueryui/1.11.4/jquery-ui.min',
		date_cn: '../../jquery/datepicker_cn',
	},
});

require(['jquery', 'list'], function($, list){
	
    //信息中心
    new list.List().getData({
    	page: 1,
    	pageSize: 10,
    	pageBar: 8,
        handler: function(){
            $(".fr a[val='first']").addClass('current');
        }
    })

    $(".fr a").live("click", function(){
    	var page = parseInt($(this).attr("rel"));
        var text = $(this).attr('val');
        var conf = {};

        conf = {
            page: page,
            pageSize: 10,
            pageBar: 8,
            handler: function(){
                $(".fr a[val='"+text+"']").addClass('current');
            }
        }
    	new list.List().getData(conf);
    })

    //全选
    $(".all").toggle(function(){
        $("input[name='lists[]']").attr("checked", "checked");
    }, function(){
        $("input[name='lists[]']").attr("checked", false);
    });
})