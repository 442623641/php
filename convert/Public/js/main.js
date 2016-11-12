require.config({
	paths:{
		jquery: 'jquery/jquery-1.8.0.min',
        window: 'modules/window',
	}
});

require(['jquery', 'window'], function($, w){
    var base_url = 'http://' + location.host + '/sales.php/';
    //登录
	$(".login_btn").click(function(){
            var user = $.trim($("input[name='user']").val());
            var pass = $.trim($("input[name='pass']").val());

            $.ajax({
                url: base_url + 'User/doLogin',
                type: 'POST',
                dataType: 'json',
                data: {
                    user: user,
                    pass: pass,
                },
                success: function(json){
                    if (json.fail == 0) {
                        new w.Window().alert({
                        	title: '小康便民提示您',
                        	content: '登录成功',
                        	width: 300,
                        	height: 150,
                            y: 50,
                        	handler: function(){
                        		location.href= base_url + 'Home/index';
                        	}
                        });
                    } else {
                         new w.Window().alert({
                        	title: '登录失败',
                        	content: '请重新登录',
                        	width: 300,
                        	height: 150,
                            y: 50,
                        });
                    }
                }
            })
    });
    
    /**左侧菜单 start**/
    $('.nav a').click(function() {
        $('.nav a').removeClass("curennt");
        var index=$('.nav a').index(this);
        var element=$(".nav a").eq(index);
        element.addClass("curennt");
    })
    
    $('.nr a').click(function() {
        $('.nr a').removeClass("cur");
        var index=$('.nr a').index(this);
        var element=$(".nr a").eq(index);
        element.addClass("cur");
    })

    function wh(){
        var h = $(window).height();
        $(".maincontent").height(h-105);
    }

    wh();

    $(window).resize(function(){
        wh();
    });

    $(".nr:not(:first)").hide();
    $(".nav").click(function(){
        $(this).next("ul").slideToggle().siblings("ul").slideUp();
        $(this).next(".nr").find("a:first").addClass("cur");
    });
    /**左侧菜单 end**/

    /**修改密码**/
    $(".mod_pass").click(function(){
        var pass = $("input[name='pass']");
        var newpass = $("input[name='newpass']");
        var repass = $("input[name='repass']");

        obj = {
            pass: pass,
            newpass: newpass,
            repass: repass
        }
        errorTip(obj);

        $.ajax({
            url: base_url + 'UserApi/changePass',
            type: 'POST',
            dataType: 'json',
            data: {
                pass: $.trim(pass.val()),
                newpass: $.trim(newpass.val()),
            },
            success: function(json){
                if (json.fail == 0) {
                    //修改成功
                    new w.Window().alert({
                            title: '小康便民提示您',
                            content: '密码修改成功',
                            width: 300,
                            height: 150,
                            y: 50
                    });
                } else {
                    //修改失败
                    new w.Window().alert({
                            title: '小康便民提示您',
                            content: '密码修改失败',
                            width: 300,
                            height: 150,
                            y: 50
                    });
                }
            }
        })
    });

    function errorTip(obj){
        var err = false;
        for (i in obj) {
            if ($.trim(obj[i].val()) == '') {
                $("<span>" + obj[i].attr("rel") + "不能为空</span>").appendTo(obj[i].parent('td')).delay(1000).fadeOut(600);
                err = true;
            }
        }

        if ($.trim(obj['newpass'].val()) != $.trim(obj['repass'].val())) {
            $("<span>两次输入密码不一致!</span>").appendTo(obj['repass'].parent('td')).delay(1000).fadeOut(600);
            err = true;
        }

        if (err == true) {
            return false;
        }
    }

});