<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <script type="text/javascript" src="<?php echo base_url(); ?>ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>ueditor/ueditor.all.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>/ueditor/themes/default/css/ueditor.css"/>
    <title>UEdite测试</title>
</head>

<body>
BaseURL:<?php echo base_url(); ?>
<div>
    <h1>Ueditor案例</h1>
    <script id="editor" type="text/plain" style="width:1024px;height:500px;"></script>
    <script id="editor" type="text/plain" style="width:1024px;height:500px;"></script>
    <form id="form" method="post">
        <script type="text/plain" id="myEditor" name="myContent">
			hello world!你好呀。
        </script>
        <input type="submit" name="submit" value="提交吧">
    </form>
    <script type="text/javascript">
        var editor_a = UE.getEditor('myEditor', {initialFrameHeight: 500});
        //--自动切换提交地址----
        var doc = document,
            version = editor_a.options.imageUrl || "php",
            form = doc.getElementById("form");
        form.action = "./ueditor/test";//自动跳转到controllers/user控制器中
    </script>
</div>
</body>
</html>