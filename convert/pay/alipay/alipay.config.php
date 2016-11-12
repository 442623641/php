<?php
/* *
 * 配置文件
 * 编写：Leo
 * 日期：2015-06-28
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
 * 提示：如何获取安全校验码和合作身份者id
 * 1.用您的签约支付宝账号登录支付宝网站(www.alipay.com)
 * 2.点击“商家服务”(https://b.alipay.com/order/myorder.htm)
 * 3.点击“查询合作者身份(pid)”、“查询安全校验码(key)”
	
 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，以2088开头的16位纯数字
$alipay_config['partner']		= "2088511891258053";
//收款支付宝账号
$alipay_config['seller_email']	= '2712397479@qq.com';

//安全检验码，以数字和字母组成的32位字符
$alipay_config['key']			= 'h4cohcuf4uk0y1rlv0l11j1ezso8a34a';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('RSA');
//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'/cacert.pem';
//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
//订单名称
$alipay_config['orderName']= "小康便民";
//订单描述
$alipay_config['orderDesc']= "小康便民充值";
//商品展示地址
$alipay_config['show_url']= "http://xk.xiangw.net";//公钥$alipay_config["rsa_public_key"] = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB-----END PUBLIC KEY-----";//私钥$alipay_config["rsa_private_key"]= '-----BEGIN RSA PRIVATE KEY-----MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMZZ6MpGOea/fdP7T+/3905Xy5luL0zbO8Si0vT0L1SSrsISmjKKvP3nwFHKFIxV18nMWuAF3uvFLhoFRyPSHdLL2/39meNhy+CpmzzUtESS0kYYuMwlb63ceFHf8YFN2p3ZU9YjzLjKKoNwk/nfFqB8YCHyW1ZSSjK4c/bKiuhZAgMBAAECgYB+i9YtVuiSAxHwMKIrY3RwkyreVKHw0t4q8gbNKQ0ZVAwS3XNrU4CILVdR1y+M6SenI63Gx3gVy9NB3qQogmFrfkZ89njgLPidwHAr+rIWLaekSFCTYWedlIGdagM5CShcSCO3zqh237drJb5bHl34VqH9JoM6lvFZDnMxEkTGAQJBAPuHugOFvPE9NsapS6DRqx07P7U2gQ1dXnc9FChYNXKvTcoL6/ySGacsNf1P15w8N8Vh0CIgIT46aw9YYtZ7vrECQQDJ4EIiWr2Cv7nfVUxD/MACU34ZUl39FsxSNQiAXmC4QvY5pk06Qapgc1/cLLDqEXKB4jSqosu05EKawTXs0r4pAkBSvUTtH7MVT+OS2eGs1wuIpBLC01kEQnBunMLiupFqVkwqaF2KSazyltJzry3nkM9vuEs0zojg5WqOM67fivkhAkEAlEmSysF0q3JCCncRim9Ca3MBEYCbYak5dLlYAVNSIIevbjFmUQCjMi4NoCGD4qvmPNn4bc8fa0SwWBTMk6VJ4QJAOTvYrstLxIv+DL0XiQVk6tOqYp0wk0PTJMNSC/i0fGQYzxD9Ui8BvJvfTJ4dl6H2VEiQU+m3CxyHLtKpdk5hyQ==-----END RSA PRIVATE KEY-----';
?>