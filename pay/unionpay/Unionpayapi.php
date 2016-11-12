<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once 'func/common.php';
include_once 'func/SDKConfig.php';
include_once 'func/secureUtil.php';
include_once 'func/httpClient.php';
include_once 'func/log.class.php';
require_once '../Common.php';
require_once '../Config.php';
		
/**
 * 消费交易-前台 
 */
/**
 *	以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考
 */
//// 初始化日志
//$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
//$log->LogInfo ( "============处理前台请求开始===============" );
//// 初始化日志
   //商户订单号
   		$common=new Common();
        $orderId =$common->make_seek($_POST['userID']); //.$_POST['WIDout_trade_no'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $pay_config['orderName'];//$_POST['WIDsubject'];
        //必填

        //付款金额
        $money = $_POST['money'];
        //必填

        //订单描述
        $body = $pay_config['orderDesc'];//$_POST['WIDbody'];   
$params = array(
		'version' => '5.0.0',				//版本号
		'encoding' => 'utf-8',				//编码方式
		'certId' => getSignCertId (),			//证书ID
		'txnType' => '01',				//交易类型	
		'txnSubType' => '01',				//交易子类
		'bizType' => '000000',				//业务类型
		'frontUrl' => $pay_config['return_url'],// $_SERVER['HTTP_HOST'].SDK_FRONT_NOTIFY_URL.$money,  		//前台通知地址，控件接入的时候不会起作用
		'backUrl' => SDK_BACK_NOTIFY_URL,		//后台通知地址	
		'signMethod' => '01',		//签名方法
		'channelType' =>'07',// '07',		//渠道类型，07-PC，08-手机
		'accessType' => '0',		//接入类型
		'merId' => SDK_MERID,		        //商户代码，请改自己的测试商户号
		'orderId' => $orderId,	//商户订单号
		'txnTime' => date('YmdHis'),	//订单发送时间
		'txnAmt' => $money*100,		//交易金额，单位分
		'currencyCode' => '156',	//交易币种
		'defaultPayType' => '0001',	//默认支付方式	
		'orderDesc' => $body,  //订单描述，网关支付和wap支付暂时不起作用
		'reqReserved' =>$_POST['userID'], //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
		);
// 签名
sign ( $params );

// 前台请求地址
$front_uri = SDK_FRONT_TRANS_URL;
// 构造 自动提交的表单
$html_form = create_html ( $params, $front_uri );

echo $html_form;
