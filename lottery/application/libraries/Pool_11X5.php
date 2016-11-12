<?php
//$this->load  'Extension';
/*
 *11选5算法
 */
class Pool_11X5
{	
	public $count=0;
 	function __construct(){
		require_once 'Extension.php';
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [[子玩法类型,模式（0:单式，1:复式，2:胆拖）,选号,注数,金额],[子玩法类型,模式（0:单式，1:复式，2:胆拖）,选号,注数,金额]..]
	 */
 	public function checkText($text){
		foreach ($text as $item){
			$this->count+=$this->checkBet($item);
		}
		return $this->count;
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [子玩法类型,模式（0:单式，1:复式，2:胆拖）,选号,注数,金额][["509","0,0,0","1","2"],["509","0,1,9","1","2"]]
	 */
	function checkBet($text){
		if(count($text)!=5){	
			Extension::export('投注格式有误');
		}
		if($text[3]*2!=$text[4]){
			Extension::export('投注金额计算错误');
		}
		$betCount=$this->getCount($text);
		//echo '计算注数：'.$betCount.'</br>';
		//echo '传入注数：'.$text[3].'</br>';
		if($betCount!=$text[3]){
			//var_dump($text);
			Extension::export('投注注数计算错误');
		}
		
		return $betCount;
	}
	/**
	 *
	 * 11选5投注算法
	 * @param $text 单注内容 [子玩法类型,模式（0:单式，1:复式，2:胆拖）,选号,注数,金额]
	 */
	protected function  getCount($text)
	{
		if($text[1]!=2){
			switch($text[0]){
				#任选1
				case 0:
					return $this->getCountForRenXuan($text[2],1);
				#任选2
				case 1:
					return $this->getCountForRenXuan($text[2],2);
					#任选3
				case 2:
					return $this->getCountForRenXuan($text[2],3);
					#任选4
				case 3:
					return $this->getCountForRenXuan($text[2],4);
					#任选5
				case 4:
					return $this->getCountForRenXuan($text[2],5);
					break;
					#任选6
				case 5:
					return $this->getCountForRenXuan($text[2],6);
					break;
					#任选7
				case 6:
					return $this->getCountForRenXuan($text[2],7);
					break;
					#任选8
				case 7:
					return $this->getCountForRenXuan($text[2],8);
					break;
					#前2直选
				case 8:
					return $this->getCountForQian2ZhiXuan($text[2]) ;
					break;
					#前2组选
				case 9:
					return $this->getCountForRenXuan($text[2],2) ;
					break;
					#前3直选
				case 10:
					return $this->getCountForQian3ZhiXuan($text[2],2);
					break;
					#前3组选
				case 11:
					return $this->getCountForRenXuan($text[2],3) ;
					break;
				default:
					Extension::export('玩法类型不存在');
			}
		}else{
			#胆拖
			switch($text[0]){
				#任选2
				case 1:
					return $this->getCountForDanTuo($text[2],2);
					#任选3
				case 2:
					return $this->getCountForDanTuo($text[2],3);
					#任选4胆拖
				case 3:
					return $this->getCountForDanTuo($text[2],4);
					#任选5
				case 4:
					return $this->getCountForDanTuo($text[2],5);
					break;
					#任选6
				case 5:
					return $this->getCountForDanTuo($text[2],6);
					break;
					#任选7
				case 6:
					return $this->getCountForDanTuo($text[2],7);
					break;
					#任选8
				case 7:
					return $this->getCountForDanTuo($text[2],8);
					break;
				default:
					Extension::export('玩法类型不存在');
			}
		}
	}

	const DAN_TUO_SPLIT = '#';
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
		if (max($touZhuNumbers)> 11||min($touZhuNumbers)<1)
		{
			Extension::export('号码范围在1-11之间');
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
	private function getCountForRenXuan($text,$n)
	{
		$touZhuNumbers = $this->getTouZhuNumbers($text, $n);
		return Extension::combine(count($touZhuNumbers), $n);
	}
	/// <summary>
	/// 获取投注数-胆拖
	/// </summary>
	/// <param name="value"></param>
	/// <param name="type"></param>
	/// <returns></returns>
	private function getCountForDanTuo($text,$type)
	{
		$numbers = explode('#',$text);
		$danMaNums = $this->getTouZhuNumbers($numbers[0], 1);
		$tuoMaNums = $this->getTouZhuNumbers($numbers[1], 2);
		return Extension::combine(count($tuoMaNums), $type -  count($danMaNums));
	}

	/// <summary>
	/// 获取前2直选的投注数
	/// </summary>
	/// <param name="value"></param>
	/// <returns></returns>
	private function getCountForQian2ZhiXuan($text)
	{
		$touZhu2ZhiXuan = explode('|',$text);
		if(count($touZhu2ZhiXuan)!=2){
			Extension::export('投注号码有误');
		}
		$touZhu2ZhiXuan1 = $this->getTouZhuNumbers($touZhu2ZhiXuan[0]);
		$touZhu2ZhiXuan2 = $this->getTouZhuNumbers($touZhu2ZhiXuan[1]);
		return count($touZhu2ZhiXuan1) * count($touZhu2ZhiXuan2) - count(array_intersect($touZhu2ZhiXuan1,$touZhu2ZhiXuan2));
	}
	/// <summary>
	/// 获取投注数_前3直选
	/// </summary>
	/// <param name="value"></param>
	/// <returns></returns>
	private function getCountForQian3ZhiXuan($text)
	{
		$touZhu3ZhiXuan = explode('|',$text);
		if(count($touZhu3ZhiXuan)!=3){
			Extension::export('投注号码有误');
		}
		$touZhu3ZhiXuan1 = $this->getTouZhuNumbers($touZhu3ZhiXuan[0]);
		$touZhu3ZhiXuan2 = $this->getTouZhuNumbers($touZhu3ZhiXuan[1]);
		$touZhu3ZhiXuan3 = $this->getTouZhuNumbers($touZhu3ZhiXuan[2]);
		return	count($touZhu3ZhiXuan1) * count($touZhu3ZhiXuan2)* count($touZhu3ZhiXuan3) -
		count(array_intersect($touZhu3ZhiXuan1,$touZhu3ZhiXuan2))-
		count(array_intersect($touZhu3ZhiXuan1,$touZhu3ZhiXuan3))-
		count(array_intersect($touZhu3ZhiXuan2,$touZhu3ZhiXuan3))+
		2*count(array_intersect($touZhu3ZhiXuan1,$touZhu3ZhiXuan2,$touZhu3ZhiXuan3));
	}
}
?>
