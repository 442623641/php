var effective;
var DevcieId;
var userID;
var phonetimer = null;
var numTimer;
$(document).ready(function () {
    effective = getQueryString("Effective");
    if (effective != null && effective.indexOf("{userid}") >= 0) {
        effective = null;
    }
    var isMobile = {
        Android: function () {
            return navigator.userAgent.match(/Android/i) ? true : false;
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i) ? true : false;
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i) ? true : false;
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i) ? true : false;
        },
        WeiXin: function () {
            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == 'micromessenger') {
                return true;
            } else {
                return false;
            }
        },
        any: function () {
            return (isMobile.Android() || isMobile.WeiXin() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
        }
    };
    if (isMobile.any()) {
        var rUrl = "mobileRegister.html";
        var ddd = getQueryString("Device");
        if (ddd == null) {
            ddd = "";
        }
        if (effective != null && effective != "") {
            rUrl += "?Effective=" + effective + "&Device=" + ddd;
        }
        else {
            rUrl += "?Device=" + ddd;
        }
        window.location.href = rUrl;
    }

});
$(function () {
    if (effective != null && effective != "") {
        GetUserInfo("", effective);
    }
    InitReg();
});

//预注册获取一个未注册状态userId
function RegistrationNo() {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../Do.ashx",
        async: false,
        data: { cls: "UserInfoWeb", fun: "RegistrationNo" },
        success: function (json) {
            if (json.Status == "access") {
                userID = json.Data.Id;
            }
            else {
                AlertMsg(json.Desc);
            }
        }
    });
}


//获取url参数值
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)
        return unescape(r[2]);
    return null;
}

//发送短信验证码
function SendPhoneCheck() {
    if ($(".send_yzm").attr("t") == "0") {
        $(".send_yzm").attr("t", "1").removeClass("get_yzm");
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "../Do.ashx",
            data: { cls: "UserInfoWeb", fun: "SendPhoneCheck", Phone: $("#txtMoblePhone").val() },
            success: function (json) {
                if (json.Status == "access") {
                    //   1分钟
                    numTimer = 60;
                    if (phonetimer == null) {
                        phonetimer = setInterval(TimeAddSecond, 1000);
                    }
                }
                else {
                    $(".send_yzm").attr("t", "0");
                    AlertMsg(json.Desc);
                }
            }
        });
    }
}

function TimeAddSecond() {
    numTimer--;
    var objSend = $(".send_yzm");
    if (numTimer <= 0) {
        window.clearInterval(phonetimer);
        phonetimer = null;
        if (!objSend.hasClass("get_yzm")) {
            objSend.addClass("get_yzm");
        }
        objSend.attr("disabled", false).html("获取验证码");
    }
    else {
        objSend.html("重新获取(" + numTimer + ")");
    }
}

//注册
function Registration() {
    var postdata = {};
    postdata.UserName = $("#txtUserName").val();
    if (postdata.UserName.length == 0) {
        $("#spUserName").html("*用户姓名不能为空");
        return;
    }
    postdata.IdCrad = $("#txtIdCrad").val();
    if (!idCard(postdata.IdCrad)) {
        $("#spIdCrad").html("*身份证号码格式错误");
        return;
    }
    postdata.MoblePhone = $("#txtMoblePhone").val();
    if (postdata.MoblePhone.length > 0) {
        if (!RegPhone(postdata.MoblePhone)) {
            $("#spMoblePhone").html("*手机号码格式错误");
            return;
        }
    }
    postdata.Verification = $("#txtVerification").val();

    if (postdata.MoblePhone.length > 0) {
        if (postdata.Verification.length == 0) {
            $("#spVerification").html("*短信验证码不能为空");
            return;
        }
    }
    postdata.Passwd = $("#txtPasswd").val();
    if (postdata.Passwd.length < 6) {
        $("#spPasswd").html("*密码长度不足6位");
        return;
    }
    postdata.rpasswd = $("#txtRPasswd").val();
    if (postdata.rpasswd != postdata.Passwd) {
        $("#spRPasswd").html("*两次输入密码不一致");
        return;
    }
    //RegistrationNo();
    //if (userID > 0) {
    //    postdata.UserId = userID;
    //}
    postdata.rpasswd = $.md5(postdata.rpasswd).toUpperCase();
    postdata.Passwd = $.md5(postdata.Passwd).toUpperCase();
    if (effective != null && effective != "") {
        postdata.Effective = effective;
    }
    postdata.IsIntermediary = "191";
    if (DevcieId == null || DevcieId == "" || getQueryString("Devcie").indexOf("{Device}") < 0) {
        DevcieId = getQueryString("Devcie");
    }
    postdata.Device = DevcieId;
    //postdata.UserType = "10";
    postdata.cls = "UserInfoWeb";
    postdata.fun = "Registration";
    if (effective == "" && $("#txtEffective").val() != "") {
        if (confirm("推荐人无效，是否继续注册？")) {
            r(postdata);
        }
    }
    else {
        r(postdata);
    }
}

function r(postdata) {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../Do.ashx",
        data: postdata,
        success: function (json) {
            if (json.Status == "access") {
                AlertMsg("注册成功");
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "../Do.ashx",
                    data: { cls: "UserInfoWeb", fun: "Login", device: 235, UserName: $("#txtIdCrad").val(), Passwd: $.md5(postdata.Passwd).toUpperCase() },
                    success: function (json) {
                        if (json.Status == "access") {
                            var jsonData = { Userinfo: json.Data[2], Token: json.Data[1].token, DeviceItem: 235, UserInfoEx: json.Data[3], Power: null };
                            SetCookie("token", JSON.stringify(jsonData));
                            window.location.href = "index.aspx";
                        }
                        else {
                            window.location.href = "login.html";
                        }
                    }
                });
            }
            else {
                AlertMsg(json.Desc);
            }
        }
    });
}

//通过用户Id或者手机号或者身份证号码后区用户id以及用户姓名
function GetUserInfo(numStr, id) {
    var postdata = {};
    postdata.cls = "UserInfoWeb";
    postdata.fun = "GetUserInfo";
    if (id != "") {
        postdata.userId = id;
    }
    else if (RegPhone(numStr)) {
        postdata.MoblePhone = numStr;
    }
    else {
        postdata.IdCrad = numStr;
    }
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "../Do.ashx",
        data: postdata,
        success: function (json) {
            if (json.Status == "access") {
                if ($("#txtEffective").val() == numStr || id != "") {
                    if (json.Data.UserName != null && json.Data.UserName.length > 0) {
                        effective = json.Data.UserId;
                        var objsp = $("#spEffective");
                        if (objsp.hasClass("color_red")) {
                            objsp.removeClass("color_red");
                        }
                        objsp.html("推荐人：" + json.Data.UserName);
                        if (id != "") {
                            $("#txtEffective").val(json.Data.UserName).attr("disabled", true);
                            objsp.html("");
                        }
                        else {
                            objsp.html("推荐人：" + json.Data.UserName);
                            DevcieId = json.Data.Devcie;
                        }
                    }
                }
            }
            else {
                AlertMsg(json.Desc);
            }
        }
    });
}

function RegPhone(pho) {
    if (pho.length != 11)
        return false;
    var phone = /^(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/;
    return phone.test(pho)
}

function InitReg() {
    $("#txtUserName").keyup(function () {
        var obj = $("#spUserName");
        if (this.value.length == 0) {
            obj.html("*用户姓名不能为空");
        }
        else {
            obj.html("");
        }
    });


    $("#txtIdCrad").keyup(function () {
        this.value = this.value.replace(/[^\d|x|X]/g, '');
        if (this.value == "")
            return;
        var obj = $("#spIdCrad");
        if (!idCard(this.value)) {
            effective = "";
            obj.html("*身份证号码格式错误");
        } else {
            obj.html("");
        }
    });

    $("#txtMoblePhone").keyup(function () {
        var obj = $("#spMoblePhone");
        var objSend = $(".send_yzm");
        var objv = $("#txtVerification");
        this.value = this.value.replace(/[^\d]/g, '');
        if (!RegPhone(this.value)) {
            obj.html("*手机号码格式错误");
            if (objSend.hasClass("get_yzm")) {
                objSend.removeClass("get_yzm");
            }
            objv.attr("disabled", true);
            objSend.attr("t", "1");
        } else {
            obj.html("");
            objSend.addClass("get_yzm");
            objv.attr("disabled", false);
            objSend.attr("t", "0");
        }
    });
    $("#txtVerification").keyup(function () {
        this.value = this.value.replace(/[^\d]/g, '');
        var obj = $("#spVerification");
        if (this.value.length == 0) {
            obj.html("*短信验证码不能为空");
        }
        else {
            obj.html("");
        }
    });

    $("#txtEffective").keyup(function () {
        this.value = this.value.replace(/[^\d|x|X]/g, '');
        var obj = $("#spEffective");
        if (this.value == "") {
            effective = "";
            obj.html("");
            return;
        }
        if (!(RegPhone(this.value) || idCard(this.value))) {
            effective = "";
            obj.html("手机号码或者身份证号码格式错误");
            if (!obj.hasClass("color_red")) {
                obj.addClass("color_red");
            }
        } else {
            GetUserInfo(this.value, "");
            obj.html("");
        }
    });

    $("#txtPasswd").keyup(function () {
        var obj = $("#spPasswd");
        var rs = /^[A-Za-z0-9]+$/;
        //this.value = this.value.replace(/[^\d]/g, '');
        if (!rs.test(this.value)) {
            obj.html("*密码只能输入字母和数字");
        } else if (this.value.len < 6) {
            obj.html("*密码长度不足6位");
        }
        else {
            obj.html("");
        }
    });

    $("#txtRPasswd").keyup(function () {
        var obj = $("#spRPasswd");
        var pas = $("#txtPasswd").val();
        if (pas != this.value) {
            obj.html("*两次输入密码不一致");
        } else {
            obj.html("");
        }
    });

}

function idCard(value) {
    if (value.length == 18 && 18 != value.length) return false;
    var number = value.toLowerCase();
    var d, sum = 0, v = '10x98765432', w = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2], a = '11,12,13,14,15,21,22,23,31,32,33,34,35,36,37,41,42,43,44,45,46,50,51,52,53,54,61,62,63,64,65,71,81,82,91';
    var re = number.match(/^(\d{2})\d{4}(((\d{2})(\d{2})(\d{2})(\d{3}))|((\d{4})(\d{2})(\d{2})(\d{3}[x\d])))$/);
    if (re == null || a.indexOf(re[1]) < 0) return false;
    if (re[2].length == 9) {
        number = number.substr(0, 6) + '19' + number.substr(6);
        d = ['19' + re[4], re[5], re[6]].join('-');
    } else d = [re[9], re[10], re[11]].join('-');
    if (!isDateTime.call(d, 'yyyy-MM-dd')) return false;
    for (var i = 0; i < 17; i++) sum += number.charAt(i) * w[i];
    return (re[2].length == 9 || number.charAt(17) == v.charAt(sum % 11));
}

var isDateTime = function (format, reObj) {
    format = format || 'yyyy-MM-dd';
    var input = this, o = {}, d = new Date();
    var f1 = format.split(/[^a-z]+/gi), f2 = input.split(/\D+/g), f3 = format.split(/[a-z]+/gi), f4 = input.split(/\d+/g);
    var len = f1.length, len1 = f3.length;
    if (len != f2.length || len1 != f4.length) return false;
    for (var i = 0; i < len1; i++) if (f3[i] != f4[i]) return false;
    for (var i = 0; i < len; i++) o[f1[i]] = f2[i];
    o.yyyy = s(o.yyyy, o.yy, d.getFullYear(), 9999, 4);
    o.MM = s(o.MM, o.M, d.getMonth() + 1, 12);
    o.dd = s(o.dd, o.d, d.getDate(), 31);
    o.hh = s(o.hh, o.h, d.getHours(), 24);
    o.mm = s(o.mm, o.m, d.getMinutes());
    o.ss = s(o.ss, o.s, d.getSeconds());
    o.ms = s(o.ms, o.ms, d.getMilliseconds(), 999, 3);
    if (o.yyyy + o.MM + o.dd + o.hh + o.mm + o.ss + o.ms < 0) return false;
    if (o.yyyy < 100) o.yyyy += (o.yyyy > 30 ? 1900 : 2000);
    d = new Date(o.yyyy, o.MM - 1, o.dd, o.hh, o.mm, o.ss, o.ms);
    var reVal = d.getFullYear() == o.yyyy && d.getMonth() + 1 == o.MM && d.getDate() == o.dd && d.getHours() == o.hh && d.getMinutes() == o.mm && d.getSeconds() == o.ss && d.getMilliseconds() == o.ms;
    return reVal && reObj ? d : reVal;
    function s(s1, s2, s3, s4, s5) {
        s4 = s4 || 60, s5 = s5 || 2;
        var reVal = s3;
        if (s1 != undefined && s1 != '' || !isNaN(s1)) reVal = s1 * 1;
        if (s2 != undefined && s2 != '' && !isNaN(s2)) reVal = s2 * 1;
        return (reVal == s1 && s1.length != s5 || reVal > s4) ? -10000 : reVal;
    }
};