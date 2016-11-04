<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        echo 'this is api page!';
    }
    public function createJson(){
    	//创建数组
    	$personArray = array(
    		'name' => 'tom',
    		'age' => 18,
    		'job' => 'php'
    		);
    	//将数组转化为json
    	$personJson = json_encode($personArray);
    	//输出查看json
    	var_dump($personJson);
    }
    public function readJson(){
    	//json格式
    	$personJson = '{"name":"tom","age":"18","job":"php"}';
    	$personObj = json_decode($personJson);
    	var_dump($personObj);
    	echo "<hr />";
    	$arr = json_decode($personJson,true);
    	var_dump($arr);
    }
    public function createXML(){
    	//xml字符串的拼接
    	$str = '<?xml version="1.0" encoding="utf-8"?>';
    	//根标签
    	$str .='<person>';
    	$str .='<name>tom</name>';
    	$str .='<age>18</age>';
    	$str .='<job>php</job>';
    	$str .='</person>';
    	//添加xml文档
    	$rs = file_put_contents('./data.xml',$str);
    	echo $rs;
    	//$rs返回值为数字
   }
   public function readXML(){
   	//获取xml数据，读取文件
   		$xmlData = file_get_contents('./data.xml');
   		//将xml数据转化为对象
   		$objData = simplexml_load_string($xmlData);
   		//使用对象调用属性
   		echo 'name:'.$objData->name.'<br />';
   		echo 'age:'.$objData->age.'<br />';
   		echo 'job:'.$objData->job;
   }
   //使用request方法发送请求
   public function testRequest(){
     	//$url = 'http://web.api.com/index.php/Api/Index/readXML';
     	$url = 'https://www.baidu.com';
     	//发送请求
     	$content = request($url);
     	echo 'this is testRequest'.'<br />';
     	//dump($content);
     	echo $content;
   }
   //查询天气接口
   public function weather(){
     	$city = I('get.city');
     	//确定接口url地址
     	$url = 'http://api.map.baidu.com/telematics/v2/weather?location='.$city.'&ak=B8aced94da0b345579f481a1294c9094';
     	//判断请求方式
     	//发送请求
     	$content = request($url,false);
     	//对返回值进行处理
     	//返回数据为xml格式
     	$xmlObj = simplexml_load_string($content);
     	//dump($xmlObj);
     	$todayInfo = $xmlObj->results->result[0];
     	echo '实时温度：'.$todayInfo->date.'<br />';
     	echo '天气情况：'.$todayInfo->weather.'<br />';
     	echo '风向风力：'.$todayInfo->wind.'<br />';
     	echo '温度区间：'.$todayInfo->temperature.'<br />';
   }
   public function getAreaByPhone(){
     	$phone = I('get.phone');
     	$url = 'http://cx.shouji.360.cn/phonearea.php?number='.$phone;
     	$content = request($url,false);
     	$content = json_decode($content);
     	echo '当前号码：'.$phone.'<br />';
     	echo '省份：'.$content->data->province.'<br />';
     	echo '城市：'.$content->data->city.'<br />';
     	echo '运营商：'.$content->data->sp.'<br />';
   }
   //快递查询测试接口
   public function express(){
      $type = 'yuantong';
      $postid = '883185003506903278';
      //url地址
      $url = 'https://www.kuaidi100.com/query?type='.$type.'&postid='.$postid;
      //判断请求
      //发送请求
      $content = request($url);
      //处理数据返回值
      $content = json_decode($content);
      //获取的物流信息数据
      $data = $content->data;
      foreach($data as $key => $value){
        echo $value->time.'#####'.$value->context.'<br />';
      }
   }
   public function sendTest(){
      $rs = sendMail('我是php发送的邮件','你好，我是php,你是谁？','phpyi1024@163.com');
      if($rs === true){
        echo '发送邮件成功';
      }else{
        echo '发送邮件失败';
      }
   }
   //手机号归属地查询
   public function getAreaByPhoneToApi(){
      $phone = I('get.phone');
      if(empty($phone)){
        $data = array(
          'errorcode' => 1,
          'time' => time(),
          );
      }else{
        $areaNum= substr($phone,0,7);
        $url = 'http://cx.shouji.360.cn/phonearea.php?number'.$areaNum;
        $content = request($url,false);
        $content = json_decode($content);
        $data = array(
          'errorcode' => 0,
          'time' => time(),
          'province' => $content->data->province,
          'city' => $content->data->city,
          'sp' => $content->data->sp,
          );
      }
      //输出json格式
      echo json_encode($data);
      //序列化输出
      //echo serialize($data);
   }
   public function getAreaByPhoneToMysql(){
      //接收参数
      $phone = I('get.phone');
      //参数校验
      if(empty($phone)){
        $data = array(
          'errorcode' => 1,
          'time' => time(),
          );
      }else{
        //通过参数进行查询
        $areaNum = substr($phone,0,7);
        $content = D('mobile')->where("mobile = $areaNum")->find();
        var_dump($content);
        //根据约定格式进行返回
        $data = array(
          'errorcode' => 0,
          'time' => time(),
          'province' => $content->data->province,
          'city' => $content->data->city,
          'sp' => $content->data->sp,
        );
        echo json_encode($data);
      }
   }
   public function doMysqlToRedis(){
      ini_set('memory_limit','500M');
      $data = M('mobile')->select();
      $redis = new \Redis();
      $redis->connect('127.0.0.1',6379);
      foreach($data as $key=>$value){
        $redis->hMSet($value['mobile'],array('id'=>$value['id'],'mobile'=>$value['mobile'],'city'=>$value['city'],'sp'=>$value['sp']));
      }      
   }
   public function getAreaByIp(){
      $ip = I('get.ip');
      if(empty($ip)){
        $data = array(
          'errorcode' => 1,
          'time' => time(),
          );
      }else{
        $Ip = new \Org\Net\IpLocation('qqwry.dat');
        $area = $Ip->getlocation($ip);
        dump($area);
        /*foreach($area as $key=>$value){
          $value = iconv('gbk','utf-8',$value);
        }
        $data = array(
          'errorcode' => 0,
          'time' => time(),
          );*/
      }
    
   }
   
}