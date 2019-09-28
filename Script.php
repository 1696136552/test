<?php
session_start();

// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
$location = 'citycard.mysql.rds.aliyuncs.com';//服务器地址
$user_name = 'honghang';//数据库账户名
$password = 'Honghang2019';//账户密码
$database = 'honghang';//数据库名称

$conn = new mysqli($location, $user_name, $password, $database, 3399);
!mysqli_connect_error() or die("连接失败！");
$conn->query("SET NAMES utf8");
//$conn=@mysqli_connect($location,$user_name,$password,3399) or die('数据库连接失败！！！');//创建数据连接
//mysqli_select_db($conn,$database) or die('数据库不存在或没该数据库权限');//连接到数据库

//合并GET与POST
$Arg = array_merge($_GET, $_POST);

/**
 * 创建
 */
function create()
{
    $Data = array();
    if ($_POST['SN'] != null) {
        $Data['SN'] = $_POST['SN'];

        $Arg=$GLOBALS['Arg'];
        $Arg['SN']= $_POST['SN'];
        if(get()!=null){
            return 'error_repeat';//不允许重复添加，SN重复
        }
    } else {

        return 'error_arg';
    }
    $Data['deliverytime'] = $_POST['deliverytime'];

    $Data['checktime'] = $_POST['checktime'];
    $Data['spec'] = $_POST['spec'];
    $Data['childbirth'] = $_POST['childbirth'];
    $Data['next'] = $_POST['next'];
    $Data['rejecttime'] = $_POST['rejecttime'];
    $Data['filling_unit'] = $_POST['filling_unit'];
    $Data['station'] = $_POST['station'];
    $Data['medium'] = $_POST['medium'];
    $Data['lasttime'] = $_POST['lasttime'];
    $Data['name'] = $_POST['name'];
    $Data['status_num'] = $_POST['status_num'];
    $Data['address'] = $_POST['address'];
    $Data['send_time'] = $_POST['send_time'];
    $Data['deliver'] = $_POST['deliver'];
    $Data['send_unit'] = $_POST['send_unit'];

    //生成二维码
    $Data['code']=http_request_json($Data['SN']);

    $conn = $GLOBALS['conn'];
//        $sql="insert into cylinder values($SN,$deliverytime,$checktime,$spec,$childbirth,$next,$rejecttime,$station,$medium,$lasttime,$name,$status_num,$address,$deliver)";
//        var_dump($sql);
//        $result = mysqli_query($conn,$sql);
    $result = insert($Data);
    return $result;
}

/**
 * 获取表字段
 * @param $Table
 * @return array|bool
 */
function Get_TableColumn($Table)
{
    $conn = $GLOBALS['conn'];

    //数据库读取
    $sql = 'SELECT * FROM `information_schema`.`COLUMNS` 
			WHERE `TABLE_SCHEMA`=\'' . honghang . '\'
			AND `TABLE_NAME` = \'' . cylinder . '\'';
    $result = mysqli_query($conn, $sql);

    //若执行失败那么回滚
    if ($result == false) {
        return false;
    }

    //循环读取数据
    $List = array();
    while (true) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if (!$row) {
            break;
        }
        array_push($List, $row);
    }

    return $List;
}

/**
 * 插入
 * @param $Data
 * @return bool|mixed
 */
function insert($Data)
{
    $conn = $GLOBALS['conn'];

    //获取表结构
    $FieldList = Get_TableColumn('cylinder');

    //生成字段
    $SQL_Field = '';
    $SQL_Value = '';
    foreach ($FieldList as $Field) {
        if ($Field['COLUMN_NAME'] != 'ctime') {
            if ($Data[$Field['COLUMN_NAME']] != '') {
                $SQL_Field .= ',`' . $Field['COLUMN_NAME'] . '`';
                $SQL_Value .= ',\'' . $Data[$Field['COLUMN_NAME']] . '\'';
            }
        } else {
            $SQL_Field .= ',`' . $Field['COLUMN_NAME'] . '`';
            $SQL_Value .= ',\'' . time() . '\'';
        }
    }

    $SQL_Field = substr($SQL_Field, 1);
    $SQL_Value = substr($SQL_Value, 1);

    //组合语句
    $sql = "INSERT INTO `cylinder` ($SQL_Field) VALUES ($SQL_Value)";

    //执行SQL
//    $result = $this->DBLink->query($sql);
    $result = mysqli_query($conn, $sql);

    return $result;
}

/**
 * 查询
 * @return array|null
 */
function search()
{
    $conn = $GLOBALS['conn'];

    $page = $_POST['page']*15;

    $sql = null;
    if (isset($_POST['page'])){
        $sql = "select * from cylinder LIMIT "."$page".",15";
        $count_sql = "select count(*) from cylinder";
    }elseif (!isset($_POST['SN'])) {
        $sql = "select * from cylinder";
        $count_sql = "select count(*) from cylinder";
    } else {
        $SN = $_POST['SN'];
        $sql = "select * from cylinder where SN='$SN'";
        $count_sql="select count(*) from cylinder where SN='$SN'";
    }
    $result = mysqli_query($conn, $sql);
    $count = mysqli_query($conn, $count_sql)->fetch_assoc();

    //判断是否读取到数据
    $list = array();
    // 定义数组下标
    $i = 0;
    // 遍历结果集
    while ($row = $result->fetch_assoc()) {
        $list[$i] = $row;
        $i++;
    }

//    var_dump($count);

    $ResultArray=array();
    $ResultArray['List']=$list;
    $ResultArray['page']=ceil($count["count(*)"]/15);
    return $ResultArray;
}

/**
 * 查询
 * @return array|null
 */
function get()
{
    $Arg=$GLOBALS['Arg'];
    $SN = $Arg['SN'];

    $conn = $GLOBALS['conn'];

    $sql = "select * from cylinder where SN='$SN'";
    $result = mysqli_query($conn, $sql);
    //判断是否读取到数据
    $list = array();
    // 定义数组下标
    $i = 0;
    // 遍历结果集
    while ($row = $result->fetch_assoc()) {
        $list[$i] = $row;
        $i++;
    }
    return $list[0];
}

/**
 * 查询
 * @return array|null
 */
function update()
{
    $SN = $_POST['SN'];
    $deliverytime = $_POST['deliverytime'];
    $checktime = $_POST['checktime'];
    $spec = $_POST['spec'];
    $childbirth = $_POST['childbirth'];
    $next = $_POST['next'];
    $rejecttime = $_POST['rejecttime'];
    $filling_unit = $_POST['filling_unit'];
    $station = $_POST['station'];
    $medium = $_POST['medium'];
    $lasttime = $_POST['lasttime'];
    $name = $_POST['name'];
    $status_num = $_POST['status_num'];
    $address = $_POST['address'];
    $send_time = $_POST['send_time'];
    $deliver = $_POST['deliver'];
    $send_unit = $_POST['send_unit'];

    $conn = $GLOBALS['conn'];

    $sql = "update cylinder set deliverytime='{$deliverytime}',checktime='{$checktime}',spec='{$spec}',childbirth='{$childbirth}',next='{$next}',rejecttime='{$rejecttime}',filling_unit='{$filling_unit}',station='{$station}',medium='{$medium}',lasttime='{$lasttime}',name='{$name}',status_num='{$status_num}',address='{$address}',send_time='{$send_time}',deliver='{$deliver}',send_unit='{$send_unit}' where SN='{$SN}'";
    $result = $conn->query($sql);
    return $result;
}

/**
 * 查询
 * @return array|null
 */
function delete()
{
    $SN = $_POST['SN'];

    $conn = $GLOBALS['conn'];

    $sql = "delete from cylinder where SN ='{$SN}'";
    $result = $conn->query($sql);

    $count_sql = "select count(*) from cylinder";
    $count = mysqli_query($conn, $count_sql)->fetch_assoc();

    $ResultArray=array();
    $ResultArray['delete']=$result;
    $ResultArray['page']=ceil($count["count(*)"]/15);
    return $ResultArray;
}


/**
 * 登录
 * @return bool
 */
function login()
{
    $conn = $GLOBALS['conn'];
    $Arg = $GLOBALS['Arg'];

    if($Arg['mode']=='Mobile'){
        $_SESSION['login']=true;
        return true;
    }

    $UsersName = $Arg['UsersName'];
    $PassWord=$Arg['PassWord'];

    $sql = "select * from users where UsersName='$UsersName'And PassWord='$PassWord'";
    $result = mysqli_query($conn, $sql);
    //判断是否读取到数据
    $res=mysqli_fetch_assoc($result);
    if($res) {
        $_SESSION['login']=true;
        return true;
    }else{
        $_SESSION['login']=false;
        return false;
    }
}

/**
 * 登录登录状态
 * @return bool
 */
function update_pwd()
{
//  $UsersName = $_POST['UsersName'];
    $UsersName = 'admin';
    $PassWord = $_POST['PassWord'];
    $PassWord_New = $_POST['PassWord_New'];

    $conn = $GLOBALS['conn'];

//判断原密码是否正确
    $sql = "select * from users where UsersName='$UsersName'And PassWord='$PassWord'";
    $result = mysqli_query($conn, $sql);
    //判断是否读取到数据
    $res=mysqli_fetch_assoc($result);
    if($res) {
        $sql = "update users set PassWord='{$PassWord_New}' where UsersName='{$UsersName}'";
        $result = $conn->query($sql);
        return $result;
    }else{
        return 'error_pwd';
    }
}

function state(){
    return $_SESSION['login'];
}

function token(){
    $appId = 'wx9fd8f627a789bd8a';
    $appSecret = 'c227121c22de3c5c145b59e9a27f70a5';
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$url); //要访问的地址
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//跳过证书验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    $data = json_decode(curl_exec($ch));
    if(curl_errno($ch)){
        var_dump(curl_error($ch)); //若错误打印错误信息
    }
    curl_close($ch);//关闭curl

    return $data; //打印信息
}

/**
 * 获取小程序二维码 //C类接口
 * 因为url是https 所有请求不能用file_get_contents,用curl请求json 数据
 * @param null $sn
 * @return mixed
 */
 function http_request_json($sn){
     $data['path']="pages/index/index?sn=$sn";
     $data['width']='300';
     $data=json_encode($data);//数据格式必须为json
     $token=(array)token();//表示小程序的唯一码，有效时间7200秒
     $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$token['access_token'];//小程序请求二维码地址

     $filename = $sn . '.jpg';//要生成的图片名字
     if (file_exists("code/" . $filename)!==true) {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         if ($data != null) {
             curl_setopt($ch, CURLOPT_POST, 1);
             curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         }
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         $result = curl_exec($ch);
         curl_close($ch);

         $jpg = $result;//得到post过来的二进制原始数据
         $file = fopen("code/" . $filename, "w");//打开文件准备写入
         fwrite($file, $jpg);//写入
         fclose($file);//关闭
     }
     return "code/".$filename;
}

/**
 * 获取不同时间范围的时间戳
 * @param $Range
 * @param $start_time
 * @param $end_time
 */
function TimeRange($Range,&$start_time,&$end_time){

    $week=date('w');//返回现在是周几
    if($week==0){//如果是周日则改为7，否则是从周日开始为一周的第一天
        $week=7;
    }

    switch ($Range['time_range']) {
        case '0'://今天时间戳
            $start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end_time = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            break;
        case '1'://本周时间戳
            $start_time = mktime(0, 0, 0, date('m'), date('d') - $week + 1, date('Y'));
            $end_time = mktime(23, 59, 59, date('m'), date('d') - $week + 7, date('Y'));
            break;
        case '2'://上周时间戳
            $start_time = mktime(0, 0, 0, date('m'), date('d') - $week + 1 - 7, date('Y'));
            $end_time = mktime(23, 59, 59, date('m'), date('d') - $week + 7 - 7, date('Y'));
            break;
        case '3'://本月时间戳
            $start_time = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $end_time = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
            break;
        case '4'://上月时间戳
            $start_time = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $end_time = mktime(23, 59, 59, date('m') - 1, cal_days_in_month(CAL_GREGORIAN, date('m') - 1, date('Y')), date('Y'));
            break;
        case '5'://今年
            $start_time = mktime(0, 0, 0, 1, 1, date('Y'));
            $end_time = mktime(23, 59, 59, 12, 31, date('Y'));
            break;
    };
}

/**
 * 发送http请求
 * 因为url是https 所有请求不能用file_get_contents,用curl请求json 数据
 * @param $url
 * @param null $data
 * @return mixed
 */
 function send_http_request_json($url,$data=null){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if($data != null){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}



$Module = explode('/', $_SERVER["REQUEST_URI"]);

//var_dump($Module['2']);
//var_dump($_SESSION['login']);
if($_SESSION['login']){
    switch ($Module['2']) {
        case 'create':
            $result = create();
            break;
        case 'update':
            $result = update();
            break;
        case 'search':
            $result = search();
            break;
        case 'get':
            $result = (object)get();
            break;
        case 'delete':
            $result = delete();
            break;
        case 'login':
            $result = login();
            break;
        case 'update_pwd':
            $result = update_pwd();
            break;
        case 'state':
            $result = state();
            break;
        case 'img':
            $result = http_request_json();
            break;
    }
}elseif ($Module['2']=='login'){
    $result=login();
} elseif ($Module['2']=='get'){
    $result = (object)get();
} elseif ($Module['2']=='search'){
    $result = search();
} else{
    $result= 'error_login';
}
if ($result !== NULL) {
    echo json_encode($result);
} else {
    echo json_encode(array());
}

