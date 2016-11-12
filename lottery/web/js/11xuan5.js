	function setIssue_11xuan5(url){
		baseUrl=url;
        $.ajax({
            type: "post",
            url: baseUrl+"/api/LotteryIssue/currentIssue",
            data: { lotteryID: 0 },
            dataType:"json",
            success: function (json) {
                if(json.fail==undefined&&json.fail!=0){return;}
                $("#currentIssue").html(json.data.issue);
                countDown(json.data.stopTime); 
            }
        });
	}

	function countDown(time) {
		 var end_time = new Date(time).getTime(),//月份是实际月份-1  
		 sys_second = (end_time - new Date().getTime()) / 1000;
	    var timer = setInterval(function () {
	        if (sys_second > 1) {
	            sys_second -= 1;
	            var min = Math.floor((sys_second / 60) % 60);
	            var sec = Math.floor(sys_second % 60);
	            min = min > 10 ? min: "0" + min;
	            sec = sec > 10 ? sec: "0" + sec;
	            $("#timer").html('<em class="red">'+min+'</em>分<em class="red">'+sec+'</em>秒');
	        } else {
	            clearInterval(timer);
	            msg("第" + issue + "期已截止，当前期是第 " + (parseInt(issue) + 1) + " 期", "warn", function () { setIssue();},10);
	        }
	    }, 1000);
	}
	
	//投注
	function performance(){
		//var text=$('#text').val();
		var issues=[];
		var data=null;
		if($('#count').val()==0){
			msg("您还没有选号","warn","",3);
			return;
		}
		var urle="/api/LotteryUser/add";
		//追号
		var totalPhases = $('#totalPhases').val();
		var multiple = $('#multiple').val();
		var lotteryID = $('#lotteryID').val();
		var money = $('#totalmoney').val();
		if(totalPhases>1){
			urle="/api/LotteryChase/add";
			data={issue:$("#currentIssue").html(),
					userID:'10017',
		    		multiple:multiple,
		    		lotteryID:lotteryID,
		    		text:JSON.stringify(text),
		    		money:money,
		    		issues:JSON.stringify(getIssues(totalPhases)),
		    		//shop:'shop-001',
		    		lotteryID:lotteryID,	
		    		isWinners:document.getElementById("isWinner").checked?1:0}
		}else{
			data={issue:$("#currentIssue").html(),
					userID:'10017',
		    		multiple:multiple,
		    		lotteryID:lotteryID,
		    		text:JSON.stringify(text),
		    		money:money,
		    		//shop:'shop-001',
		    		lotteryID:lotteryID}
		}
		var s=text;
	    $.ajax({
	        type: "post",
	        url: baseUrl+urle,
	        data: data,
	        dataType:"json",
	        error:function () {msg("网络异常,投注失败","error");},
	        success: function (json) {
	            if(json.fail==undefined){msg("网络异常,投注失败","error");return;}
	            if(json.fail!=0){msg(json.mess,"error");return;}
	            msg("投注成功","ok",function(){clearball()},3);
	            //清空
	           
	            return;
	        }
	    });
	}
	
	function getIssues(c){
		var multiple = $('#multiple').val();
		var issues=[];
		for(var i=0;i<c;i++){
			issues.push([parseInt($("#currentIssue").html())+i,multiple]);		
		}
		return issues;
	}
	
	function clearball() {
		$('#div_bets').children().remove();
		lottery="";
		clearItem("lottery");
		$('.fot_a_bot em:eq(0)').html(0);
	    $('.fot_a_bot em:eq(1)').html(0);
	}