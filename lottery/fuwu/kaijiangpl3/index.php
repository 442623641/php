<?php
set_time_limit(0);
require_once('common.php');

//
while (true) {
    $dt=date("H:i");
    if(($dt>"19:00"&& $dt<="23:59")||($dt >= "00:00" && $dt < "01:00")){
        //自购
        comm(1, 'lotteryuser', 'lotteryuserentity', 1, 'lotteryUserID');

        //追号
        comm(1, 'lotterychase', 'lotterychaseentity', 2, 'lotteryUserID');

        //合买
        comm(1, 'lotteryjoint', 'lotteryjointentity', 3, 'lotteryJointID');

        //
        sleep(600);
    }else{
	echo "this program will run between 20:00 and 23:50\n";
        break;
    }

}