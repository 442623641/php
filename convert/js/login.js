function login(url) {
	
	var user = $('#user').val();
<<<<<<< .mine
	var pass = $('#password').val();
	var url = "../../index.php/user/login";
	//alert(url);
	$.ajax( {
=======
	var pass = $('#pass').val();

	$.ajax({
>>>>>>> .r53
		type : "POST",
		dataType: "json",
		url:url,
		data:{user:user, 
			pass:pass,},
		success : function(json) {
		}
	});
}

function showMesInfo(msg, type) {
	$('.msg-wrap').empty();
	if (type == 'warn') {
		var info = '<div class="msg-warn"><b></b>' + msg + '</div>';
		$('.form>.msg-wrap').append(info);
	}
	if (type == 'error') {
		var info = '<div class="msg-error"><b></b>' + msg + '</div>';
		$('.form>.msg-wrap').append(info);
	}
}