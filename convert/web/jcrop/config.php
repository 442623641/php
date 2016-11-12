<?php
define('ALLOW_UPLOAD_IMAGE_TYPES', 'jpg, jpeg, png, gif');		// 允许上传的图片类型

define('MAX_UPLOAD_SIZE', 1048576);							// 最大允许上传的图片大小(1M = 1024 * 1024 = 1048576)

// ===========================头像裁切尺寸
define('AVATAR_WIDTH', 90);
define('AVATAR_HEIGHT', 90);

// ===========================最大图片尺寸，过大时将自动按比例缩小，防止超大图片撑破页面
define('AVATAR_MAX_WIDTH', 500);
define('AVATAR_MAX_HEIGHT', 1000);
?>

