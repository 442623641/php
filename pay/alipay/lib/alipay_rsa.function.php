<?php
/* *
 * RSA
 * 详细：RSA加密
 * 版本：1.0
 * 日期：2015-08-22
 * 说明：
 * 该代码仅供学习和研究支付宝接口使用.
 */
/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * return 签名结果
 */
function rsaSign($prestr) {
	//$public_key= file_get_contents('rsa_private_key.pem');
$private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAMZZ6MpGOea/fdP7
T+/3905Xy5luL0zbO8Si0vT0L1SSrsISmjKKvP3nwFHKFIxV18nMWuAF3uvFLhoF
RyPSHdLL2/39meNhy+CpmzzUtESS0kYYuMwlb63ceFHf8YFN2p3ZU9YjzLjKKoNw
k/nfFqB8YCHyW1ZSSjK4c/bKiuhZAgMBAAECgYB+i9YtVuiSAxHwMKIrY3Rwkyre
VKHw0t4q8gbNKQ0ZVAwS3XNrU4CILVdR1y+M6SenI63Gx3gVy9NB3qQogmFrfkZ8
9njgLPidwHAr+rIWLaekSFCTYWedlIGdagM5CShcSCO3zqh237drJb5bHl34VqH9
JoM6lvFZDnMxEkTGAQJBAPuHugOFvPE9NsapS6DRqx07P7U2gQ1dXnc9FChYNXKv
TcoL6/ySGacsNf1P15w8N8Vh0CIgIT46aw9YYtZ7vrECQQDJ4EIiWr2Cv7nfVUxD
/MACU34ZUl39FsxSNQiAXmC4QvY5pk06Qapgc1/cLLDqEXKB4jSqosu05EKawTXs
0r4pAkBSvUTtH7MVT+OS2eGs1wuIpBLC01kEQnBunMLiupFqVkwqaF2KSazyltJz
ry3nkM9vuEs0zojg5WqOM67fivkhAkEAlEmSysF0q3JCCncRim9Ca3MBEYCbYak5
dLlYAVNSIIevbjFmUQCjMi4NoCGD4qvmPNn4bc8fa0SwWBTMk6VJ4QJAOTvYrstL
xIv+DL0XiQVk6tOqYp0wk0PTJMNSC/i0fGQYzxD9Ui8BvJvfTJ4dl6H2VEiQU+m3
CxyHLtKpdk5hyQ==
-----END RSA PRIVATE KEY-----';
	$pkeyid = openssl_get_privatekey($private_key);
	openssl_sign($prestr, $sign, $pkeyid);
	openssl_free_key($pkeyid);
	$sign = base64_encode($sign);
	return $sign;
}
/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * return 签名结果
 */
function rsaVerify($prestr, $sign) {
	$sign = base64_decode($sign);
	//$public_key= file_get_contents('rsa_public_key.pem');
		$public_key = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRA
FljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQE
B/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5Ksi
NG9zpgmLCUYuLkxpLQIDAQAB
-----END PUBLIC KEY-----";
	$pkeyid = openssl_get_publickey($public_key);
	if ($pkeyid) {
		$verify = openssl_verify($prestr, $sign, $pkeyid);
		openssl_free_key($pkeyid);
	}
	if($verify == 1){
		return true;
	}else{
		return false;
	}
}
?>