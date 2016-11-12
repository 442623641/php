<?php
	$conn = new mysqli('192.168.10.21','root','root');
	$conn->select_db('lottery');
	$sql = "SELECT a.issue,b.* FROM lotteryissue a INNER JOIN chaseuser b ON b.lotteryID=a.lotteryID WHERE  b.currentIssue<a.issue AND a.stopTime>NOW() AND b.flag=0 AND 
			b.state=7   AND b.totalPhases>b.chasePhases AND LOCATE(a.issue,b.issues)";
	$res = $conn->query($sql);
	while($row=$res->fetch_assoc()){
		$chaseuserid=$row['ID'];
		$curissue=$row['issue'];
		$conn->autocommit(false);//开始事物
		//更新追号信息表
		$sql = "UPDATE chaseuser a SET a.chasePhases=a.chasePhases+1,a.updateDate=NOW(),
		a.currentIssue='$curissue' where a.ID='$chaseuserid'";
		$res1 = $conn->query($sql);
		
		$firstID=date("Ymdhism").$row['lotteryID'];
		$multiple = 1;
		$issues = $row['issues'];
		$array = json_decode($issues,true);
	 	for($temp = 0;$temp < count($array);$temp++){
  			if($array[$temp][0]==$curissue){
  				$multiple = $array[$temp][1];
  			}
  		}
  		//插入追号购彩表
		$sql = "insert into lotterychase(ID,shop,money,chaseID,financeID,issue,lotteryID,multiple,state,deviceID,createDate,updateDate,memo)
		values ('$firstID','".$row['shop']."','".$row['money']/$row['totalPhases']."','$chaseuserid','".$row['financeID']."',
		'$curissue','".$row['lotteryID']."','$multiple','0','".$row['deviceID']."',NOW(),NOW(),'".$row['memo']."')";
		$res2 = $conn->query($sql);

		//插入追号购彩明细表
		$curTime = date('y-m-d h:i:s',time());
		$sql = "insert into lotterychaseentity(ID,lotteryUserID,text,money,entityID,state,createDate)
		values ('$firstID','$firstID','".$row['text']."','".$row['money']/$row['totalPhases']."',0,0,NOW())";
		$res3 = $conn->query($sql);
		if($res1&&$res2&$res3){
			$conn->commit(); //提交事物
		}else{
			$mysqli->rollback();//回滚
		}
		
		$conn->autocommit(true);//不使用事物

	
	}
?>
