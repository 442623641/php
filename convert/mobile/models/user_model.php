<?php

/**
 * @author:yulei<13685590366@126.com>  
 * descrip:用户登录模型
 * date:2015-5-7
 */
class User_model extends CI_Model {

    private $userid;
    private $username;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 用户信息存放在session�&#65533;
     * @param type $userinfo
     */
    public function save_user_session($userinfo) {
        $this->session->set_userdata($userinfo);
    }

    /**
     * 获取id
     */
    public function get_session_userid() {
        $this->userid = $this->session->userdata('userid');
    }

    /**
     * 获取name
     */
    public function get_session_username() {
        $this->username = $this->session->userdata('user');
    }

    /**
     * 检查session�&#65533;
     * @return boolean
     */
    public function check_userinfo() {
        $this->get_session_username();
        if (empty($this->username)) {
            return FALSE;
        } else {
            return array('user' => $this->username);
        }
    }

}

?>