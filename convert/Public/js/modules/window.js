/**
 * 弹窗
 */
define(['jquery'], function ($){
	function Window(){
		this.conf = {
			title: "系统消息",
			content: 'welcome',
			width: 500,
			height: 300,
			handler: null
		}
	}

	Window.prototype = {
		alert: function(conf){
			var conf = $.extend(this.conf, conf),
				box = $(
					'<div class="windowBox">' +
						'<div class="windowBox_header">' + conf.title + '</div>' + 
						'<div class="windowBox_body">' + conf.content + '</div>' + 
						'<div class="windowBox_footer"><input type="button" value="确定" /></div>' +  
				 	'</div>'
					),
				btn = box.find(".windowBox_footer input");
				box.appendTo('body');
				btn.click(function(){
					conf.handler && conf.handler();
					box.remove();
				});

				box.css({
					width: conf.width + 'px',
					height: conf.height + 'px',
					left: conf.x || ((window.innerWidth - conf.width)/2) + 'px',
					top: conf.y || ((window.innerHeight - conf.height)/2) + 'px',
				});
		},
		prompt: function(){},
		confirm: function(){},
	}

	return {
		Window: Window,
	}
});