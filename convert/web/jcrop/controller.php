<?php
include 'config.php';

class controller
{

	// ajax 上传头像图片
	public function ajax_upload_avatar()
	{
		include('models'.DIRECTORY_SEPARATOR.'uploader.php');

		$uploader = new uploader( explode(', ', ALLOW_UPLOAD_IMAGE_TYPES), MAX_UPLOAD_SIZE );

		$result = $uploader->upload( 'tmp'.DIRECTORY_SEPARATOR );	// 先保存到临时文件夹

		$reponse = new stdClass();
		if( isset($result['success']) && $result['success'] )
		{
			include('models'.DIRECTORY_SEPARATOR.'gd.php');

			$src_path = 'tmp'.DIRECTORY_SEPARATOR.$uploader->get_real_name();

			$gd = new gd();
			$gd->open( $src_path );
			if( $gd->is_image() )
			{
				if( $gd->get_width() < AVATAR_WIDTH )
				{
					$reponse->success = false;	// 传递给 file-uploader 表示服务器端已处理
					$reponse->description = '您上传的图片宽度('.$gd->get_width().'像素)过小！最小需要'.AVATAR_WIDTH.'像素。';
				}
				else if( $gd->get_height() < AVATAR_HEIGHT )
				{
					$reponse->success = false;	// 传递给 file-uploader 表示服务器端已处理
					$reponse->description = '您上传的图片高度('.$gd->get_height().'像素)过小！最小需要'.AVATAR_HEIGHT.'像素。';
				}
				else
				{
					$reponse->success = true;
					$reponse->tmp_avatar = $uploader->get_real_name();

					if($gd->get_width()>AVATAR_MAX_WIDTH || $gd->get_height() > AVATAR_MAX_HEIGHT)
					{
						// 图片过大时按比例缩小，防止超大图片撑破页面
						$gd->resize_to(AVATAR_MAX_WIDTH, AVATAR_MAX_HEIGHT, 'scale');
						$gd->save_to( $src_path );
					}
				}
			}
		}
		else if( isset($result['error']) )
		{
			$reponse->success = false;
			$reponse->description = $result['error'];
		}

		header('Content-type: application/json');
		echo json_encode($reponse);
	}

	// ajax 裁切头像图片
	public function ajax_crop()
	{
		$tmp_avatar = $_POST['tmp_avatar'];
		$x1 = $_POST['x1'];
		$y1 = $_POST['y1'];
		$x2 = $_POST['x2'];
		$y2 = $_POST['y2'];
		$w = $_POST['w'];
		$h = $_POST['h'];

		$reponse = new stdClass();

		$src_path = 'tmp'.DIRECTORY_SEPARATOR.$tmp_avatar;
		if(!file_exists($src_path))
		{
			$reponse->success = false;
			$reponse->description = '未找到图片文件';
		}
		else
		{
			include('models'.DIRECTORY_SEPARATOR.'gd.php');
			$gd = new gd();
			$gd->open( $src_path );
			if( $gd->is_image() )
			{
				$gd->crop($x1, $y1, $w, $h);
				$gd->resize_to(AVATAR_WIDTH, AVATAR_HEIGHT, 'scale_fill');

				$avatar_name = date('YmdHis').'_'.md5(uniqid()).'.'.$gd->get_type();
				$gd->save_to( 'avatars'.DIRECTORY_SEPARATOR.$avatar_name );

				setcookie('avatar', $avatar_name, time()+86400*30);	// 本示例程序仅在 cookie 中保存 
				/*

				实际应用中会有更多 保存头像代码

				......

				*/

				@unlink($src_path);

				$reponse->success = true;
				$reponse->avatar = $avatar_name;

				

				$reponse->description = '';
			}
			else
			{
				$reponse->success = false;
				$reponse->description = '该图片文件不是有效的图片';
			}
		}

		header('Content-type: application/json');
		echo json_encode($reponse);
	}
}

$task = isset($_GET['task'])?$_GET['task']:'';
if($task!='')
{
	$instance = new controller();
	$instance->$task();
}
?>