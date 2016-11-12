<?php
set_time_limit(0);
require_once('common.php');

while (true) {
$dt=date("H:i");
if($dt>"08:00"&& $dt<"22:30"){
    try{
    //自购
        comm(0,1);

//追号
        comm(0,2);

//自购
        comm(5,1);

//追号
        comm(5,2);

//自购
        comm(6,1);

//追号
        comm(6,2);
//
        sleep(60);
    }catch(Exception $e){
        error_log('======index======='.$e.'======index=======',3, '/var/tmp/ex_log.log');
    }
}else{
break;
}

}