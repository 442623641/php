<?php
include_once 'func/common.php';
include_once 'func/secureUtil.php';
require_once 'func/log.class.php';
?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>响应页面</title>

<style type="text/css">
body table tr td {
	font-size: 14px;
	word-wrap: break-word;
	word-break: break-all;
	empty-cells: show;
}
</style>
</head>
<body>
<table width="800px" border="1" align="center">
	<tr>
		<th colspan="2" align="center">响应结果</th>
	</tr>

	<?php
	// 初始化日志
	$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
	$log->LogInfo ( "============处理前台请求开始===============" );
	// 初始化日志
	foreach ( $_POST as $key => $val ) {
		?>
	<tr>
		<td width='30%'><?php echo isset($mpi_arr[$key]) ?$mpi_arr[$key] : $key ;?></td>
		<td><?php echo $val ;?></td>
	</tr>
	<?php }?>
	<tr>
		<td width='30%'>验证签名</td>
		<td><?php			
		if (isset ( $_POST ['signature'] )) {
			if(verify ( $_POST )&&$_POST['respCode']=='00'){		
				require_once '../Common.php';
				$common=new Common();
				$s=$common->recharge(substr($_POST['orderId'], 16),$_POST['settleAmt'],$_POST['orderId'],$_POST['queryId']);
				$log->LogInfo ( "接口返回值为>" .serialize($s));
			}			
				echo verify ( $_POST ) ? '验签成功' : '验签失败';
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
		} else {
			echo '签名为空';
		}
		?></td>
	</tr>
</table>
</body>
</html>
