<?php
define("CX_API_LOG","/var/tmp/fuwu.log");

     $flag=0;
	for ($i = 0; $i < 100; $i++)
	{ 
		$flag++;
		if(!getShanxi11x5Kjhm()){
			sleep(5);
			continue;
		}else{
			break;
		}
	}

	getShanxi11x5Kjhm();

function getShanxi11x5Kjhm()
{
        try{
            $data = file_get_contents("http://api.yssh365.com//Lotterys.ashx?type=sx115");
            $array = json_decode($data,true);

            if(!$array){
                throw new Exception( "从yssh365获取数据失败" );
                return false;
            }

            $cur_issue='';
            $cur_num='' ;
            $cur_dt='' ;
            $order_num='';
            $flag = 0;
            foreach ($array as $res) {
                if($flag==0){
                    $cur_issue = $res['LotteryIssue'];
                    $cur_num = $res['LotteryValue'];;
                    $cur_dt = $res['LotteryDate'];
                    $order_num = $res['LotteryOrderValue'];
                }else{
                    break;
                }
                $flag++;
            }

		$conn = new mysqli('.','caipiaokong','caixiang518');
		$conn->select_db('caipiaokong');

		if($cur_issue==''||$cur_issue=='status'){
				echo "获取数据失败";
				return false;
		}
		$sql = "SELECT COUNT(*) sum FROM sxsyxw WHERE LotteryIssue='$cur_issue'";
		$res = $conn->query($sql);
		if($res===false) throw new Exception("DB error:".$conn->error);

		while($row=$res->fetch_assoc()){
			$count = $row['sum'];
			if($count>0){
				echo "当前开奖信息已存在";
				return false;
			}else{
				$sql = "insert into sxsyxw(LotteryIssue,LotteryValue,LotteryDate,LotteryDateStr,LotteryOrderValue) values('$cur_issue','$cur_num','$cur_dt','$cur_dt','$order_num')";
				$conn->query($sql);
			}
		}
		$conn->close();

            $conn2 = new mysqli('.','lottery','caixiang518');
            $conn2->select_db('xklottery');
            $lotteryID = 0;
            $sql = "SELECT COUNT(*) sum FROM currentlottery WHERE lotteryID='$lotteryID'";
            $res2 = $conn2->query($sql);
            while($row=$res2->fetch_assoc()){
                $count = $row['sum'];
                if($count>0){
                    $sql = "UPDATE currentlottery SET issue='$cur_issue',num='$cur_num',dt='$cur_dt',order_num='$order_num' WHERE lotteryID='$lotteryID'";
                    $conn2->query($sql);
                }else{
                    $sql = "insert into currentlottery(lotteryID,issue,num,dt,order_num) values('$lotteryID','$cur_issue','$cur_num','$cur_dt','$order_num')";
                    $conn2->query($sql);
                }
            }

            $conn2->close();

            return true;
        }catch (Exception $e){
	  $access=date('Y-m-d H:i:s');
           error_log("[$access] '出错: ".$e->getMessage()."\n", 3, CX_API_LOG);
            return false;
        }

	}

?>
