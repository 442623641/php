<?php
set_time_limit(0);
require_once('common.php');
//comm(0,2);exit;
//comm(0,1);exit;

while (true) {
$dt=date("H:i");
if($dt>"08:00"&& $dt<"22:30"){
//自购
comm(0,1);

//追号
comm(0,2);

//
//自购
comm(5,1);

//追号
comm(5,2);

//自购
comm(6,1);

//追号
comm(6,2);

//
    sleep(45);
}else{
break;
}

}