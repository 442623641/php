<?php

/*
@版本日期: 2012年8月9日
@著作权所有: 1024 intelligence ( http://www.1024i.com )

获得使用本类库的许可, 您必须保留著作权声明信息.
报告漏洞，意见或建议, 请联系 Lou Barnes(iua1024@gmail.com)
*/

class uploader
{
	private $handler = null;
	private $real_name = '';
	private $allow_upload_types = array();	// 允许上传的文件类型
	private $limit_size = 0;	// 最大允许上传的文件大小


    public function __construct( $allow_upload_types = array(), $limit_size = null)
	{
        $this->allow_upload_types = $allow_upload_types;

		if( $limit_size === null) $limit_size = $this->to_bytes(ini_get('upload_max_filesize'));
        $this->limit_size = $limit_size;
        
		$ini_post_max_size = $this->to_bytes(ini_get('post_max_size'));
		$ini_upload_max_filesize = $this->to_bytes(ini_get('upload_max_filesize'));        

		if ($ini_post_max_size < $this->limit_size || $ini_upload_max_filesize < $this->limit_size)
		{
			$require_size = max(1, $this->limit_size / 1024 / 1024) . 'M';             
			die("{'error':'请设置 php.ini 中 post_max_size 和 upload_max_filesize 值的最小为：$require_size'}");    
		}

        if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/') === 0)
		{
            $this->handler = new uploader_form();
        }
		else
		{
            $this->handler = new uploader_xhr();
        }
    }
    



	// 获取文件名: 客户端原文件名
	public function get_file_name()
	{
		return $this->handler->get_file_name();
	}

	// 获取文件大小，整型数字
	public function get_file_size()
	{
		return $this->handler->get_file_size();
	}

	// 获取文件类型
	public function get_file_type()
	{
		if ($this->handler)
		{
			$file_name = strtolower($this->handler->get_file_name());
			$pos = strrpos($file_name, '.');
			if ($pos !== false)
			{
				return substr($file_name, $pos+1);
			}
        }
		return '';
	}

	// 获取在服务器上保存的真实文件名
	public function get_real_name()
	{
		return $this->real_name;
	}


    private function to_bytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

	
	public function upload( $upload_dir )
	{
        if(!is_writable($upload_dir)) return array('error' => "没有写入权限.");
        if(!$this->handler) return array('error' => '文件没有上传.');
        
        $file_size = $this->handler->get_file_size();     
        if($file_size == 0) return array('error' => '文件不能为空');
        if($file_size > $this->limit_size) return array('error' => '文件过大');

        $file_type = $this->get_file_type();

        if(count($this->allow_upload_types) && !in_array($file_type, $this->allow_upload_types))
		{
            $types = implode(', ', $this->allow_upload_types);
            return array('error' => '禁止上传的格式，请上传以下格式 '. $types . '.');
        }
        
		$real_name = date('YmdHis').'-'.md5(uniqid()).'.'. $file_type;

		$this->real_name = $real_name;

        if( $this->handler->save($upload_dir.$real_name) )
		{
            return array('success'=>true);
        }
		else
		{
            return array('error'=> '上传失败，服务器保存文件时出错！');
        }
        
    }

}




// 处理支持 ajax 上传的浏览器上传的文件
class uploader_xhr
{

	// 保存文件到指定路径
    public function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $size = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($size != $this->get_file_size()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }

	// 获取文件名: 客户端原文件名
	public function get_file_name()
	{
        return $_GET['qqfile'];
    }

	// 获取文件大小，整型数字
	public function get_file_size()
	{
		if(isset($_SERVER["CONTENT_LENGTH"]))
		{
			return (int)$_SERVER["CONTENT_LENGTH"];         
		}
		else
		{
			throw new Exception('不支持获取文件大小.');
		}
	} 

}

// 处理普通 form 上传的文件
class uploader_form
{

	// 保存文件到指定路径
    public function save($path)
	{
        return move_uploaded_file($_FILES['qqfile']['tmp_name'], $path);
    }

	// 获取文件名: 客户端原文件名
    public function get_file_name()
	{
        return $_FILES['qqfile']['name'];
    }

	// 获取文件大小，整型数字
	public function get_file_size() {
        return $_FILES['qqfile']['size'];
    }
}

?>