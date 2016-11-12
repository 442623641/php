<?php
//$this->load  'Extension';
/*
 *11选5算法
 */
class Pool_PaiLie3
{	
	public $count=0;
 	function __construct(){
		require_once 'Extension.php';
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [[子玩法类型,选号,注数,金额],[子玩法类型,选号,注数,金额]..]
	 */
 	public function checkText($text){
 		//var_dump($text);
 		foreach ($text as $item){
			$this->count+=$this->checkBet($item);
		}
		return $this->count;
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [子玩法类型,选号,注数,金额]
	 */
	function checkBet($item){
		if(count($item)!=4){
			Extension::export('投注格式有误');
		}
		if($item[2]*2!=$item[3]){
			Extension::export('投注金额计算错误');
		}
		$count=$this->getCount($item);
//		echo '计算注数：'.$count.'</br>';
//		echo '传入注数：'.$text[3].'</br>';
		if($count!=$item[2]){
			Extension::export('投注注数计算错误');
		}
		return $count;
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [子玩法类型,模式（0:单式，1:复式，2:胆拖）,选号,注数,金额]
	 */
	protected function  getCount($text)
	{
		//var_dump($text);
			switch($text[0]){
					#任选2
				case 0:
					return $this->getCountForZhiXuan($text[1]);
					#任选3
				case 1:
					return $this->getCountForZuSan($text[1],3);
					#任选4
				case 2:
					return $this->getCountForZuLiu($text[1],4);
					#任选5
				default:
					Extension::export('玩法类型不存在');
			}
	}
	/// <summary>
	/// 获取投注号码
	/// </summary>
	/// <param name="value"></param>
	/// <param name="type"></param>
	/// <returns></returns>
	private function  getTouZhuNumbers($text,$index=1)
	{
		
		$touZhuNumbers = explode(',',$text);
		//var_dump($touZhuNumbers);
		if (count($touZhuNumbers) < $index)
		{
			Extension::export('投注号码有误');
		}
		if (max($touZhuNumbers)> 9||min($touZhuNumbers)<0)
		{
			Extension::export('号码范围在0-9之间');
		}
		if (count(array_unique($touZhuNumbers))<count($touZhuNumbers))
		{
			Extension::export('号码内存在重复数据');
		}
		return $touZhuNumbers;
	}

	/// <summary>
	/// 获取任选的投注数
	/// </summary>
	/// <param name="$text"></param>
	/// <param name="$n"></param>
	/// <returns></returns>
	private function getCountForZhiXuan($text)
	{
		
		$touZhuNumbers = explode('|',$text);
		//var_dump($touZhuNumbers);
		if (count($touZhuNumbers) != 3)
		{
			Extension::export('投注号码有误');
		}
		$touZhuNumbers[0]=$this->getTouZhuNumbers($touZhuNumbers[0]);
		$touZhuNumbers[1]=$this->getTouZhuNumbers($touZhuNumbers[1]);
		$touZhuNumbers[2]=$this->getTouZhuNumbers($touZhuNumbers[2]);
		return count($touZhuNumbers[0])*count($touZhuNumbers[1])*count($touZhuNumbers[2]);      
	}
	/// <summary>
	/// 获取投注-组三
	/// </summary>
	/// <param name="value"></param>
	/// <returns></returns>
	private function getCountForZuSan($text)
	{
		$touZhuNumbers=$this->getTouZhuNumbers($text,2);
		return Extension::combine(count($touZhuNumbers),2)*2;
	}
	/// <summary>
	/// 获取投注-组六
	/// </summary>
	/// <param name="value"></param>
	/// <returns></returns>
	private function getCountForZuLiu($text)
	{
		$touZhuNumbers=$this->getTouZhuNumbers($text,3);
		return Extension::combine(count($touZhuNumbers),3);
	}

	
}
?>
