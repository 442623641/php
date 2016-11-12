<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {
	#开启web端
//protected $_web='default/';
	#开启手机端
	public function switch_web_on(){
		$this->_ci_view_paths = array(FCPATH . WEB_DIR => TRUE);
	}
	public function switch_themes_on(){
		$this->_ci_view_paths = array(FCPATH . THEMES_DIR . 'default/'	=> TRUE);
	}
	public function switch_themes_off(){
		//just do nothing
	}
	
// admin/category/index
/**
 * 成功提示函数
 * @param  [type] $url [跳转地址]
 * @param  [type] $msg [提示信息]
 * @return [type]      [description]
 */
function success($url, $msg){
	header('Content-Type:text/html;charset=utf-8');
	$url = site_url($url);
	echo "<script type='text/javascript'>alert('$msg');location.href='$url'</script>";
	die;
}
/**
 * 错误提示函数
 * @param  [type] $msg [提示信息]
 * @return [type]      [description]
 */
function error($msg){
	header('Content-Type:text/html;charset=utf-8');
	echo "<script type='text/javascript'>alert('$msg');window.history.back();</script>";
	die;
}
	#判断是否是手机端
	function isMobile()
	{
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA']))
		{
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT']))
		{
			$clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
            	return true;
            }
		}
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT']))
		{
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
			{
				return true;
			}
		}
		return false;
	}
	/**
	 *
	 * 根据php的$_SERVER['HTTP_USER_AGENT'] 中各种浏览器访问时所包含各个浏览器特定的字符串来判断是属于PC还是移动端
	 * @author           discuz3x
	 * @lastmodify    2014-04-09
	 * @return  BOOL
	 */
	function checkmobile() {
		global $_G;
		$mobile = array();
		//各个触控浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
		static $touchbrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
    'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
    'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
    'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
    'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
    'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
    'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
		//window手机浏览器数组【猜的】
		static $mobilebrowser_list =array('windows phone');
		//wap浏览器中$_SERVER['HTTP_USER_AGENT']所包含的字符串数组
		static $wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
   'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
   'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');
		$pad_list = array('pad', 'gt-p1000');
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if($this->dstrpos($useragent, $pad_list)) {
			return false;
		}
		if(($v = $this->dstrpos($useragent, $mobilebrowser_list, true))){
			$_G['mobile'] = $v;
			return '1';
		}
		if(($v = $this->dstrpos($useragent, $touchbrowser_list, true))){
			$_G['mobile'] = $v;
			return '2';
		}
		if(($v = $this->dstrpos($useragent, $wmlbrowser_list))) {
			$_G['mobile'] = $v;
			return '3'; //wml版
		}
		$brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
		if($this->dstrpos($useragent, $brower)) return false;
		$_G['mobile'] = 'unknown';
		//对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器
		if(isset($_G['mobiletpl'][$_GET['mobile']])) {
			return true;
		} else {
			return false;
		}
		//对于未知类型的浏览器，通过$_GET['mobile']参数来决定是否是手机浏览器
		if($this->isMobile()) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 判断$arr中元素字符串是否有出现在$string中
	 * @param  $string     $_SERVER['HTTP_USER_AGENT']
	 * @param  $arr          各中浏览器$_SERVER['HTTP_USER_AGENT']中必定会包含的字符串
	 * @param  $returnvalue 返回浏览器名称还是返回布尔值，true为返回浏览器名称，false为返回布尔值【默认】
	 * @author           discuz3x
	 * @lastmodify    2014-04-09
	 */
	protected function dstrpos($string, $arr, $returnvalue = false) {
		if(empty($string)) return false;
		foreach((array)$arr as $v) {
			if(strpos($string, $v) !== false) {
				$return = $returnvalue ? $v : true;
				return $return;
			}
		}
		return false;
	}
}