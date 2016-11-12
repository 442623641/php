/**
 * 订单
 * @type {Object}
 */
require.config({
    paths: {
        jquery: '../../jquery/jquery-1.8.0.min',
        jquery_ui: '//cdn.bootcss.com/jqueryui/1.11.4/jquery-ui.min',
        date_cn: '../../jquery/datepicker_cn',
    },
});

require(['jquery', 'jquery_ui', 'date_cn', 'list'], function($, ui, date, list){
    
    //日期控件
    initdatepicker_cn();
    $('.datepicker').datepicker({
        inline: true
    });

    //订单记录
    var flag = true;
    new list.List().getData({
        page: 1,
        pageSize: 10,
        pageBar: 8,
        handler: function(){
            $(".fr a[val='first']").addClass('current');
        }
    })

    //搜索记录
    var start = '',
        end = '',
        type = '';
    $(".search_btn").click(function(){
        flag = false;
        start = $("input[name='start']").val();
        end = $("input[name='end']").val();
        type = $("select[name='type'] option:selected").attr("value");
        new list.List().getData({
            url: 'UserApi/search',
            page: 1,
            pageSize: 10,
            pageBar: 8,
            start: start,
            end: end,
            type: type,
            handler: function(){
                $(".fr a[val='first']").addClass('current');
            }
        })
    })

    $(".fr a").live("click", function(){
        var page = parseInt($(this).attr("rel"));
        var text = $(this).attr('val');
        var conf = {};

        if (flag) {
            conf = {
                page: page,
                pageSize: 10,
                pageBar: 8,
                handler: function(){
                    $(".fr a[val='"+text+"']").addClass('current');
                }
            }
        } else {
            conf = {
                url: 'UserApi/search',
                page: page,
                pageSize: 10,
                pageBar: 8,
                start: start,
                end: end,
                type: type,
                handler: function(){
                    $(".fr a[val='"+text+"']").addClass('current');
                }
            }
        }

        new list.List().getData(conf);
    })

    //全选
    $(".all").toggle(function(){
        $("input[name='order[]']").attr("checked", "checked");
    }, function(){
        $("input[name='order[]']").attr("checked", false);
    });

})