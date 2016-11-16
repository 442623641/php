<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {
	#开启皮肤功能
	protected $_theme='default/';

	public function switch_themes_on(){
		$this->_ci_view_paths = array(FCPATH . THEMES_DIR . $this->_theme	=> TRUE);
	}
	public function switch_themes_off(){
		//just do nothing
	}
}