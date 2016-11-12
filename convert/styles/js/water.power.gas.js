/**
 * Created by 卞治华 on 15-8-6.
 */
var WaterPowerGas = WaterPowerGas || {};

WaterPowerGas.baseUrl = APP_CONFIG.WaterPowerGas_URL;

var len = 0;
//存放省份信息
var title = ['&nbsp;&nbsp;&nbsp;省份&nbsp;&nbsp;' , '&nbsp;&nbsp;地级市&nbsp;'];//, '市、县、区'
/*
 * 预加载省份等数据,可以融入GPRS实时定位，来获取省分与城市信息
 */
$(document).ready(function () {
    WaterPowerGas.jiaoFeiType = $('#type').val();//缴费类型：水电燃气
    $.each(title, function (k, v) {
        title[k] = '<option value="">' + v + '</option>';
    })
    $('#loc_province').append(title[0]);
    $('#loc_city').append(title[1]);
    WaterPowerGas.init();
});

/*
 * 1.点击省份下拉选项菜单项,来初始化城市下拉选项
 */
WaterPowerGas.provinceChange = function setCity() {
    $('#loc_city').empty();
    $('#loc_payUnit').empty();
    $('#loc_city').append(title[1]);
    WaterPowerGas.citys($('#loc_province').val());//城市初始化
}

/*
 * 2.点击城市下拉选项菜单项，根据省份id和城市id查询支持的缴费类型
 */
WaterPowerGas.cityChange = function setRechargeType() {
    $('#loc_payUnit').empty();
    WaterPowerGas.reRechargeType($('#loc_province').val(), $('#loc_city').val());
    WaterPowerGas.fillOption('loc_city', data['result']);
}

/*
 * 3.点击缴费单位下拉选项菜单项，根据省份ID、城市ID、充值类型、缴费单位 获取水电煤商品信息
 */
WaterPowerGas.payUnitChange = function setQuery() {
    WaterPowerGas.payUnitId = $('#loc_payUnit').val();
    WaterPowerGas.query(WaterPowerGas.provinceId, WaterPowerGas.cityId, WaterPowerGas.payProjectId, WaterPowerGas.payUnitId);//为后面查询做准备
}

//gps定位与缴费省份初始化
WaterPowerGas.init = function initProvinceAndPosition() {
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicProvinceInfo', //'<?php echo site_url("allPay/getPublicProvinceInfo");?>'
        //{"reason":"验证通过,可以送单","result":{"price":"197.900","time":"2015-07-29 09:21:47"},"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                WaterPowerGas.fillOption('province', data['result']);
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {//alert("请求异常！");
            data = {"reason": "查询成功", "result": [
                {"provinceId": "v1904", "provinceName": "黑龙江"},
                {"provinceId": "v1918", "provinceName": "湖南"},
                {"provinceId": "v2091", "provinceName": "贵州"},
                {"provinceId": "v2159", "provinceName": "山西"},
                {"provinceId": "v2043", "provinceName": "内蒙古"},
                {"provinceId": "v2056", "provinceName": "江苏"},
                {"provinceId": "v2070", "provinceName": "重庆"},
                {"provinceId": "v2210", "provinceName": "山东"},
                {"provinceId": "v2129", "provinceName": "甘肃"},
                {"provinceId": "v2307", "provinceName": "全国"},
                {"provinceId": "v2228", "provinceName": "广东"},
                {"provinceId": "v2186", "provinceName": "江西"},
                {"provinceId": "v2118", "provinceName": "广西"},
                {"provinceId": "v2280", "provinceName": "上海"},
                {"provinceId": "v2171", "provinceName": "辽宁"},
                {"provinceId": "v2297", "provinceName": "福建"},
                {"provinceId": "v2269", "provinceName": "陕西"},
                {"provinceId": "v2198", "provinceName": "河北"},
                {"provinceId": "v1953", "provinceName": "北京"},
                {"provinceId": "v1933", "provinceName": "四川"},
                {"provinceId": "v1955", "provinceName": "浙江"},
                {"provinceId": "v1967", "provinceName": "河南"},
                {"provinceId": "v2101", "provinceName": "安徽"},
                {"provinceId": "v2250", "provinceName": "青海"}
            ], "error_code": 0};
            WaterPowerGas.fillOption('province', data['result']);
        }
    });
}

//缴费城市初始化
WaterPowerGas.citys = function getCitysByProvincedId(id) {
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: id
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicCityInfo', // '<?php echo site_url("allPay/getPublicCityInfo");?>'
        success: function (data) {
            if (data['error_code'] == 0) {
                WaterPowerGas.fillOption('city', data['result']);
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//缴费类型初始化
WaterPowerGas.reRechargeType = function getRechargeTypeByProvIdCityId(provinceId, cityId) {
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: provinceId,
            cityId: cityId
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicRechargeType',// '<?php echo site_url("allPay/getPublicRechargeType");?>'
        //{"reason":"查询成功","result":[{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2680","payProjectName":"电费"},{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payProjectName":"水费"}],"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                len = data['result'].length;//缴费
                var payProjectId = '';
                $.each(data['result'], function (k, v) {
                    if (WaterPowerGas.jiaoFeiType === v['payProjectName']) {
                        payProjectId = v['payProjectId'];
                        return false;
                    }
                })
                if (payProjectId === '') {//$('input[name="RadioGroup1"]:checked').val()
                    var provinceName = $("#loc_province  option:selected").text();
                    var cityName = $("#loc_city option:selected").text();
                    alert(provinceName+'省'+ + cityName + '市暂不支持缴纳' + WaterPowerGas.jiaoFeiType);//水电燃气，北京北京市，与上海上海市都支持
                } else {//缴费单位初始化
                    WaterPowerGas.unit(provinceId, cityId, payProjectId);
                }
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//缴费单位初始化
WaterPowerGas.unit = function unit(provinceId, cityId, payProjectId) {
    WaterPowerGas.provinceId = provinceId;
    WaterPowerGas.cityId = cityId;
    WaterPowerGas.payProjectId = payProjectId;
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: provinceId,
            cityId: cityId,
            payProjectId: payProjectId
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicUnit',// '<?php echo site_url("allPay/getPublicUnit");?>',
        //{"reason":"查询成功","result":[{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payUnitId":"v83497","payUnitName":"合肥市水费"}],"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                WaterPowerGas.fillOption('payUnit', data['result']);
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//缴费单位商品信息查询
WaterPowerGas.query = function query(provinceId, cityId, payProjectId, payUnitId) {
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: provinceId,
            cityId: cityId,
            payProjectId: payProjectId,
            payUnitId: payUnitId
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicProductInfo', //'<?php echo site_url("allPay/getPublicProductInfo");?>',
        //{"reason":"查询成功","result":{"productId":"64318001","productName":"安徽 合肥市水费 水费 户号 直充任意充","inprice":[]},"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                WaterPowerGas.productId = data['result']['productId'];
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//账户余额查询
WaterPowerGas.mbalance = function getMbalance() {
    var provinceName = $("#loc_province  option:selected").text();
    var cityName = $("#loc_city option:selected").text();
    var payUnitName = $("#loc_payUnit option:selected").text();
    var account = $("#account").val();
    var type = (WaterPowerGas.jiaoFeiType === '水费') ? '001' : ((WaterPowerGas.jiaoFeiType === '电费') ? '002' : '003');
    if (account === '') {
        alert('查询用户账户不能为空！');
        //  $("#account").focus;
        return;
    }
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            type: type,
            account: account,
            cityName: cityName,
            provinceName: provinceName,
            payUnitName: payUnitName,
            payUnitId: WaterPowerGas.payUnitId,
            productId: WaterPowerGas.productId,
            paymodeId: ''
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicMbalance',//'<?php echo site_url("allPay/getPublicMbalance");?>',
        //{"reason":"查询成功","result":{"account":"5132827118","balances":{"balance":{"accountName":"合*司","balance":"0.00","contractNo":"34401"}}},"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                var balance = data['result']['balances']['balance']['balance'];
                var name = data['result']['balances']['balance']['accountName'];
                WaterPowerGas.balance = balance;
                $('#userName').html('充值账户用户名:' + name);
                $('#ownMoney').html(balance + '元');
                //  $('#payMoney').focus;//焦点定位
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//公共事业缴费方式查询 - 暂时没有使用
WaterPowerGas.payMode = function payMode(provinceId, cityId, payProjectId, payUnitId) {
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: provinceId,
            cityId: cityId,
            payProjectId: payProjectId,
            payUnitId: payUnitId
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicPayMode',//'<?php echo site_url("allPay/getPublicPayMode");?>',
        //{"reason":"查询成功","result":{"payMode":{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payUnitId":"v83497","payModeId":"v2620","payModeName":"户号"}},"error_code":0}
        success: function (data) {
            if (data['error_code'] == 0) {
                var payModeId = data['result']['payMode']['payModeId'];
                var payModeName = data['result']['payMode']['payModeName']
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//公共事业充值 - 公司账户该个人账户余额有多少？再进行计算？
WaterPowerGas.order = function reCharge() {
    var account = $("#account").val();
    var payMoney = $("#payMoney").val();
    var ownMoney = WaterPowerGas.balance;
    //console.log(' account='+account+'\t payMoney='+payMoney);
    if (account == '') {
        alert('查询用户账户不能为空！');
        //$("#account").focus;
        return;
    }
    if (payMoney == '') {
        alert('缴纳金额不能为空');
        //$("#payMoney").focus;
        return;
    }
    if (typeof(ownMoney) == 'undefined') {//没有查寻账户余额而直接支付
        alert("请先选择省市与相关缴费单位，并进行记号查询，确认该用户存在");
        return;
    }
    if (payMoney <= ownMoney) {//充值金额小于应付金额(1.查询公司该用户账户余额。2.第三方支付。)
        alert('缴纳金额必须大于所欠金额');
        //$("#payMoney").focus;
        return;
    }
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            provinceId: WaterPowerGas.productId,
            cityId: WaterPowerGas.cityId,
            payProjectId: WaterPowerGas.payProjectId,
            payUnitId: WaterPowerGas.payUnitId,
            cardId: WaterPowerGas.productId,//(水电煤商品信息查询)返回：productId
            account: account,//水电煤户号／条形码号
            contract: '',//合同号, （通过欠费接口查询，查到必传，查不到就不传） 非必填
            payMentDay: '',//账期，（通过欠费接口查询，查到必传，查不到就不传） 非必填
            //orderId:orderId, //自定义订单号（8-32位数字字母）,由后台生成
            cardNum: payMoney//充值金额（单位：元，保留小数点后2位），例如198.35元
        },
        url: WaterPowerGas.baseUrl + 'allPay/getPublicOrder',//'<?php echo site_url("allPay/getPublicOrder");?>',
        //{"reason": "提交充值成功","result":{"cardid": "64323301","cardnum": "1","ordercash": "1","cardname": "江苏 苏州市电费  户号 直充任意充","sporderId": "P20141211203444313","account": "6200185388","uorderid":"13345680","status": "0"},"error_code": 0}
        success: function (data) {
            if (data['error_code'] == 0) {
                alert('充值成功！');
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//水电燃气费订单状态 - 未作处理
WaterPowerGas.orderStatus = function orderStatus() {
    var orderId = '';
    if (typeof(ownMoney) == 'undefined') { //没有查寻账户余额而直接支付
        alert("请先选择省市与相关缴费单位，并进行户号查询，以便确认该用户是否存在");
        return;
    }
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            orderId: orderId//订单号
        },

        url: WaterPowerGas.baseUrl + 'allPay/getPublicOrderStatus',//'<?php echo site_url("allPay/getPublicOrderStatus");?>',
        //{"reason": "提交充值成功","result":{"uordercash": "5.000","sporder_id": "20150511163237508", "game_state": "1"},"error_code": 0}
        success: function (data) {
            if (data['error_code'] == 0) {
                var uordercash = data['result']['uordercash'];//订单扣除金额
                var sporder_id = data['result']['sporder_id'];//聚合订单号
                var game_state = data['result']['game_state'];//状态 1:成功 9:失败 0：充值中
            } else {
                alert(data['reason']);
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}
/*
 * 下拉列表框，内容填充（省、市、缴费单位）
 */
WaterPowerGas.fillOption = function (el_id, data) {
    var el = $('#loc_' + el_id);
    if (data) {
        var value = '';
        $.each(data, function (k, v) {// k=0,1,2,3,4……; v=Object {provinceId: "v1918", provinceName: "湖南"}
            var option = '<option value="' + v[el_id + 'Id'] + '">' + v[el_id + 'Name'] + '</option>';
            el.append(option);
        })
    }
    if (el_id === 'payUnit') {
        WaterPowerGas.payUnitId = $('#loc_' + el_id).val();
        WaterPowerGas.query(WaterPowerGas.provinceId, WaterPowerGas.cityId, WaterPowerGas.payProjectId, WaterPowerGas.payUnitId);//为后面查询做准备
    }
}

//检查输入数据类型,保留小数点后2位
WaterPowerGas.checkNumber = function checkNumber() {
    var number = $("#payMoney").val();
    if (isNaN(number)) {//检查是否是数字值
        alert("仅能输入数据！");
        return;
    }
    number = number.toString();
    if (number.split(".").length > 1 && number.split(".")[1].length > 2) {
        alert("小数点后多于两位！");
        return;
    }
}