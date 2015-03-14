  
<?php
date_default_timezone_set('Asia/Hong_Kong');/*include 是用来加载其他PHP的文件函数*/
define("TOKEN", "dutbs");
include("codes/entrance.php");            //入口。包含了一级菜单和二级菜单的case 
include("codes/event.php");            	  //自定义菜单及订阅时的消息 
include("codes/writechoice.php");         //写lastchoice进数据库这个函数
include("codes/sleep.php");               //早安晚安的代码          此处可供借鉴数据库操作、算法
include("codes/class.php");               //课程查询的代码          此处可供借鉴数据库操作、图文编辑
include("codes/kuaidi.php");              //快递查询的代码          此处可供借鉴API使用
include("codes/food.php");                //外卖查询的代码          此处可供借鉴多级菜单、数据库操作
include("codes/weather.php");             //天气查询的代码          此处可供借鉴json
include("codes/suggest.php");             //反馈的代码              
include("codes/question.php");            //大工猜猜猜的代码        此处可供借鉴算法
include("codes/map.php");                 //寻车问路的代码          此处可供借鉴API、XML读取
//include("codes/march.php");               //校歌的代码 
include("codes/tel.php");                 //常用电话的代码
include("codes/fanyi.php");               //翻译的代码
include("codes/jiaowu.php");             //查询成绩的代码          此处可供借鉴模拟登陆
include("codes/qita.php");                //一些已经不用了的代码     可供借鉴、学习到许多东西，并不是没有用
include("codes/newsget.php");             //新闻查询的代码          正则
include("codes/carrget.php");             //就业信息的代码          正则
include("codes/service.php");             //澡堂，假期查询，各种服务
include("codes/lzshm.php");               //刘泽世的。
include("codes/lilith.php");			  //李英东
include("codes/partyconference.php");	
include("caidanrewrite.php");
include("codes/cet.php");
include("codes/wall.php");

//如果没有get到echostr这个变量，那就是普通的查询；如果有，就是在验证token
if(!isset($_GET['echostr']))
     responseMsg();
else
     valid();


$_c= new SaeCounter();
$_c->incr('页面访问');
/*注：这是一个统计功能已散播到个人认为需要统计的功能中，
具体用法嘛，自己探究，线索http://sae.sina.com.cn/?m=counter&app_id=master436&ver=3
还有好多功能要记得探索，不要我走了只是加一些代码，要学会利用Sae的便利哦-----Pica！
*/

function  valid()
    {
            $echoStr = $_GET["echostr"];        //随机字符串
            if(checkSignature()){
            echo $echoStr;
            exit;}
    }

function  checkSignature()
    {
        $signature = $_GET["signature"];    //微信加密签名
        $timestamp = $_GET["timestamp"];    //时间戳
        $nonce = $_GET["nonce"];            //随机数
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr,SORT_STRING);      //进行字典序排序
        //sha1加密后与签名对比
        if( sha1(implode($tmpArr)) == $signature )    {return true;}
		else      {return false;}
    }
	
function responseMsg()
    {
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            if (!empty($postStr)){
			logger("R ".$postStr);//将get的变量记入日志文件，方便检测
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
			switch ($RX_TYPE)
            {
                case "text":
				    $resultStr =receiveText($postObj);
                	break;
				case "image":
				    $resultStr =receiveImage($postObj);
                	break;
                case "event":   
                    $resultStr =recieveEvent($postObj);
                    break;
				case "location":
				    $resultStr =receiveLocation($postObj);
					break;
				case "voice":
				    $resultStr =receiceVoice($postObj);
					break; 
									
            }
		logger("T ".$resultStr);  //记入日志文件
		echo $resultStr;  
         }
		else
		{
			echo "";
            exit;
        }
    }
	
function transmitText($object, $content)
    {
		if (!isset($content) || empty($content)){
            return "";         }//返回为空值会造成“该公众号暂时无法提供服务”
			
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $resultStr;
    }

function transmitMusic($object, $title, $description, $musicurl, $hqmusicurl)
    {
		$textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<MusicUrl><![CDATA[%s]]></MusicUrl>
<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $title, $description, $musicurl, $hqmusicurl);
        return $resultStr;
    }
	
function transmitNews($object, $msgType, $title, $Discription, $PicUrl, $Url)
    {
		$textTpl ="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[%s]]></Title> 
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>
</Articles>
</xml> ";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $title, $Discription, $PicUrl, $Url);
        return $resultStr;
    }
	
function transmitNewstest($object, $contentArray)
    {		
		$CreateTime = time();
 
        $newTplHeader = "<xml>
            <ToUserName><![CDATA[{$object->FromUserName}]]></ToUserName>
            <FromUserName><![CDATA[{$object->ToUserName}]]></FromUserName>
            <CreateTime>{$CreateTime}</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <ArticleCount>%s</ArticleCount><Articles>";
        $newTplItem = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $newTplFoot = "</Articles>
             </xml>";
        $Content = '';
		
		$articleCount = count($contentArray);
		
		$i = 0;
		while($i < $articleCount){
		
			$Content.= sprintf($newTplItem,$contentArray[$i]['title'],$contentArray[$i]['description'],$contentArray[$i]['picurl'],$contentArray[$i]['url']);
			$i++;
		}

        $header = sprintf($newTplHeader, "hello", $articleCount);
        $footer = sprintf($newTplFoot);
        return $header . $Content . $footer;
	}


function logger($log_content)
{      
       if(isset($_SERVER['HTTP_APPNAME']))            //SAE
	   {
		   sae_set_display_errors(false);
		   sae_debug($log_content);
		   sae_set_display_errors(true);
		   }
		else if($_SERVER['REMOTE_ADDR']!="127.0.0.1")   //local
		{
			$max_size = 100000;    
 		    $log_filename = "log.xml";
    	    if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size))
			     {unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n");
			}
        
}

function connect()  //数据库的连接设置，引用数据库时只需输入 connect()
{
	$con = mysql_connect(set::host,set::id,set::pw);
	if(!$con){die('Could not connect: ' . mysql_error());}
	mysql_select_db(set::db, $con);
	mysql_query("SET NAMES utf-8");
	return $con;
}


class notices   
{
const notice="\n---\n遇到问题请输入“！”或使用菜单清除缓存";//尝试用一下吧
const subscribe="欢迎使用大连理工大学官方微信平台!\n\t本平台正处于开发期，各项功能持续更新中..\n\t大家的宣传和支持是我们前进的动力。";
const tongzhi="\n---\n\t提示:进入功能后输入“帮助”或者“？”才能退出回到主菜单哦~/::D";//上次选择通常一个小时内有效
const info=
'		大连理工大学是教育部直属全国重点大学之一.也是教育部“援疆学科建设计划”40所重点高校之一。
		国家"211工程"、"985工程"和"111计划"首批重点建设高校和自主招生"卓越联盟"成员。
		点击查看详细介绍';

		}
class set //数据库的链接设置
{

const host="w.rdc.sae.sina.com.cn:3307";
const id="wj1wkml0ow";
const pw="4524l40ymiyj315zi04235l3xjjw5h13yk2lj4h2";
const db="app_master436";

}
function checkIllegalWord ($str)
{
    // 定义不允许提交的SQL命令及关键字，防止sql注入
    $words = array();
    $words[] = " add ";
    $words[] = " count ";
    $words[] = " create ";
    $words[] = " delete ";
    $words[] = " drop ";
    $words[] = " from ";
    $words[] = " grant ";
    $words[] = " insert ";
    $words[] = " select ";
    $words[] = " truncate ";
    $words[] = " update ";
    $words[] = " use ";
    $words[] = "-- ";
   
    // 判断提交的数据中是否存在以上关键字, $_REQUEST中含有所有提交数据
    foreach($str as $strGot) 
	{
        $strGot = strtolower($strGot); // 转为小写
        foreach($words as $word)
		 {
            if (strstr($strGot, $word)) 
			{
                return false; // 退出运行
            }
         }
    }
	return true;
}




function get_accesstoken()
{
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxd239aec2877ee63f&secret=07faaac27d6e09d44e172a88d794aaa8";
	$TokenJson=file_get_contents($url);
    $Token = json_decode($TokenJson,true);
    $token=$Token["access_token"];
	return $token;
	}






function feedback($fakeid,$content)   //给指定fakeid的用户利用模拟登陆发送内容为content的文本消息
	{
$cookie_file = tempnam('./temp','cookie');
$url="https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
$ch=curl_init($url);
$post['username']='iduter';
$post['pwd']=md5('dutxcb123456');
$post['f']='json';
$post['imgcode']='';
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    //验证证书
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);        //验证HOST
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);      //对推送来的消息进行设置   1不自动输出任何内容 0输出返回的内容
curl_setopt($ch,CURLOPT_HEADER,1);      //是否返回头文件
curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/loginpage?t=wxm2-login&lang=zh_CN');
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);  //数据传输最大允许时间
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
curl_setopt($ch,CURLOPT_COOKIEJAR, $cookie_file);
$html=curl_exec($ch);
preg_match('/[\?\&]token=(\d+)"/',$html,$t);        //匹配到token
$token=$t[1];
curl_close($ch);

$cookie_wenjain = file_get_contents($cookie_file);  
$cookie_wenjain = str_replace("\n","",$cookie_wenjain);  
$cookie_wenjain = str_replace("\t","",$cookie_wenjain);  
$cookie_wenjain = str_replace("\r","",$cookie_wenjain);  
preg_match_all('/data_bizuin(.*)mp/isU',$cookie_wenjain,$slave_user_lists);  
$data_bizuin = $slave_user_lists[1][0];  
preg_match_all('/data_ticket(.*)mp/isU',$cookie_wenjain,$slave_user_lists);  
$data_ticket = $slave_user_lists[1][0];  
preg_match_all('/slave_user(.*)mp/isU',$cookie_wenjain,$slave_user_lists);  
$slave_user = $slave_user_lists[1][0];  
preg_match_all('/slave_sid(.*)=/isU',$cookie_wenjain,$slave_sid_lists);  
$slave_sid = $slave_sid_lists[1][0];  
$cookie = "data_ticket=".$data_ticket.";data_bizuin=".$data_bizuin.";slave_user=".$slave_user.";slave_sid=".$slave_sid."=";  
unlink($cookie_file);  
$cookie = str_replace("#HttpOnly_","",$cookie);//将信息保存在$cookie里  


 $url="https://mp.weixin.qq.com/cgi-bin/singlesend";  
 $ch=curl_init($url);  
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  
 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);  
 curl_setopt($ch, CURLOPT_COOKIE, $cookie);  
 curl_setopt($ch,CURLOPT_REFERER,'https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=20&day=7&token='.$token.'&lang=zh_CN');  
 curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');  
 curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);  
 $post['random']=rand(1,999999999999999)/10000000000000000;  
 $post['lang']='zh_CN';  
 $post['type'] =1;  
 $post['content']=$content;  
 $post['tofakeid']=$fakeid;  
 $post['imgcode']='';  
 $post['token']=$token;  
 $post['f'] =json;  
 $post['ajax'] =1;  
 $post['t']="ajax-response";  
 curl_setopt($ch,CURLOPT_POST,1);  
 curl_setopt($ch,CURLOPT_POSTFIELDS,$post);  
 $html=curl_exec($ch);  
 curl_close($ch);  
 	}


	?>