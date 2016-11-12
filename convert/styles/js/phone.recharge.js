/**
 * Created by 卞治华 on 15-8-6.
 */

var HuaFeiChongZhi = HuaFeiChongZhi || {};

HuaFeiChongZhi.baseUrl = APP_CONFIG.HuaFei_URL;

//充值金额
var money = 0;

//全局按钮监听
$(function () {
    //当单选按钮发生变化时重新调用realPay();
    $("input[name='RadioGroup1']").click(function () {
        //var value = $(this).attr("value");//价格
        HuaFeiChongZhi.realPay();
    });
});

//实付金额
HuaFeiChongZhi.realPay = function realPay() {
    // var money = $("#money").val();
    money = HuaFeiChongZhi.price;
    $('#huaFeiPrice').val(money);   //隱藏以便传输
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            payPrice: money
        },
        url: HuaFeiChongZhi.baseUrl+'allPay/getHuaFeiRealPayPrice',//'<?php echo site_url("allPay/getHuaFeiRealPayPrice");?>',
        //{"reason":"验证通过,可以送单","result":{"price":"197.900","time":"2015-07-29 09:21:47"},"error_code":0}
        success: function (data) {
            if (data['error_code'] != 0) {
            } else {
                var realPrice = data['result']['price'];
                var couponPrice = HuaFeiChongZhi.formatFloat(money, '-', realPrice);
                $('#payPrice').html(realPrice + '元,立省' + couponPrice + '元');
                $('#goToPay').val('共' + realPrice + '元，去充值'); //html不能用a,用input否则required无效
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//获取当前话费充值总额
HuaFeiChongZhi.price = function getPrice() {
    money = $('input[name="RadioGroup1"]:checked').val(); //获取被选中Radio的Value值
    if (money == 'else') {
        money = $("#money").val();//1:200 2:300
    }
    return money;
}

//表单提交
HuaFeiChongZhi.submit = function submit() {
    var phoneNumber = $('#phone');
    if ("" == phoneNumber.val()) {
        alert("手机号码不能为空");
        phoneNumber.focus;
        return;
    }

    var reg_tel = /^1[3|4|5|8][0-9]\d{4,8}$/;
    if (!reg_tel.test(phoneNumber.val())) {
        alert("手机号码号码不合法");
        phoneNumber.val('');
        phoneNumber.focus;
        return;
    }
    phoneNumber = phoneNumber.val();
    $.ajax({
        dataType: "json",
        type: "POST",
        data: {
            payPrice: money, phone: phoneNumber
        },
        url: HuaFeiChongZhi.baseUrl+'allPay/getHuaFeiRecharge',//'<?php echo site_url("allPay/getHuaFeiRecharge");?>',
        //{"reason":"验证通过,可以送单","result":{"price":"197.900","time":"2015-07-29 09:21:47"},"error_code":0}
        success: function (data) {
            if (data['error_code'] != 0) {
                alert(data['reason']);
            } else {
                var realPrice = data['result']['price'];
                var couponPrice = HuaFeiChongZhi.formatFloat(money, '-', realPrice);
                $('#payPrice').html(realPrice + '元,立省' + couponPrice + '元');
                $('#goToPay').val('共' + realPrice + '元，去充值'); //html不能用a,用input否则required无效
            }
        },
        error: function (data) {
            alert("请求异常！");
        }
    });
}

//当前话费充值总额计算，误差避免
HuaFeiChongZhi.formatFloat = function FloatFigure(a, symbol, b) {
    if (a.toString().indexOf(".") < 0 && b.toString().indexOf(".") < 0) {//判断是否有小数
        return eval_r(a + symbol + b);
    }
    //至少一个有小数
    var alen = a.toString().split(".");
    if (alen.length == 1) {//没有小数
        alen = 0;
    } else {
        alen = alen[1].length;
    }
    var blen = b.toString().split(".");
    if (blen.length == 1) {
        blen = 0;
    } else {
        blen = blen[1].length;
    }
    if (blen > alen) alen = blen;
    blen = "1";
    for (; alen > 0; alen--) {//创建一个相应的倍数
        blen = blen + "0";
    }
    switch (symbol) {
        case "+":
            return (a * blen + b * blen) / blen;
            break;
        case "-":
            return (a * blen - b * blen) / blen;
            break;
        case "*":
            return ((a * blen) * (b * blen)) / (blen * blen);
            break;
        default:
            alert("你要求的\t" + symbol + "\t运算未完成!");
            return eval_r(a + symbol + b);
    }
}

