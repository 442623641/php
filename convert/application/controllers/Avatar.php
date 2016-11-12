<?php
//include 'config.php';

class Avatar extends Common_Controller
{

	public function __construct(){
		parent::__construct();
		$this->save_path=$_SERVER['DOCUMENT_ROOT'].'/jcrop/avatars'.DIRECTORY_SEPARATOR;
		
	}
	//头像上传
	public function index(){
		header('Content-type: application/json');
		$data['MAX_UPLOAD_SIZE']=	MAX_UPLOAD_SIZE;						// 最大允许上传的图片大小(1M = 1024 * 1024 = 1048576)
		$data['ALLOW_UPLOAD_IMAGE_TYPES']=ALLOW_UPLOAD_IMAGE_TYPES;
		// ===========================头像裁切尺寸
		$data['AVATAR_WIDTH']=AVATAR_WIDTH;
		$data['AVATAR_HEIGHT']=AVATAR_HEIGHT;

		// ===========================最大图片尺寸，过大时将自动按比例缩小，防止超大图片撑破页面
		$data['AVATAR_MAX_WIDTH']=AVATAR_MAX_WIDTH;
		$data['AVATAR_MAX_HEIGHT']=AVATAR_MAX_HEIGHT;
		$data['userInfo']=$this->session->userdata;
		$this->load->view("UserCenter/jcrop/avatar.html",$data);
	}
	public function getPostFile(){
		if(!isset($_REQUEST['userID'])){
			$reponse['fail'] = 1;
			$reponse['mess']='用户编号不能为空';
			$reponse['param']=$_FILES;
			echo json_encode($reponse);
			exit();
		}
		$user=$_REQUEST['userID'];
		if(!isset($_FILES['file'])){
			$reponse['fail'] = 1;
			$reponse['mess']='参数不正确';
			$reponse['param']=$_FILES;
			echo json_encode($reponse);
			exit();
		}
		$reponse['fail'] = 0;
		if ((($_FILES["file"]["type"] == "image/gif")
		|| ($_FILES["file"]["type"] == "image/jpeg")
		|| ($_FILES["file"]["type"] == "image/png")
		|| ($_FILES["file"]["type"] == "image/jpg")
		|| ($_FILES["file"]["type"] == "image/pjpeg"))
		&& ($_FILES["file"]["size"] < MAX_UPLOAD_SIZE))
		{
			if ($_FILES["file"]["error"] > 0)
			{
				$reponse['fail'] = 1;
				$reponse['mess']='上传异常,code:'.$_FILES["file"]["error"] ;
				echo json_encode($reponse);
				exit();
			}
			$type=explode('/', $_FILES["file"]["type"]);
			//echo $_FILES["file"]["type"];
			if(!isset($type[1])){
				$reponse['fail'] = 1;
				$reponse['mess']= "图片格式非法";	
				echo json_encode($reponse);
				exit();
			}
			$filename=md5($user).'.'.$type[1];
			$target_path =$this->save_path. basename($filename);
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
				$this->setAvatar($filename);
				$reponse['fail'] = 0;
				$reponse['data']['avatar']=$filename;
				$reponse['data']['linkUrl']=base_url().'jcrop/avatars/'.$filename;
				$reponse['data']['api_return']=@$this->setAvatar($filename);
				} else{
				$reponse['fail']=1;
				$reponse['mess']= "网络延时，稍后再试!" . $_FILES['file']['error'];
				echo json_encode($reponse);	
				exit();
				}
		}else{
			$reponse['fail']=1;
			$reponse['mess']= "图片格式或大小不支持!" . $_FILES['file']['error'];
			$reponse['param']=$_FILES;
			echo json_encode($reponse);
			exit();	
		}
		header('Content-type: application/json');
		echo json_encode($reponse);

	}
	//更改
	private function setAvatar($avatar_name){
		//$url=config_item('api_url')."user/info/set?user=".$_REQUEST['userID']."&avatar=".$avatar_name;
		$url=config_item('api_url')."user/info/set?user=".$_REQUEST['userID']."&avatar=".$avatar_name;
		try {
				$ch = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
				$file_contents = curl_exec($ch);
				curl_close($ch);
				return $file_contents;
		} catch (Exception $e) {
				return false;
		}
		return true;
	}
	// ajax 上传头像图片
	public function upload()
	{
		//include('models'.DIRECTORY_SEPARATOR.'uploader.php');
		$this->load->library('uploader');
		$uploader = new uploader( explode(', ', ALLOW_UPLOAD_IMAGE_TYPES), MAX_UPLOAD_SIZE );

		$result = $uploader->upload($_SERVER['DOCUMENT_ROOT'].'/jcrop/tmp'.DIRECTORY_SEPARATOR );	// 先保存到临时文件夹

		$reponse = new stdClass();
		if( isset($result['success']) && $result['success'] )
		{
			//include('models'.DIRECTORY_SEPARATOR.'gd.php');
			$this->load->library('gd');
			//$src_path = $_SERVER['DOCUMENT_ROOT'].'/jcrop/tmp'.DIRECTORY_SEPARATOR.$uploader->get_real_name();
			$src_path = $_SERVER['DOCUMENT_ROOT'].'/jcrop/tmp'.DIRECTORY_SEPARATOR.$uploader->get_real_name();

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
	public function crop()
	{
		$tmp_avatar = $_POST['tmp_avatar'];
		$x1 = $_POST['x1'];
		$y1 = $_POST['y1'];
		$x2 = $_POST['x2'];
		$y2 = $_POST['y2'];
		$w = $_POST['w'];
		$h = $_POST['h'];
		$user=$_POST['userID'];

		$reponse = new stdClass();

		$src_path = $_SERVER['DOCUMENT_ROOT'].'/jcrop/tmp'.DIRECTORY_SEPARATOR.$tmp_avatar;
		if(!file_exists($src_path))
		{
			$reponse->success = false;
			$reponse->description = '未找到图片文件';
		}
		else
		{
			$this->load->library('gd');
			$gd = new gd();
			$gd->open( $src_path );
			if( $gd->is_image() )
			{
				$gd->crop($x1, $y1, $w, $h);
				$gd->resize_to(AVATAR_WIDTH, AVATAR_HEIGHT, 'scale_fill');

				//$avatar_name = date('YmdHis').'_'.md5(uniqid()).'.'.$gd->get_type();
				$avatar_name = md5($user).'.'.$gd->get_type();
				$gd->save_to( $_SERVER['DOCUMENT_ROOT'].'/jcrop/avatars'.DIRECTORY_SEPARATOR.$avatar_name );
				setcookie("avatar", "", time() - 3600);
				setcookie('avatar', $avatar_name, time()+86400*30);	// 本示例程序仅在 cookie 中保存
				@unlink($src_path);
				$reponse->success = true;
				$reponse->avatar = $avatar_name;
				$reponse->api_return=@$this->setAvatar($avatar_name);
				//更新用户vatar
				$this->session->set_userdata(array('avatar'=>$avatar_name));
			}

			else
			{
				$reponse->success = false;
				$reponse->description = '该图片文件不是有效的图片';
			}
		}

		//$reponse->description=$reponse->description.$src_path;
		header('Content-type: application/json');
		echo json_encode($reponse,JSON_UNESCAPED_UNICODE);
	}
}