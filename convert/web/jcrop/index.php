<?php
include 'config.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>上传头像自定义裁切 示例页面</title>
<meta charset="utf-8" />


<script type="text/javascript" language="javascript"
	src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>


<link type="text/css" rel="stylesheet"
	href="/web/jcrop/file-uploader/fileuploader.css" />
<script type="text/javascript" language="javascript"
	src="/web/jcrop/file-uploader/fileuploader.min.js"></script>


<link type="text/css" rel="stylesheet"
	href="/web/jcrop/jcrop/jquery.Jcrop.min.css" />
<script type="text/javascript" language="javascript"
	src="/web/jcrop/jcrop/jquery.Jcrop.min.js"></script>
	<script src="/web/js/alert.js"></script>

<link href="/web/css/headPortrait.css" rel="stylesheet" type="text/css">

<link href="/web/css/base.css" rel="stylesheet" type="text/css">
<link href="/web/css/layout.css" rel="stylesheet" type="text/css">
<link href="/web/css/headPortrait.css" rel="stylesheet" type="text/css">
<link href="/web/css/headPortrait.css" rel="stylesheet" type="text/css">


<script type="text/javascript" language="javascript">
   		var g_oJCrop = null;
		
		$(function(){
			new qq.FileUploader({
				element: document.getElementById('upload_avatar'),
				action: "controller.php?task=ajax_upload_avatar",
				multiple: false,
				disableDefaultDropzone: true,
				allowedExtensions: ["<?php echo implode('", "', explode(', ', ALLOW_UPLOAD_IMAGE_TYPES) ); ?>"],
				uploadButtonText: '选择头像图片',
				onComplete: function(id, fileName, json) {
					if(json.success)
					{
						if(g_oJCrop!=null) g_oJCrop.destroy();
						
						$("#crop_tmp_avatar").val(json.tmp_avatar);
						$("#crop_container").show();
						$("#crop_target, #crop_preview").html('<img src="tmp/'+json.tmp_avatar+'">');

						$('#crop_target img').Jcrop({
							allowSelect: false,
							onChange: updatePreview,
     						onSelect: updatePreview,
							aspectRatio: <?php echo AVATAR_WIDTH/AVATAR_HEIGHT; ?>,
							minSize:[<?php echo AVATAR_WIDTH; ?>, <?php echo AVATAR_HEIGHT; ?>]
						},function(){
						  	g_oJCrop = this;
							var bounds = g_oJCrop.getBounds();
							var x1,y1,x2,y2;
							if(bounds[0]/bounds[1] > <?php echo AVATAR_WIDTH; ?>/<?php echo AVATAR_HEIGHT; ?>)
							{
								y1 = 0;
								y2 = bounds[1];

								x1 = (bounds[0] - <?php echo AVATAR_WIDTH; ?> * bounds[1]/<?php echo AVATAR_HEIGHT; ?>)/2
								x2 = bounds[0]-x1;
							}
							else
							{
								x1 = 0;
								x2 = bounds[0];
								
								y1 = (bounds[1] - <?php echo AVATAR_HEIGHT; ?> * bounds[0]/<?php echo AVATAR_WIDTH; ?>)/2
								y2 = bounds[1]-y1;
							}
							g_oJCrop.setSelect([x1,y1,x2,y2]);
						});
					}
					else
					{
						alert(json.description);
					}
				}
			});
			
			
			
		});
		
		
		function updatePreview(c)
		{
		    $('#crop_x1').val(c.x);
			$('#crop_y1').val(c.y);
			$('#crop_x2').val(c.x2);
			$('#crop_y2').val(c.y2);
			$('#crop_w').val(c.w);
			$('#crop_h').val(c.h);

			if (parseInt(c.w) > 0)
			{
				var bounds = g_oJCrop.getBounds();

				var rx = <?php echo AVATAR_WIDTH; ?> / c.w;
				var ry = <?php echo AVATAR_HEIGHT; ?> / c.h;
				
				$('#crop_preview img').css({
					width: Math.round(rx * bounds[0]) + 'px',
					height: Math.round(ry * bounds[1]) + 'px',
					marginLeft: '-' + Math.round(rx * c.x) + 'px',
					marginTop: '-' + Math.round(ry * c.y) + 'px'
				});
			}
		};
		
		
		
		function saveCropAvatar()
		{
			if($("#crop_tmp_avatar").val()=="")
			{
				alert("您还没有上传头像");
				return false;
			}
			
			$.ajax({
				type: "POST",
				url: "controller.php?task=ajax_crop",
				data: $("#form_crop_avatar").serialize(),
				dataType: "json",
				success: function(json)
				{
					if(json.success)
					{
						$("#crop_tmp_avatar").val("");
						$("#crop_container").hide();
						
						$("#my_avatar").html('<img src="avatars/'+json.avatar+'">');
					}
					else
					{
						alert(json.description);
					}
				}
			});
		}
	</script>


</head>
<body>
<div class="grzx_cot_rgt_tit">个人资料</div>
<div class="sns-nf" style="padding:25px 0 0 32px;"></div>											  
<div class="mt">
						<ul class="extra-l">
							<li class="fore-1"><a class="curr"  href="<?php echo 'http://'.$_SERVER["HTTP_HOST"].'/UserCenter/personInfo'?>" target="Iframe_grzx">基本信息</a></li>
                            <li class="fore-2"><a href="<?php echo 'http://'.$_SERVER["HTTP_HOST"].'/web/jcrop/index.php'?>" target="Iframe_grzx">头像照片</a></li>													
						</ul>
					</div>
    												
<div class="mod-main">


<div class="mc update-face-cont">
<div class="update-lcol">
<div class="mb10">
<div id="upload_avatar"  class="grzx_gtable_btnOk"></div>
<div class="ftx03" style="float:left;margin-top:24px;clear:left">仅支持JPG、GIF、PNG、JPEG、BMP格式，文件小于2M</div>
	<div style="border:1px solid #e8e8e8;width:200px;height:200px;padding:6px;float:left;clear:left;margin-top:12px;margin:0 auto;">
	<table style="border: none;">
	<tr>
	</tr>
		<tr>
			<td>
			<div id="crop_target"></div>
			</td>
			<td valign="top">
			<div id="crop_preview" style="width:<?php echo AVATAR_WIDTH; ?>px; height:<?php echo AVATAR_HEIGHT; ?>px; overflow:hidden;">
			</div>
			</td>
		</tr>
	</table></div>
	<div style="clear:left;float:left;margin-top:12px;"><input type="button" id="btn_save_crop" value="保存"
		style="padding: 2px 10px;border:0;" class="grzx_gtable_btnOk" onClick="javascript:saveCropAvatar();" /></div>

	<form id="form_crop_avatar"><input type="hidden" name="tmp_avatar"
	id="crop_tmp_avatar" value=""> <input type="hidden" name="x1"
		id="crop_x1" value=""> <input type="hidden" name="y1" id="crop_y1"
		value=""> <input type="hidden" name="x2" id="crop_x2" value=""> <input
		type="hidden" name="y2" id="crop_y2" value=""> <input type="hidden"
		name="w" id="crop_w" value=""> <input type="hidden" name="h"
		id="crop_h" value=""></form>

</div>
</div>
</div>
</div>

</body>
</html>

