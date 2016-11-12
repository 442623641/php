<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* *
 * 支付配置文件
 * 编写：Leo
 * 日期：2015-06-28
 * 说明：
 * 该代码仅供支付接口使用
 * 提示：如何获取安全校验码和合作身份者id
**/ 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，以2088开头的16位纯数字
$config['alipay']['partner']= "2088511891258053";
//收款支付宝账号
$config['alipay']['seller_email']	= '2712397479@qq.com';

//安全检验码，以数字和字母组成的32位字符
$config['alipay']['key']			= 'h4cohcuf4uk0y1rlv0l11j1ezso8a34a';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//签名方式 不需修改
$config['alipay']['sign_type']    = strtoupper('RSA');
//字符编码格式 目前支持 gbk 或 utf-8
$config['alipay']['input_charset']= strtolower('utf-8');
//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$config['alipay']['cacert']    = getcwd().'/cacert.pem';
//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$config['alipay']['transport']    = 'http';
//订单名称
$config['alipay']['orderName']= "小康便民";
//订单描述
$config['alipay']['orderDesc']= "小康便民充值";
//商品展示地址
$config['alipay']['show_url']= base_url();