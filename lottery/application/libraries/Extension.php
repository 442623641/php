<?php
class Extension {
	#输出
	static function export($msg,$code=1) {
		$ret['fail']=1;
		$ret['mess']=$msg;
		echo self::decodeUnicode(json_encode($ret));
		exit;
	}

	/// <summary>
	/// 排列循环方法
	/// </summary>
	/// <param name="m"></param>
	/// <param name="n"></param>
	/// <returns></returns>
	static function fac($m, $n)
	{
		$t = 1;
		$i = $m;
		while ($i != $m - $n)
		{
			try
			{
				$t *= $i;
			}
			catch (Exception $e){
				return 0;
			}
			--$i;
		}
		return $t;
	}

	/// <summary>
	/// 组合
	/// </summary>
	/// <param name="N"></param>
	/// <param name="R"></param>
	/// <returns></returns>
	static function combine($m, $n)
	{
		if ($m < $n || $n < 0) return 0;
		return (int)(self::fac($m, $n) / self::fac($n, $n));
	}

	/// <summary>
	/// 倍数总和
	/// </summary>
	/// <param name="multi_array"></param>
	/// <param name="sort_key"></param>
	/// <returns></returns>
	static function totalMultiple($array){
		$total=0;
		foreach ($array as $item){
			$total+=$item[1];
		}
		return 	$total;
	}
	static function decodeUnicode($str)
	{
	    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
	        create_function(
	            '$matches',
	            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
	        ),
	        $str);
	}
	/**
	 * POST请求接口
	 * @param  [type] $class     接口类名
	 * @param  [type] $function  接口方法名
	 * @param  [type] $parameter 传递post参数
	 * @param  [type] $type      请求类型HTTP, HTTPS
	 * @return [type]            请求结果
	 */
	static function params_valid($params) {

		foreach ($params as $key => $val){
			//echo $key.'ddd'.$val.'ddd'.$_REQUEST[$key].'</br>';
			if(!isset($_REQUEST[$key])){
				self::export($val.'不能为空');
			}
		}
		return;
	}
	static function getBetCount($lotteryID,$text){
		#校验投注号码,金额
		switch ($lotteryID){
			#11选5
			case 0:
				//$this->load->library('Pool_11X5');
				require_once 'Pool_11X5.php';
				$pool=new Pool_11X5();
				$pool->checkText(json_decode($text,true));
				return $pool->count;
			#11选5
			case 5:
				//$this->load->library('Pool_11X5');
				require_once 'Pool_11X5.php';
				$pool=new Pool_11X5();
				$pool->checkText(json_decode($text,true));
				return $pool->count;
			#11选5
			case 6:
				//$this->load->library('Pool_11X5');
				require_once 'Pool_11X5.php';
				$pool=new Pool_11X5();
				$pool->checkText(json_decode($text,true));
				return $pool->count;
			#排列3
			case 1:
				//$this->load->library('Pool_PiaLie3');
				require_once 'Pool_PaiLie3.php';
				$pool=new Pool_PaiLie3();
				$pool->checkText(json_decode($text,true));
				return $pool->count;
			default:
				self::export('彩票类型不存在');
		}
	}
	function encrypt($data, $key) {
		$prep_code = serialize($data);
		$block = mcrypt_get_block_size('des', 'ecb');
		if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
			$prep_code .= str_repeat(chr($pad), $pad);
		}
		$encrypt = mcrypt_encrypt(MCRYPT_DES, $key, $prep_code, MCRYPT_MODE_ECB);
		return base64_encode($encrypt);
	}

	function decrypt($str, $key) {
		$str = base64_decode($str);
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
			$str = substr($str, 0, strlen($str) - $pad);
		}
		return unserialize($str);
	}
}