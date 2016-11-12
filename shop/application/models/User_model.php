<?php
 class User_Model extends CI_Model {
 	public $dealer;
 	public $id;
 	public $code;
 	public $validTime;
 	public $islogin;
	function __construct()
    {
        parent::__construct();
        $userinfo=$this->session->userdata();
        if(!empty($userinfo))
        {
        $this->dealer= @$userinfo['dealer'];
        $this->id= @$userinfo['dealerID'];
        $this->code= @$userinfo['dealer_code'];
        $this->validTime= @$userinfo['dealer_validTime'];
        $this->islogin= @$userinfo['dealer_islogin'];
        }
        
    }
    /**
 * descrip: 经销商登录名
 * date: 2015-5-7
 */
    function dealer()
    {
    	
    }
 }