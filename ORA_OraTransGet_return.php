<?php
/**
 * @author huang.xuting
 *
 */
	header('Content-type: text/html; charset=GBK');
	include_once("netpayclient_config.php");

	//���� netpayclient ���
	include_once("netpayclient.php");
	//���� CURL �����⣬�ÿ��� chinapay �ṩ��������ʹ�� curl ���� HTTP ����
	include_once("lib_curl.php");
	$client_sign = new netpayclient();
	//����˽Կ�ļ�, ����ֵ��Ϊ�����̻��ţ�����15λ
	$merId = $client_sign->buildKey(PRI_KEY);
	if(!$merId) {
		echo "����˽Կ�ļ�ʧ�ܣ�";
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
	
	//��������ϱ�����ϢΪ��ǩ����
	$plain = $merId . $merDate  . $merSeqId . $cardNo . $usrName  . $openBank  . $prov  . $city  . $transAmt  . $purpose  . $subBank  . $flag  . $version . $termType;
	//����Base64����
	$data = base64_encode($plain);
	//����ǩ��ֵ������
	$chkValue = $client_sign->sign($data);
	if (!$chkValue) {
		echo "ǩ��ʧ�ܣ�";
		exit;
	}		
		
		
	$usrName = urlencode($usrName);         
	$openBank = urlencode($openBank);       
	$prov = urlencode($prov);                
	$city = urlencode($city);                
	$purpose = urlencode($purpose);          
	$subBank = urlencode($subBank);          

	
?>
<title>����</title>
<h1>����</h1>
<?php		
	if(($merSeqId!='')&&($merDate!='')){
	    $http = HttpInit();
		$post_data = "merId=$merId&merDate=$merDate&merSeqId=$merSeqId&cardNo=$cardNo&usrName=$usrName&openBank=$openBank&prov=$prov&city=$city&transAmt=$transAmt&purpose=$purpose&subBank=$subBank&flag=$flag&version=$version&termType=$termType&signFlag=$signFlag&chkValue=$chkValue";
		$output = HttpPost($http, $post_data, PAY_URL);
		
		if($output){
			$output = trim(strip_tags($output));
			
			echo "<h2>���׷���</h2>";
			echo "$output<br/>";
			echo "=================================<br/>";
			//��ʼ��������
			$datas = explode("&",$output);
			foreach($datas as $data){
				echo "$data<br/>";
			}
			
			echo "=================================<br/>";
			
			$dex = strripos($output,"&");
			$plain = substr($output,0,$dex);
			echo "��ǩ���ģ�<br/>" . $plain . "<br/>";
			$plaindata = base64_encode($plain);	
			$resp_code = $data[0];
			$chkValue = substr($output,$dex+ 10);
			echo "chkValueֵ��<br/>" . $chkValue . "<br/>";
				
			//��ʼ��֤ǩ�������ȵ��빫Կ�ļ�
			$flag = $client_sign->buildKey(PUB_KEY);
			if(!$flag) {
				echo "���빫Կ�ļ�ʧ�ܣ�";
			} else {
				$flag  =  $client_sign->verify($plaindata, $chkValue);
				if($flag) {
				//��֤ǩ���ɹ���
				echo "<h4>��֤ǩ���ɹ�</h4>";
				//������Լ���Ҫ�������߼�д������
												
				} else {
					echo "<h4>��֤ǩ��ʧ�ܣ�</h4>";
					}
				}
				
		} else {
			echo "<h3>HTTP ����ʧ�ܣ�</h3>";
		}
		HttpDone($http);
	} else {
		echo "<h3>����д�������ںͶ�����</h3>";
	}
?>