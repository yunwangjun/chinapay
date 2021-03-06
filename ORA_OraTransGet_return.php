<?php
/**
 * @author huang.xuting
 *
 */
	header('Content-type: text/html; charset=GBK');
	include_once("netpayclient_config.php");

	//加载 netpayclient 组件
	include_once("netpayclient.php");
	//加载 CURL 函数库，该库由 chinapay 提供，方便您使用 curl 发送 HTTP 请求
	include_once("lib_curl.php");
	$client_sign = new netpayclient();
	//导入私钥文件, 返回值即为您的商户号，长度15位
	$merId = $client_sign->buildKey(PRI_KEY);
	if(!$merId) {
		echo "导入私钥文件失败！";
		exit;
	}
		
	$merDate = $_REQUEST["merDate"];
	$merSeqId = $_REQUEST["merSeqId"];
	$cardNo = $_REQUEST["cardNo"];
	$usrName = $_REQUEST["usrName"];
	$openBank = $_REQUEST["openBank"];
	$prov = $_REQUEST["prov"];
	$city = $_REQUEST["city"];
	$transAmt = $_REQUEST["transAmt"];
	$purpose = $_REQUEST["purpose"];
	$subBank = $_REQUEST["subBank"];
	$flag = $_REQUEST["flag"];
	$signFlag = $_REQUEST["signFlag"];
	$version = $_REQUEST["version"];
	$termType = $_REQUEST["termType"];          
	
	//按次序组合报文信息为待签名串
	$plain = $merId . $merDate  . $merSeqId . $cardNo . $usrName  . $openBank  . $prov  . $city  . $transAmt  . $purpose  . $subBank  . $flag  . $version . $termType;
	//进行Base64编码
	$data = base64_encode($plain);
	//生成签名值，必填
	$chkValue = $client_sign->sign($data);
	if (!$chkValue) {
		echo "签名失败！";
		exit;
	}		
		
		
	$usrName = urlencode($usrName);         
	$openBank = urlencode($openBank);       
	$prov = urlencode($prov);                
	$city = urlencode($city);                
	$purpose = urlencode($purpose);          
	$subBank = urlencode($subBank);          

	
?>
<title>交易</title>
<h1>交易</h1>
<?php		
	if(($merSeqId!='')&&($merDate!='')){
	    $http = HttpInit();
		$post_data = "merId=$merId&merDate=$merDate&merSeqId=$merSeqId&cardNo=$cardNo&usrName=$usrName&openBank=$openBank&prov=$prov&city=$city&transAmt=$transAmt&purpose=$purpose&subBank=$subBank&flag=$flag&version=$version&termType=$termType&signFlag=$signFlag&chkValue=$chkValue";
		$output = HttpPost($http, $post_data, PAY_URL);
		
		if($output){
			$output = trim(strip_tags($output));
			
			echo "<h2>交易返回</h2>";
			echo "$output<br/>";
			echo "=================================<br/>";
			//开始解析数据
			$datas = explode("&",$output);
			foreach($datas as $data){
				echo "$data<br/>";
			}
			
			echo "=================================<br/>";
			
			$dex = strripos($output,"&");
			$plain = substr($output,0,$dex);
			echo "验签明文：<br/>" . $plain . "<br/>";
			$plaindata = base64_encode($plain);	
			$resp_code = $data[0];
			$chkValue = substr($output,$dex+ 10);
			echo "chkValue值：<br/>" . $chkValue . "<br/>";
				
			//开始验证签名，首先导入公钥文件
			$flag = $client_sign->buildKey(PUB_KEY);
			if(!$flag) {
				echo "导入公钥文件失败！";
			} else {
				$flag  =  $client_sign->verify($plaindata, $chkValue);
				if($flag) {
				//验证签名成功，
				echo "<h4>验证签名成功</h4>";
				//请把您自己需要处理的逻辑写在这里
												
				} else {
					echo "<h4>验证签名失败！</h4>";
					}
				}
				
		} else {
			echo "<h3>HTTP 请求失败！</h3>";
		}
		HttpDone($http);
	} else {
		echo "<h3>请填写订单日期和订单号</h3>";
	}
?>