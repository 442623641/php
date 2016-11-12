<?php
	//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
	//订单名称
	$pay_config['orderName']= "小康便民";
	//订单描述
	$pay_config['orderDesc']= "小康便民充值";
	//商品展示地址
	$pay_config['show_url']= $_SERVER["HTTP_HOST"];
	$pay_config['return_url']= "http://".$_SERVER["HTTP_HOST"].":".$_SERVER['SERVER_PORT']."/UserCenter/charge#chongzhi";
	$pay_config['return_url_wap']= "http://".$_SERVER["HTTP_HOST"].":".$_SERVER['SERVER_PORT']."/UserCenter/charge#chongzhi";
	$pay_config['api_url'] = 'http://api.xiangw.com.cn/';
	$pay_config['lottery_url'] = 'http://www.xklottery.com:82/';
?>

        