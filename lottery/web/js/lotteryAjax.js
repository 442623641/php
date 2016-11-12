//获取当前期号信息
function setIssue(url){
		baseUrl=url;
        $.ajax({
            type: "post",
            url: baseUrl+"/api/LotteryIssue/currentIssue",
            data: { lotteryID: lottery.code },
            dataType:"json",
            success: function (json) {
                if(json.fail==undefined&&json.fail!=0){return;}
                 $("#currentIssue").html(json.data.issue);
                 countDown(json.data.stopTime); 
            }
        });
}
//获取最新的开奖号码
function getLottery(){
    $.ajax({
        type: "post",
        url: "ajax/11x5.ashx?kjtypeID=22",
        data: { m: 'a' },
        success: function (result) {
        }
    });
}
//投注
function performance(){
    $.ajax({
        type: "post",
        url: baseUrl+"/api/LotteryUser/add",
        data: { 
    			lotteryID: lottery.code,    			
    	},
        dataType:"json",
        success: function (json) {
            if(json.fail==undefined&&json.fail!=0){return;}
             $("#currentIssue").html(json.data.issue);
             countDown(json.data.stopTime); 
        }
    });
}