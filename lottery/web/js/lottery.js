function setItem(key,obj){
	if(window.localStorage){
		localStorage[key] = JSON.stringify(obj);
		
	}else{
		var date=new Date(); 
		date.setTime(date.getTime()+30*60*1000); //设置date为当前时间+30分
		$.cookie(key, JSON.stringify(obj), { expires: date });
	}
}
function getItem(key){
	if(window.localStorage){
		var s=localStorage[key];
	}else{
		var s=$.cookie(key);
	}
	return s==undefined?null:JSON.parse(s);
}
function clearItem(key){
	if(window.localStorage){
		return localStorage.removeItem(key);
	}else{
		return $.cookie(key, '', { expires: -1 });
	}
}
function bet_model(code) {
	//this.desc=desc;
	//this.id=1;
    this.code = code;
    //this.name = name;
    this.text = [];
    this.count = 0;
    this.money = 0;
    switch(code){
    	case 0:
	    	this.name = "直选";
	    	this.min=1;
	    	break;
    	case 1:
   	 		this.name = "组三";
   	 		this.min=2;
   	 		break;
    	case 2:
   	 		this.name = "组六";
   	 		this.min=3;
   	 		break;
   	 	default:
   	 		break;
    }
}
function lottery_model(name, code) {
    this.code = code;
    this.name = name;
    this.bets = [];
    this.count = 0;
    this.money = 0;
    this.multiple = 1;
    this.issueCount=1;
}

//加入投注集合
lottery_model.prototype.append = function (b) {
	if(this.count==0){	
		this.bets=[];
	}
	this.bets.push(b);
    this.count +=b.count;
    this.money = this.count*this.multiple*this.issueCount*2;
}
lottery_model.prototype.setMultiple = function (m) {
	this.multiple=parseInt(m);
	this.money = this.count*this.multiple*this.issueCount*2;
}
lottery_model.prototype.setIssue = function (m) {
	this.issueCount=parseInt(m);
	this.money = this.count*this.multiple*this.issueCount*2;
}
lottery_model.prototype.instance = function (b) {
	if(b!=null){
	 	this.code = b.code;
	    this.name = b.name;
	    this.bets = b.bets;
	    this.count = b.count;
	    this.money = b.money;
	    this.multiple = b.multiple;
	    this.issueCount = b.issueCount;
	}
}
bet_model.prototype.instance = function (b) {
	if(b!=null){
	 	this.code = b.code;
	    this.name = b.name;
	    this.text = b.text;
	    this.count = b.count;
	    this.money = b.money;
	}
}
bet_model.prototype.instance = function (b) {
	if(b!=null){
	    this.code = b.code;
	    this.name = b.name;
	    this.text = b.text;
	    this.count = b.count;
	    this.money = b.money;
	    this.min = b.min;
	}else{
		   this.text = [];
		   this.count = 0;
		   this.money = 0;
		   this.min = 2;
		   this.name="选三";	
		   this.code=1;
	}
}
//删除投注
lottery_model.prototype.remove = function (index) {
    var b = this.bets[index];
    this.bets.splice(index, 1);
    this.count -= b.count;
    this.money = this.count*this.multiple*this.issueCount*2;
    setItem("lottery",this);
}
//计算注数
bet_model.prototype.getCount= function () {
	var count=0;
	switch(this.code){
	case 0:	
		if(this.text.length==3&&this.text[0].length>=1&&this.text[1].length>=1&&this.text[2].length>=1){
			count= (this.text[0].length+1)/2*(this.text[1].length+1)/2*(this.text[2].length+1)/2;
			 
		}
		break;
	case 1:
		if(this.text[0].length>=3){
			count=combine((this.text[0].length+1)/2, 2)*2;
		}
		break;
	case 2:
		if(this.text[0].length>=5){
			count= combine((this.text[0].length+1)/2, 3);
		}
		break;
	default:
		break;
	}
	return count;
}
//初始化
bet_model.prototype.initialize= function () {
    this.text = [];
    this.count = 0;
    this.money = 0;
    this.min = 0;
}
//排列组合
function combine(m, n) {
    if (m < n || n < 0) {
        return 0;
    }
    return factorial(m, m - n + 1) / factorial(n, 1);
}
// 阶乘
function factorial(max, min) {
    if (max >= min && max > 1) {
        return max * factorial(max - 1, min);
    } else {
        return 1;
    }
}
function queryString(val) {
    var uri = window.location.search;
    var re = new RegExp("" + val + "=([^&?]*)", "ig");
    return decodeURI(((uri.match(re)) ? (uri.match(re)[0].substr(val.length + 1)) : null));
}