<?php
class Common {
static function valid(){
		#校验投注号码,金额
		switch ($_REQUEST['lotteryID']){
			#11选5
			case 0:
				$this->load->library('Pool_11X5');
				$pool=new Pool_11X5();
				$pool->checkText(json_decode($_REQUEST['text']));
				$count=$pool->count;
			#排列3
			case 1:
				$this->load->library('Pool_PiaLie3');
				$pool=new Pool_PaiLie3();
				$pool->checkText(json_decode($_REQUEST['text']));
				$count=$pool->count;
			default:
				$this->retFailed('彩票类型不存在');						
		}
		if($count*(int)$_REQUEST['multiple']*2!=$_REQUEST['money']){
			$this->retFailed('金额有误');
		}
		return true;
	}
}