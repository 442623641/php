<?php
$config = array(
		'login' => array(
			array(
				'label' => '密码',
				'field' => 'passwd',
				'rules' => 'trim|required',
				),
			array(
				'label' => '邮箱或手机号',
				'field' => 'user',
				'rules' => 'trim|required|valid_email',
				),
			),
		);