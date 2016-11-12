<?php

/**
 * Created by PhpStorm.
 * User: 卞治华
 * Date: 15-5-12
 * Time: 上午11:51
 */

//富文本编辑器Ueditor测试
class Ueditor extends CI_Controller
{
    //http://localhost:81/web/index.php/ueditor
    function index()
    {
        $this->load->view('ueditor/index');
    }

    function test()
    { //views->ueditor->index.php 回传数据接收测试
        if (empty($_REQUEST['myContent']))
            echo '没有接收到数据';
         else
            echo '接收到的测试数据' . $_REQUEST['myContent'];
    }
} 