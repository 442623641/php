<?php defined('BASEPATH') or exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');//解决跨域问题
#聚合云接口
class AllPay extends Home_Controller
{
    #话费
    var $juhe_huafei_base_url = 'http://v.juhe.cn/huafei/';
    var $huafei_app_key = '36e685618ddd749f6603fe932bb779e5';

    #水电煤燃气-公共服务
    var $juhe_public_base_url = 'http://op.juhe.cn/ofpay/public/';
    var $public_app_key = '593784333a5cffe15273aaf9990dfad7';

    var $type = 'json';
    var $per_value = 0;
    var $phone = 15056068286; //默认充值手机号，主要用于计算优惠额度
    var $openID = 'JH42ba9604c98b13f5780c8b4a0362eaba';

    public function __construct()
    {
        parent::__construct('xkapp.com');

        $this->load->library('umspay');
//        $this->juhe_base_url = 'http://v.juhe.cn/';
//        $this->huafei_app_key = '36e685618ddd749f6603fe932bb779e5';
//        $this->type = 'json';
//        $this->per_value = 0;
//        $this->phone = 15056068286; //默认充值手机号，主要用于计算优惠额度
//        $this->openID = 'JH42ba9604c98b13f5780c8b4a0362eaba';
        $this->load->helper('form'); //表单,只能接受以POST方式传过来的表单数据
    }

    //手机充值
    public function payFor()
    {
        $bizName = $this->input->get('bizName');
        $user = $this->session->userdata('user');
        $result = $this->umspay->charge($user, $bizName);
        // var_dump($result);
        if ($result) {
            redirect($result);
        } else {
            exit('未知错误');
        }
    }

    #分流-分到不同页面
    public function goToPay()
    {
        $bizName = $this->input->get("bizName");

        if ($bizName === 'phoneRecharge') { //话费充值
            $this->load->view('bmfw_hfcz_next.html');
            return;
        }
        if ($bizName === 'water') { //水费充值
            $this->load->view('bmfw_sfjf_next.html');
            return;
        }
        if ($bizName === 'power') { //电费充值
            $this->load->view('bmfw_dfjf_next.html');
            return;
        }
        if ($bizName === 'gas') { //燃气费充值
            $this->load->view('bmfw_rqfjf_next.html');
            http: //localhost:81/web/index.php/allPay/
            return;
        }
        if ($bizName === 'creditCard') {
            $this->load->view('bmfw_hfcz_next.html');
            return;
        }
        echo '无效请求！';
    }

    #生成唯一订单号，16位
    public function generateOrderId() //http://localhost:81/web/index.php/allPay/generateOrderId
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'); //英文字母、年月日、Unix 时间戳和微秒数、随机数
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        //echo date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //echo $orderSn;
        return $orderSn;
        //echo ' length:' . mb_strlen($orderSn);
    }

    #手机充值第一步 - 充值金额与充值手机号
    public function  phoneRechargeStep1()
    {
        // echo ' phoneRechargeStep2 ';
        $this->load->view('bmfw_hfcz_next.html');
    }

    #手机充值第二步 - 平台选择
    public function phoneRechargeStep2()
    {
        $bizName = $this->input->get('bizName');
        $user = $this->session->userdata('user');

        // $this->phone = $this->input->get('phone');
        $this->phone = $this->input->post("phone");
        $this->per_value = $this->input->post("payPrice");
        echo ' phone = ' . $this->phone;
        //  $this->load->view('chongzhi.html');
        //  $this->load->view('bmfw_hfcz.html');
    }

    #手机充值第1步 - 支付1 - 检测当前手机号码及面额是否可充值，返回实际销售价格
    public function  getHuaFeiRealPayPrice() //http://localhost:81/web/index.php/allPay/getHuaFeiRealPayPrice?payPrice=100
    {
        if (!empty($_REQUEST['payPrice'])) {
            $this->per_value = $_REQUEST['payPrice'];
        }
        $options = 'key=' . $this->huafei_app_key . '&dtype=' . $this->type . '&phone=' . $this->phone . '&pervalue=' . $this->per_value;
        // echo '$options = ' . $options;
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'telcheck', $options, 0);
        echo $content; //{"reason":"验证通过,可以送单","result":{"price":"98.950","time":"2015-07-30 11:48:33"},"error_code":0}
    }

    #话费充值订单列表获取
    public function getHuaFeiOrderList() //http://localhost:81/web/index.php/allPay/getHuaFeiOrderList
    {
        $options = 'key=' . $this->huafei_app_key; //还可以传参数
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'orderlist', $options, 0);
        echo $content;
    }

    public function getHuaFeiOrderList2() //http://localhost:81/web/index.php/allPay/getHuaFeiOrderList2
    {   //key=36e685618ddd749f6603fe932bb779e5&dtype=json&page=&pagesize=
        $options = 'key=' . $this->huafei_app_key; //还可以传参数
        //echo '$options = ' . $options;
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'orderlist', $options, 0);
        echo $content;
        //{"reason":"成功返回!",
        //"result":{"list":[
        //{"sporder_id":"143821914887797241","orderid":"E730190715103050","addtime":"2015-07-30 09:18:42","cardname":"话费200元直冲","sta":"-1","mobilephone":"15056068286","ordercash":null},
        //{"sporder_id":"143816144051638117","orderid":"1102220330sd","addtime":"2015-07-29 17:16:55","cardname":"话费300元直冲","sta":"-1","mobilephone":"15056068286","ordercash":null}],
        //"page":1,"pagesize":50,"totalcount":2,"totalpage":1},"error_code":0}
    }

    #话费订单状态查询
    public function getHuaFeiOrderStatus() //http://localhost:81/web/index.php/allPay/getHuaFeiOrderStatus
    {
        $order_id = 'E730190715103050'; //$this->generateOrderId();
        //key=36e685618ddd749f6603fe932bb779e5&dtype=json&phone=15056068286&pervalue=50&orderid=1102220330sd&sign=c6d124392ab1ab05bb9ab7b8bdfdc691
        $options = 'key=' . $this->huafei_app_key . '&dtype=' . $this->type . '&sporder_id=' . $order_id;
        echo '$options = ' . $options;
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'status', $options, 0);
        echo $content; //{"reason":"错误的订单号","result":null,"error_code":214210} 错误的订单号，所有未充值成功的皆为错误
    }

    /*
    * phone	string	是	手机号码
    * pervalue	int	是	充值面额，可选:10,20,30,50,100,200,300,500
    * orderid	string	是	自定义订单号，8-32位字母数字组合
    * key	string	是	应用APPKEY(应用详细页查询)
    * sign	string	是	校验值，md5(OpenID+key+phone+pervalue+orderid)，
    * OpenID在个人中心查询，结果转为32小写
    */
    #手机充值第2步 - 提交话费充值 - 现直接入库
    public function getHuaFeiRecharge() //http://localhost:81/web/index.php/allPay/getHuaFeiRecharge
    {
        $order_id = $this->generateOrderId();
        $this->phone = $_REQUEST['phone'];
        $this->per_value = $_REQUEST["payPrice"];
        $sign = strtolower(md5($this->openID . $this->huafei_app_key . $this->phone . $this->per_value . $order_id));
        $options = 'key=' . $this->huafei_app_key . '&dtype=' . $this->type . '&phone=' . $this->phone . '&pervalue=' . $this->per_value . '&orderid=' . $order_id . '&sign=' . $sign;
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'recharge', $options, 0);
        echo $content;
    }

    public function getHuaFeiRecharge2() //http://localhost:81/web/index.php/allPay/getHuaFeiRecharge2
    {
        $order_id = 'E730190715103051';
        //话费一旦提交，就必须将订单信息写入库。
        $sign = strtolower(md5($this->openID . $this->huafei_app_key . $this->phone . $this->per_value . $order_id));
        // key=36e685618ddd749f6603fe932bb779e5 &dtype=json &phone=15056068286 &pervalue=50 &orderid=1102220330sd &sign=c6d124392ab1ab05bb9ab7b8bdfdc691
        $options = 'key=' . $this->huafei_app_key . '&dtype=' . $this->type . '&phone=' . $this->phone . '&pervalue=' . $this->per_value . '&orderid=' . $order_id . '&sign=' . $sign;
        $content = $this->juhecurl($this->juhe_huafei_base_url . 'recharge', $options, 0);
        echo $content; //{"reason":"当前账户可用余额不足￥197.900元","result":null,"error_code":214208}
    }

    #水电煤缴费
    #公共事业省份查询【province】 步骤1
    #查看支持水电煤缴费的省份列表
    public function getPublicProvinceInfo() //http://localhost:81/web/index.php/allPay/getPublicProvinceInfo
    {
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type;
        $content = $this->juhecurl($this->juhe_public_base_url . 'province', $options, 0);
        echo $content;
    }

    public function getPublicProvinceInfo2() //http://localhost:81/web/index.php/allPay/getPublicProvinceInfo2
    {
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type;
        //  $options = 'key=593784333a5cffe15273aaf9990dfad7&dtype=json';
        //   echo 'options=' . $options; //$this->juhe_base_url . 'ofpay/public/province'
        $content = $this->juhecurl($this->juhe_public_base_url . 'province', $options, 0);
        echo $content;
        //{"reason":"查询成功","result":
        //[{"provinceId":"v1904","provinceName":"黑龙江"},{"provinceId":"v1918","provinceName":"湖南"},{"provinceId":"v1955","provinceName":"浙江"},
        //{"provinceId":"v2091","provinceName":"贵州"},{"provinceId":"v2159","provinceName":"山西"},{"provinceId":"v1967","provinceName":"河南"},
        //{"provinceId":"v2043","provinceName":"内蒙古"},{"provinceId":"v2056","provinceName":"江苏"},{"provinceId":"v2101","provinceName":"安徽"},
        //{"provinceId":"v2070","provinceName":"重庆"},{"provinceId":"v2210","provinceName":"山东"},{"provinceId":"v1953","provinceName":"北京"},
        //{"provinceId":"v2129","provinceName":"甘肃"},{"provinceId":"v2307","provinceName":"全国"},{"provinceId":"v2269","provinceName":"陕西"},
        //{"provinceId":"v2228","provinceName":"广东"},{"provinceId":"v2186","provinceName":"江西"},{"provinceId":"v2297","provinceName":"福建"},
        //{"provinceId":"v2118","provinceName":"广西"},{"provinceId":"v2280","provinceName":"上海"},{"provinceId":"v2198","provinceName":"河北"},
        //{"provinceId":"v2171","provinceName":"辽宁"},{"provinceId":"v1933","provinceName":"四川"},{"provinceId":"v2250","provinceName":"青海"}],"error_code":0}
    }

    #水电煤缴费
    #公共事业城市查询【city】 步骤2
    #根据省份ID查询支持缴费的城市列表
    public function getPublicCityInfo() //http://localhost:81/web/index.php/allPay/getPublicCityInfo
    {
        $provinceId = '';
        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'city', $options, 0);
        echo $content;
    }

    public function getPublicCityInfo2() //http://localhost:81/web/index.php/allPay/getPublicCityInfo
    {
        $provinceId = 'v2101';
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId;
        //  $options = 'key=593784333a5cffe15273aaf9990dfad7&dtype=json';
        //   echo 'options=' . $options; //$this->juhe_base_url . 'ofpay/public/province'
        $content = $this->juhecurl($this->juhe_public_base_url . 'city', $options, 0);
        echo $content; //{"reason":"查询成功","result":
        //[{"provinceId":"v2101","cityId":"v2104","cityName":"池州"},{"provinceId":"v2101","cityId":"v2108","cityName":"蚌埠"},
        //{"provinceId":"v2101","cityId":"v2107","cityName":"合肥"},{"provinceId":"v2101","cityId":"v2114","cityName":"马鞍山"}],"error_code":0}
    }

    #水电煤缴费
    #公共事业充值类型查询【project】 步骤3
    #根据省份id和城市id查询支持的缴费类型
    public function getPublicRechargeType() //http://localhost:81/web/index.php/allPay/getPublicRechargeType
    {
        $cityId = '';
        $provinceId = '';
        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        if (!empty($_REQUEST['cityId'])) {
            $cityId = $_REQUEST['cityId'];
        }
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'project', $options, 0);
        echo $content;
    }

    public function getPublicRechargeType2() //http://localhost:81/web/index.php/allPay/getPublicRechargeType
    {
        $provinceId = 'v2101';
        $cityId = 'v2107';
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId;
        //  $options = 'key=593784333a5cffe15273aaf9990dfad7&dtype=json';
        //  echo 'options=' . $options; //$this->juhe_base_url . 'ofpay/public/province'
        $content = $this->juhecurl($this->juhe_public_base_url . 'project', $options, 0);
        echo $content; //{"reason":"查询成功","result":
        //[{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2680","payProjectName":"电费"},
        //{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payProjectName":"水费"}],"error_code":0}
    }

    #水电煤缴费
    #公共事业缴费单位查询【unit】 步骤4
    #根据省份id和城市id、类型ID查询支持的缴费单位
    public function getPublicUnit() //http://localhost:81/web/index.php/allPay/getPublicUnit
    {
        $cityId = '';
        $provinceId = '';
        $payProjectId = '';
        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        if (!empty($_REQUEST['cityId'])) {
            $cityId = $_REQUEST['cityId'];
        }
        if (!empty($_REQUEST['payProjectId'])) {
            $payProjectId = $_REQUEST['payProjectId'];
        }
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'unit', $options, 0);
        echo $content;
    }

    public function getPublicUnit2() //http://localhost:81/web/index.php/allPay/getPublicUnit
    {
        $provinceId = 'v2101';
        $cityId = 'v2107';
        $payProjectId = 'c2670';
        //key=593784333a5cffe15273aaf9990dfad7&dtype=json&provid=v2101&cityid=v2107&type=c2670
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId;
        //  echo 'options=' . $options; //$this->juhe_base_url . 'ofpay/public/province'
        $content = $this->juhecurl($this->juhe_public_base_url . 'unit', $options, 0);
        echo $content; //{"reason":"查询成功","result":
        //[{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payUnitId":"v83497","payUnitName":"合肥市水费"}],"error_code":0}
    }

    #水电煤缴费
    #水电煤商品信息查询【query】  步骤7
    #此接口用户公共事业充值，根据省份ID、城市ID、充值类型、缴费单位 获取水电煤商品信息
    public function  getPublicProductInfo() //http://localhost:81/web/index.php/allPay/getPublicProductInfo
    {
        $cityId = '';
        $provinceId = '';
        $payProjectId = ''; //缴费类型编码,对应接口4返回的:payProjectId
        $payUnitId = ''; //缴费单位编码,对应接口4返回的:payUnitId
        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        if (!empty($_REQUEST['cityId'])) {
            $cityId = $_REQUEST['cityId'];
        }
        if (!empty($_REQUEST['payProjectId'])) {
            $payProjectId = $_REQUEST['payProjectId'];
        }
        if (!empty($_REQUEST['payUnitId'])) {
            $payUnitId = $_REQUEST['payUnitId'];
        }
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'query', $options, 0);
        echo $content;
    }

    public function  getPublicProductInfo2() //http://localhost:81/web/index.php/allPay/getPublicProductInfo2
    {
        $provinceId = 'v2101';
        $cityId = 'v2107';
        $payProjectId = 'c2670'; //缴费类型编码,对应接口4返回的:payProjectId
        $payUnitId = 'v83497'; //缴费单位编码,对应接口4返回的:payUnitId
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'query', $options, 0);
        echo $content; //{"reason":"查询成功","result":
        //{"productId":"64318001","productName":"安徽 合肥市水费 水费 户号 直充任意充","inprice":[]},"error_code":0}
    }

    #水电煤缴费
    #水电煤公共事业充值【order】  步骤8
    #此接口用于根据用户的请求生成水电煤的订单, 订单入库，以方便用户查询，操作步骤9。
    public function  getPublicOrder() //http://localhost:81/web/index.php/allPay/getPublicOrder
    {
        $provinceId = ''; //'v2101';
        $cityId = ''; //'v2107';
        $payProjectId = ''; // 'c2670'; //缴费类型编码,对应接口4返回的:payProjectId
        $payUnitId = ''; //'v83497'; //缴费单位编码,对应接口4返回的:payUnitId

        $cardId = ''; //水电煤的商品编号
        $account = ''; //水电煤户号／条形码号
        $contract = ''; //合同号 （通过欠费接口查询，查到必传，查不到就不传） 非必填
        $payMentDay = ''; //账期，（通过欠费接口查询，查到必传，查不到就不传） 非必填
        $orderId = $this->generateOrderId(); //自定义订单号（8-32位数字字母）
        $cardNum = ''; //充值金额（单位：元，保留小数点后2位），例如198.35元

        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        if (!empty($_REQUEST['cityId'])) {
            $cityId = $_REQUEST['cityId'];
        }
        if (!empty($_REQUEST['payProjectId'])) {
            $payProjectId = $_REQUEST['payProjectId'];
        }
        if (!empty($_REQUEST['payUnitId'])) {
            $payUnitId = $_REQUEST['payUnitId'];
        }
        if (!empty($_REQUEST['cardId'])) {
            $cardId = $_REQUEST['cardId'];
        }
        if (!empty($_REQUEST['account'])) {
            $account = $_REQUEST['account'];
        }
        if (!empty($_REQUEST['contract'])) {
            $contract = $_REQUEST['contract'];
        }
        if (!empty($_REQUEST['payMentDay'])) {
            $payMentDay = $_REQUEST['payMentDay'];
        }
        if (!empty($_REQUEST['cardNum'])) {
            $cardNum = $_REQUEST['cardNum'];
        }

        $sign = strtolower(md5($this->openID . $this->public_app_key . $cardId . $cardNum . $orderId . $provinceId . $cityId . $payProjectId . $payUnitId . $account));
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId
            . '&cardid=' . $cardId . '&cardnum=' . $cardNum . '&account=' . $account . '&orderid=' . $orderId . '&sign=' . $sign . '&contract=' . $contract . '&payMentDay=' . $payMentDay;
        $content = $this->juhecurl($this->juhe_public_base_url . 'order', $options, 0);
        echo $content;
    }

    public function  getPublicOrder2() //http://localhost:81/web/index.php/allPay/getPublicOrder2
    {
        $provinceId = 'v2056'; //'v2101';
        $cityId = 'v2067'; //'v2107';
        $payProjectId = 'c2680'; // 'c2670'; //缴费类型编码,对应接口4返回的:payProjectId
        $payUnitId = 'v84089'; //'v83497'; //缴费单位编码,对应接口4返回的:payUnitId

        $cardId = '64323301'; //水电煤的商品编号
        $account = '888888888'; //水电煤户号／条形码号
        $contract = ''; //合同号 （通过欠费接口查询，查到必传，查不到就不传） 非必填
        $payMentDay = ''; //账期，（通过欠费接口查询，查到必传，查不到就不传） 非必填
        $orderId = '13345689'; //自定义订单号（8-32位数字字母）
        $cardNum = '1.25'; //充值金额（单位：元，保留小数点后2位），例如198.35元
        //校验值，md5(OpenID+key+cardid+cardnum+orderid+provid+cityid+type+code+account)
        $sign = strtolower(md5($this->openID . $this->public_app_key . $cardId . $cardNum . $orderId . $provinceId . $cityId . $payProjectId . $payUnitId . $account));
        //key=key&provid=v2056&cityid=v2067&type=c2680&code=v84089&cardid=64323301&cardnum=1&account=888888888&orderid=13345689&sign=71e9ba44f756d4251d205726487d60f4
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId
            . '&cardid=' . $cardId . '&cardnum=' . $cardNum . '&account=' . $account . '&orderid=' . $orderId . '&sign=' . $sign . '&contract=' . $contract . '&payMentDay=' . $payMentDay;
        $content = $this->juhecurl($this->juhe_public_base_url . 'order', $options, 0);
        echo $content; //{"reason":"当前账户可用余额不足￥1元","result":null,"error_code":209318}
        // {"reason": "提交充值成功","result":
        // {"cardid": "64323301", "cardnum": "1",/*充值金额*/ "ordercash": "1",/*扣费金额*/
        //  "cardname": "江苏 苏州市电费  户号 直充任意充", "sporderId": "P20141211203444313",
        //  "account": "6200185388","uorderid":"13345680",/*商户自定的订单号*/ "status": "0" /*充值状态:0充值中 1成功 9撤销*/
        // },  "error_code": 0 }
    }

    #水电煤缴费
    #水电煤订单状态查询【ordersta】  步骤9
    #此接口用于根据订单号查询公共事业单位缴费订单状态
    public function  getPublicOrderStatus() //http://localhost:81/web/index.php/allPay/getPublicOrderStatus
    {
        $orderId = ''; //商家订单号
        if (!empty($_REQUEST['orderId'])) {
            $orderId = $_REQUEST['orderId'];
        }
        $options = 'key=' . $this->public_app_key . '&orderid=' . $orderId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'ordersta', $options, 0);
        echo $content;
    }

    public function  getPublicOrderStatus2() //http://localhost:81/web/index.php/allPay/getPublicOrderStatus2
    {
        $orderId = '110dfd'; //商家订单号
        $options = 'key=' . $this->public_app_key . '&orderid=' . $orderId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'ordersta', $options, 0);
        echo $content; //{"reason":"错误的订单号","result":null,"error_code":209319}
    }

    #水电煤缴费
    #水电煤历史订单列表【orderlist】  步骤10
    #此接口用于根据订单号查询公共事业单位缴费订单状态
    public function  getPublicOrderList() //http://localhost:81/web/index.php/allPay/getPublicOrderList
    {
        $page = 1; //当前页数，默认1
        $pageSize = 20; //每页显示条数，默认50，最大100
        $options = 'key=' . $this->public_app_key . '&page=' . $page . '&pagesize=' . $pageSize;
        $content = $this->juhecurl($this->juhe_public_base_url . 'orderlist', $options, 0);
        echo $content;
    }

    public function  getPublicOrderList2() //http://localhost:81/web/index.php/allPay/getPublicOrderList2
    {
        $page = 1; //当前页数，默认1
        $pageSize = 20; //每页显示条数，默认50，最大100
        $options = 'key=' . $this->public_app_key . '&page=' . $page . '&pagesize=' . $pageSize;
        $content = $this->juhecurl($this->juhe_public_base_url . 'orderlist', $options, 0);
        echo $content; //{"reason": "success","result": {"data": [{"sporder_id": "P201506111*****","uorderid":"123123123123123","cardid": "641401","cardnum": "1","uordercash": "4.000","cardname": "江苏南京 南京市电力公司 电费户号 任意充直充","game_state": "1","addtime": "2015-06-11 16:58:39"},{"sporder_id": "P201506111*****","uorderid": "1231231231","cardid": "6432800","cardnum": "1","uordercash": "41.000","cardname": "江苏南京 南京市自来水总公司 水费户号 任意充直充","game_state": "1","addtime": "2015-06-11 16:14:10"}]}}
    }

    #水电煤缴费
    #公共事业缴费方式查询【paymode】 步骤5 新增
    #此接口用于查询水电煤缴费方式
    public function getPublicPayMode() //http://localhost:81/web/index.php/allPay/getPublicPayMode
    {
        $provinceId = '';
        $cityId = '';
        $payProjectId = ''; //缴费类型编码
        $payUnitId = ''; //缴费单位编码

        if (!empty($_REQUEST['provinceId'])) {
            $provinceId = $_REQUEST['provinceId'];
        }
        if (!empty($_REQUEST['cityId'])) {
            $cityId = $_REQUEST['cityId'];
        }
        if (!empty($_REQUEST['payProjectId'])) {
            $payProjectId = $_REQUEST['payProjectId'];
        }
        if (!empty($_REQUEST['payUnitId'])) {
            $payUnitId = $_REQUEST['payUnitId'];
        }

        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId;
        $content = $this->juhecurl($this->juhe_public_base_url . 'paymode', $options, 0);
        echo $content;
    }
    public function getPublicPayMode2() //http://localhost:81/web/index.php/allPay/getPublicPayMode2
    {
        $provinceId = 'v2101';
        $cityId = 'v2107';
        $payProjectId = 'c2670'; //缴费类型编码
        $payUnitId = 'v83497'; //缴费单位编码
        //  key=593784333a5cffe15273aaf9990dfad7&dtype=json&provid=v2101&cityid=v2107&type=c2670
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provid=' . $provinceId . '&cityid=' . $cityId . '&type=' . $payProjectId . '&code=' . $payUnitId;
        //  $options = 'key=593784333a5cffe15273aaf9990dfad7&dtype=json';
        //  echo 'options=' . $options; //$this->juhe_base_url . 'ofpay/public/province'
        $content = $this->juhecurl($this->juhe_public_base_url . 'paymode', $options, 0);
        echo $content; //{"reason":"查询成功","result":
        //{"payMode":{"provinceId":"v2101","cityId":"v2107","payProjectId":"c2670","payUnitId":"v83497","payModeId":"v2620","payModeName":"户号"}},"error_code":0}
    }

    #水电煤缴费
    #公共事业账户欠费查询（支持多期）【mbalance】 步骤6 新增
    #此接口用公共事业充值，查询账户欠费金额,可以支持返回多期账单
    public function getPublicMbalance() //http://localhost:81/web/index.php/allPay/getPublicMbalance
    {
        $provinceName = '';
        $cityName = '';
        $type = ''; //类型（001：水费、002：电费、003：燃气费）
        $payUnitId = ''; //缴费单位编码
        $payUnitName = ''; //缴费单位名称, 对应步骤4
        $account = ''; //水电煤户号／条形码号
        $productId = ''; //水电煤的商品编号，对应接口7(水电煤商品信息查询)返回：productId
        $payModeId = ''; //缴费方式编号(条形码:1,户号:2 不填默认为户号) 注：条形码一般是24位

        if (!empty($_REQUEST['provinceName'])) {
            $provinceName = $_REQUEST['provinceName'];
        }
        if (!empty($_REQUEST['cityName'])) {
            $cityName = $_REQUEST['cityName'];
        }
        if (!empty($_REQUEST['type'])) {
            $type = $_REQUEST['type'];
        }
        if (!empty($_REQUEST['payUnitId'])) {
            $payUnitId = $_REQUEST['payUnitId'];
        }
        if (!empty($_REQUEST['payUnitName'])) {
            $payUnitName = $_REQUEST['payUnitName'];
        }
        if (!empty($_REQUEST['account'])) {
            $account = $_REQUEST['account'];
        }
        if (!empty($_REQUEST['productId'])) {
            $productId = $_REQUEST['productId'];
        }
        if (!empty($_REQUEST['payModeId'])) {
            $payModeId = $_REQUEST['payModeId'];
        }

        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provname=' . $provinceName . '&cityname=' . $cityName . '&code=' . $payUnitId .
            '&name=' . $payUnitName . '&account=' . $account . '&cardid=' . $productId . '&paymodeid=' . $payModeId . '&type=' . $type;
        $content = $this->juhecurl($this->juhe_public_base_url . 'mbalance', $options, 0);
        echo $content;
    }

    public function getPublicMbalance2() //http://localhost:81/web/index.php/allPay/getPublicMbalance2
    {
        $provinceName = '安徽';
        $cityName = '合肥';
        $type = '001'; //类型（001：水费、002：电费、003：燃气费）
        $payUnitId = 'v83497'; //缴费单位编码
        $payUnitName = '合肥市水费'; //缴费单位名称, 对应步骤4
        $account = '123456789'; //水电煤户号／条形码号
        $productId = '64318001'; //水电煤的商品编号，对应接口7(水电煤商品信息查询)返回：productId
        $payModeId = 1; //缴费方式编号(条形码:1,户号:2 不填默认为户号) 注：条形码一般是24位
        $options = 'key=' . $this->public_app_key . '&dtype=' . $this->type . '&provname=' . $provinceName . '&cityname=' . $cityName . '&code=' . $payUnitId .
            '&name=' . $payUnitName . '&account=' . $account . '&cardid=' . $productId . '&paymodeid=' . $payModeId . '&type=' . $type;
        // echo $options;
        $content = $this->juhecurl($this->juhe_public_base_url . 'mbalance', $options, 0);
        echo $content; //{"reason":" 该户号不存在","result":null,"error_code":209318}
        //{"reason": "查询成功","result": {"account": "021068****","balances": {"balance": {"balance": "60.10","contractNo": "55507021068461700****","payMentDay": "201507"}}},"error_code": 0} 官方API文档
        //{"reason":"查询成功","result":{"account":"5132827118","balances":{"balance":{"accountName":"合*司","balance":"0.00","contractNo":"34401"}}},"error_code":0}
    }

    /*
    *请求接口，返回JSON数据
    *@url:接口地址
    **params:传递的参数
    *@ispost:是否以POST提交，默认GET
    */
    function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            #echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    //请求方式：get
    //请求URL：http://v.juhe.cn/huafei/telcheck
    //请求参数：key=36e685618ddd749f6603fe932bb779e5&dtype=json&phone=15056068286&pervalue=100
    #手机充值第三步 -  手机充值 - 聚合云接口
    public function phoneRechargeStep3()
    {
        //echo ' go to pay for it ';
        $phone = 15056068286;
        $pervalue = 100;
    }

    /* private function buildRequest($app_key, $type, $phone, $pervalue)
     {
         return "<body style='height:100%; text-align:center;'><div  style='margin:300px'><img src=" . base_url() . "themes/default/images/loading1.gif></div>
                 <form  style='display:none;' id='juhesubmit' name='umspaysubmit' method='post' action=" . $this->$this->juhe_base_url . ">
                 <input name='key' type='text' value='$app_key' />
                 <input name='dtype' type='text' value='$type'/>
                 <input name='phone' type='text' value='$phone'/>
                 <input name='pervalue' type='text' value='$pervalue'/>
                 </form>
                 <script>document.forms['juhesubmit'].submit();</script></body>";
     }*/

}