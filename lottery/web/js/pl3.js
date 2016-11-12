//lottery=new lottery_model('排列3',1);
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
                lottery.issues=[[parseInt(json.data.issue)]];
                $("#currentIssue").html(json.data.issue);
                countDown(json.data.stopTime); 
            }
        });
}
//投注
function performance(){
	//alert('\u5f53\u524d\u4f59\u989d\u4e0d\u8db3');
	var text=[];
	var issues=[];
	var isWinners;
	var data=null;
	if(lottery.count==0){
		msg("你还没有选号","warn","",3);
		return;
	}
	$(lottery.bets).each(function(){
		var item=[];
		item.push(this.code);
		item.push(this.text.join('|'));
		item.push(this.count);
		item.push(this.money);
		text.push(item);
	});
	var urle="/api/LotteryUser/add";
	//追號
	if(lottery.issueCount>1){
		urle="/api/LotteryChase/add";
		data={issue:$("#currentIssue").html(),
				userID:'10017',
	    		multiple:1,
	    		lotteryID:lottery.code,
	    		text:JSON.stringify(text),
	    		money:lottery.money,
	    		issues:JSON.stringify(getIssues(lottery.issueCount)),
	    		lotteryID:lottery.code,	
	    		isWinners:document.getElementById("isWinner").checked?1:0}
	}else{
		data={issue:$("#currentIssue").html(),
				userID:'10017',
	    		multiple:lottery.multiple,
	    		lotteryID:lottery.code,
	    		text:JSON.stringify(text),
	    		money:lottery.money,    		
	    		lotteryID:lottery.code}
	}
	var s=JSON.stringify(text);
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
	var issues=[];
	for(var i=0;i<lottery.issueCount;i++){
		issues.push([parseInt($("#currentIssue").html())+i,lottery.multiple]);		
	}
	return issues;
}
// 选号按钮点击事件
function ball_click() {
    $(".box_c .gtable tr td a").bind("click", function () {
        var obj = $(this);
        if (!obj.hasClass("curr")) {
        	//$(".gtable tr:lt("+$(this).parent().parent().index()+")").children('tr td .curr').each(function(index,e){ if $(this).html()==obj..html();).$(this)})
            obj.addClass("curr");
        }
        else {
            obj.removeClass("curr");
        }
        bet.getBet();
   
    });
}
//清除所选球
function ball_clear() {
    $(".rxrx li a").removeClass("curr");
}

//确认选号
function addbet(url) {
	if(bet.count>0){
		if(getItem("lottery")){
			lottery.instance(getItem("lottery"));	
		}
		lottery.append(bet);
		setItem("lottery",lottery);
	}
	location.href=url;
    return;
}
//随机
function bet_random(num) {
    for (var i = 0; i < num; i++) {
        bet.random(5);
        addBall();
    }
}
//获取页面数据
bet_model.prototype.getBet = function () {
    this.initialize();
    //var arr = $(".div_ball .block .curr");
    var textArr=[];
    var arr=$(".div_ball .block .box_c .gtable");
    arr.each(function(){
    	var temptext = [];
    	$(this).find('td .curr').each(function () {
            temptext.push($(this).html());
        });
    	textArr.push(temptext.sort().join(","));
    });
    this.text = textArr;
    this.count = this.getCount();
    this.money = this.count * 2;
    $('.fot_a_bot tr td em:eq(0)').html(bet.count);
    $('.fot_a_bot tr td em:eq(1)').html(bet.money);
}
//设置页面数据
bet_model.prototype.setBet = function () {
	//$("div:contains('123')[innerHTML='123']").length
	var arr=$(".div_ball .block .box_c .gtable tr");
    //var arr = $(".div_ball .block .curr");
    //var textArr=[];
	$(this.text).each(function(index,e){
		var ball=e.split(',');
		for(var i=0;i<ball.length;i++){
			if(arr.length>2){
				var m=parseInt(ball[i]);
				if(m<6){
					$(arr[1]).find("td .btn_e").eq(m).addClass('curr');
				}else{
					$(arr[2]).find("td .btn_e").eq(m).addClass('curr');
				}
			}
		}
	})
    $('.fot_a_bot tr td em:eq(0)').html(this.count);
    $('.fot_a_bot tr td em:eq(1)').html(this.money);
}
//加载投注内容
function loophtml(){
	$('#div_bets').children().remove();
	var html=[];
	var bets=lottery.bets;
	for(var i=bets.length-1;i>=0;i--)
	{
		html.push('<div class="syxw_list"><input type="hidden" id="betcode" value="'+bets[i].code+
				'"><a href="javascript:void(0);" onclick="del(this); return false;" class="syxw_list_close"></a>'+
				'<font class="red fot_14">'+bets[i].text.join('|')+
				'</p></font><p class="mb_05 fot_12">'+bets[i].name+'&nbsp; '+bets[i].count+'注 '+bets[i].money+
				'元<a href="'+baseUrl+'/pl3/index?index='+i+'" class="syxw_list_go"></a>'+
				'</div>');
	}
	    $('#div_bets').prepend(html.join(""));
	    setItem("lottery",lottery);
	    setFoot();
}
function setFoot(){
    $('.fot_a_bot em:eq(0)').html(lottery.money);
    $('.fot_a_bot em:eq(1)').html(lottery.count);
    $('.fot_a_bot em:eq(2)').html(lottery.issueCount);
    $('.fot_a_bot em:eq(3)').html(lottery.multiple);
}
//随机
//生成不重复的随机号码
lottery_model.prototype.random = function () {
	var bet= new bet_model(this.bets.length<1?0:this.bets[0].code);
    var arr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    
    switch(bet.name){
	case "直选":
		bet.text=[Math.floor(Math.random()*9)+1,Math.floor(Math.random()*9)+1,Math.floor(Math.random()*9)+1];
		break;
	case "组三":
		arr.sort(function () { return 0.5 - Math.random(); });
		bet.text[0]=arr.slice(0, 2).sort();
		break;
	case "组六":
		arr.sort(function () { return 0.5 - Math.random(); });
		bet.text[0]=arr.slice(0, 3).sort();
		break;
	default:
		bet.text=[Math.floor(Math.random()*9)+1,Math.floor(Math.random()*9)+1,Math.floor(Math.random()*9)+1];
		break;
	}
    bet.count = 1;
    bet.money = 2;
    return this.append(bet); 
}
//随机
function clearball() {
	$('#div_bets').children().remove();
	lottery="";
	clearItem("lottery");
	$('.fot_a_bot em:eq(0)').html(0);
    $('.fot_a_bot em:eq(1)').html(0);
}
//随机
function random() {
	lottery.random();
	loophtml();
}
function del(self) {
    var obj = $(self).parent();
    var index = parseInt(lottery.bets.length) - parseInt(obj.index())- 1;
    lottery.remove(index);
    $(self).parent().remove();
    $('.fot_a_bot em:eq(0)').html(lottery.count);
    $('.fot_a_bot em:eq(1)').html(lottery.money);
    //loophtml();
}
function countDown(time) {
	 var end_time = new Date(time).getTime(),//月份是实际月份-1 
	 sys_second = (end_time - new Date().getTime()) / 1000;
    var timer = setInterval(function () {
        if (sys_second > 1) {
            sys_second -= 1;
            var hour = Math.floor((sys_second /3600)%24);
            var min = Math.floor((sys_second / 60) % 60);
            var sec = Math.floor(sys_second % 60);
            hour=hour<10?"0"+hour:hour;
            min = min > 10 ? min: "0" + min;
            sec = sec > 10 ? sec: "0" + sec;
            $("#timer").html('<em class="red">'+hour+'</em>时<em class="red">'+min+'</em>分<em class="red">'+sec+'</em>秒');
        } else {
            clearInterval(timer);
            msg("第" + $("#currentIssue").html() + "期已截止，当前期是第 " + (parseInt($("#currentIssue").html()) + 1) + " 期", "warn", function () { setIssue(baseUrl);},5);
        }
    }, 1000);
}