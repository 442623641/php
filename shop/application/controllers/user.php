<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//用户控制器
class User extends Home_Controller{

	public function __construct(){
		parent::__construct();
		$this->load->helper('form');
	}
	public function test(){
		$save_dir =$_SERVER['DOCUMENT_ROOT'].'/upload/';
		var_dump($_FILES["file"]);
		if ((($_FILES["file"]["type"] == "image/gif")
		|| ($_FILES["file"]["type"] == "image/jpeg")
		|| ($_FILES["file"]["type"] == "image/pjpeg"))
		&& ($_FILES["file"]["size"] < 60000))
		{
			if ($_FILES["file"]["error"] > 0)
			{
				echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
			}
			else
			{
				echo "Upload: " . $_FILES["file"]["name"] . "<br />";
				echo "Type: " . $_FILES["file"]["type"] . "<br />";
				echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
				echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

				if (file_exists("upload/" . $_FILES["file"]["name"]))
				{
					echo $_FILES["file"]["name"] . " already exists. ";
				}
				else
				{
					move_uploaded_file($_FILES["file"]["tmp_name"],$save_dir.$_FILES["file"]["name"]);
					echo "Stored in: " . $save_dir . $_FILES["file"]["name"];
				}
			}
		}
		else
		{
			echo "Invalid file";
		}
	}
//	'content' => 
//    array (size=1)
//      0 => 
//        array (size=7)
//          'dealerID' => string '10002' (length=5)
//          'phoneNumber' => string '18225605421' (length=11)
//          'agentID' => string '' (length=0)
//          'name' => string 'wei' (length=3)
//          'company_name' => string '' (length=0)
//          'birthday' => string '0000-00-00' (length=10)
//          'licenseID' => string '' (length=0)
	//首页
	public function index(){
		//获取用户信息
		$params['user']=$this->dealer;
		$params['lastcode'] = $this->get_session('code');
		$response = $this->request('dealer', 'info/get', $params);	
		//var_dump($response)	;		
		if ($response['fail'] != 0) {
			error($response['mess']);
			return ;
		} 	
		$this->load->view('index.html',$response['content'][0]);
	}
	#注销动作
	public function logout(){
		$this->session->unset_userdata("dealer_islogin");
		$this->islogin=false;
		redirect('Privilege/index');
	}
	
}
?>
