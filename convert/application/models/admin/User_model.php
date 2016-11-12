<?php
class User_model extends CI_Model{

	function __construct(){

		parent::__construct();
	}

	function search($id){         
		$this->db->where('user_id', $id);  
        $this->db->select('*');  
        $query = $this->db->get('users');  
        return $query->row_array(); 
	}
	
	function countUser(){
		$query = $this->db->query("SELECT COUNT(*) user_num FROM users");
		return $query->row_array(); 
	}
	
	function countUserByCondition($account,$name,$depart){
		$sql="SELECT COUNT(*) user_num FROM users where 1=1 ";
		if(!""==$account){
			$sql = $sql." and account='".$account."'";
		}
		if(!""==$name){
			$sql = $sql." and name='".$name."'";
		}
		if(!""==$depart){
			$sql = $sql." and depart='".$depart."'";
		}
		$query = $this->db->query($sql);
		return $query->row_array(); 
	}
	
	function searchAll($num,$offset){   
        $query = $this->db->get('users',$num,$offset);  
        return $query->result_array(); 
	}
	
	function searchByCondition($account,$name,$depart,$num,$offset){ 
		if(!""==$account){
			$this->db->where('account=',$account);
		}
		if(!""==$name){
			$this->db->where('name=',$name);
		}
		if(!""==$depart){
			$this->db->where('depart=',$depart);
		}
        $query = $this->db->get('users',$num,$offset);  
        return $query->result_array(); 
	}

	function add($arr){
		$this->db->insert('users',$arr);

	}

	function edit($user_id,$arr){

        $this->db->where('user_id', $user_id);  
        $this->db->update('users', $arr);  

		return $this->db->affected_rows();//返回一想行数

	}

	function delete($id){//删除对应ID信息

		$this->db->where('user_id',$id);

		$res = $this->db->delete('users');
		return $res;
	}

//cookie登陆：检测用户是否登陆，如果cookie值失效，则返回false，如果cookie值未失效，则根据cookie中的用户名和密码从数据库中获取用户信息，如果能获取到用户信息，则返回查询到的用户信息，如果没有查询到用户信息，则返回0  
    public function is_login(){  
        //获取cookie中的值  
        if(empty($_COOKIE['username']) || empty($_COOKIE['password'])){  
            $user_info = false;  
        }else{  
            $user_info=$this->check_user_login($_COOKIE['username'],$_COOKIE['password']);     
        }  
        return $user_info;  
    }  
  
    //根据用户名及加密密码从数据库中获取用户信息，如果能获取到，则返回获取到的用户信息，否则返回false，注意：密码为加密密码  
    public function check_user_login($username,$password){  
        //这里大家要注意：$password为md5加密后的密码  
        //$this->db->query("select * from ");   
        //快捷查询类的使用：能为我们提供快速获取数据的方法  
        //此数组为查询条件  
        //注意：关联数组  
        $arr=array(   
            'account'=>$username,//用户名  
            'password'=>$password,//加密密码  
            'status'=>1            //账户为开启状态  
        );  
        //在database.php文件中已经设置了数据表的前缀，所以此时数据表无需带前缀  
        $query = $this->db->get_where("users",$arr);  
        //返回二维数组  
        //$data=$query->result_array();  
        //返回一维数组  
        $user_info=$query->row_array();  
        if(!empty($user_info)){  
            return $user_info;    
        }else{    
            return false;  
        }  
    } 
    
    public function check_pass($user_id,$password){  
        $arr=array(   
            'user_id'=>$user_id,//用户名  
            'password'=>$password,//加密密码  
            'status'=>1            //账户为开启状态  
        );  
        $query = $this->db->get_where("users",$arr);  
       
        $user_info=$query->row_array();  
        if(!empty($user_info)){  
            return true;    
        }else{    
            return false;  
        }  
    } 

    /**
     * 用户登录时加载菜单节点
     * $user_id 用户ID
     */
    public function getUserNodes($user_id){
		$query = $this->db->query('SELECT DISTINCT d.node_id,d.node_level,d.node_name,d.node_url,
										  d.order_num,d.parent_node_id,d.class_order
								    FROM users a,role b,user_role c,menu d,role_menu e
								   WHERE a.user_id=c.user_id AND b.role_id=c.role_id
									 AND b.role_id=e.role_id AND d.node_id=e.node_id
									 AND a.status=1 AND b.status=1 AND c.status=1
									 AND d.status=1 AND e.status=1 and a.user_id='.$user_id.' and d.node_level=2 order by d.node_level asc,d.order_num asc;');
		return $query->result_array();
    }
    
    public function getSecondLevelNodes(){
    	$this->db->order_by('order_num','asc');  
    	$this->db->where('node_level', 2);  
        $this->db->select('*');  
        $query = $this->db->get('menu');  
        return $query->result_array(); 
    }

}

//调用模型model 在控制其中执行，
//
//<?php
//
//class Pages extends CI_Controller {
//
//	function __construct() {
//
//		parent::__construct();
//
//	}
//
//	public function read($id) {
//
//		$this->load->Model("users");//调用users模型
//
//		$data = $this->users->read($id);//调用模型read方法，参数为$id
//
//		$this->load->view('pages',$data);//调用视图pages，并传递参数为返回来的新闻$data
//
//	}
//
//}
//
//?
//
////调用模型实际方法为 $this->load->model('Model_name');
//
//$this->Model_name->function(); 可以对对象起别名 $this->load->model('Model_name',
//'newModel_name'); $this->newModel_name->function();

