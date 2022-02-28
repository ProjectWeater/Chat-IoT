<?php 
	/*Get Data From POST Http Request*/
	$datas = file_get_contents('php://input');
	/*Decode Json From LINE Data Body*/
	$deCode = json_decode($datas,true);


	file_put_contents('log.txt', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);

	$replyToken = $deCode['events'][0]['replyToken'];
	$recv_msg = $deCode['events'][0]['message']['text'];



	$messages = [];
	$messages['replyToken'] = $replyToken;
	$rep_msg = [];

	if($recv_msg == "สวัสดี") {
		$rep_msg ['text'] = "สวัสดีครับ";
		$rep_msg ['type'] = 'text';
	}else if($recv_msg == "อุณหภูมิ") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$temp = $strRet->feeds[0]->field1;
		$rep_msg['text'] = $temp;
		$rep_msg['type']='text';
	}else if($recv_msg == "ฝน") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$rain = $strRet->feeds[0]->field6;
		$rep_msg['text'] = $rain;
		$rep_msg['type']='text';
	}else if($recv_msg == "คุณภาพอากาศ") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$pm = $strRet->feeds[0]->field3;
		$rep_msg['text'] = $pm;
		$rep_msg['type']='text';
	}else if($recv_msg == "รูปภาพสถานที่") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$pict = $strRet->feeds[0]->field7;
		$rep_msg['text'] = $pict;
		$rep_msg['type']='text';
	}else if($recv_msg == "อาทิตย์ขึ้น-ตก") {
		$url = "https://api.sunrise-sunset.org/json?";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$sunset = $strRet->results->sunset;
		$sunrise = $strRet->results->sunrise;
		$sunset2 = "อาทิตย์ตก $sunset";
		$sunrise2 = "\nอาทิตย์ขึ้น $sunrise";
		$rep_msg['text'] = "$sunset2 $sunrise2" ;
		$rep_msg['type']='text';}
	}else if($recv_msg == "Dashboard") {
		$rep_msg['text'] = "https://www.facebook.com/profile.php?id=100004107043003";
		$rep_msg['type']='text';}
	// else{
	// 	$nsend = "กรุณาพิมพ์คำสั่ง ดังนี้ \n - อุณหภูมิ \n - ฝน \n - คุณภาพอากาศ \n - รูปภาพสถานที่ \n - Dashboard \n ขอบคุณครับ"
	// 	$rep_msg['text'] = $nsend;
	// 	$rep_msg['type']='text';}
	// }
		

	$messages['messages'][0] =  $rep_msg;

	$encodeJson = json_encode($messages);

	$LINEDatas['url'] = "https://api.line.me/v2/bot/message/reply";
 	$LINEDatas['token'] = "NVnIbDiVadUFT9jjco1mPfYVcTUQ3O7cEqGV8U8IpWykAm05iT6CoYmbf10J+YJZhZzUMLWe4sJGOcjLZAm2ofyv8/dtH0ILQPGaUeQgOMTrLTXfb15Nb1Ak3A7Bo9wuxWxP/QqzNRd+AuuTQttNLAdB04t89/1O/w1cDnyilFU=";
  	$results = sentMessage($encodeJson,$LINEDatas);

	/*Return HTTP Request 200*/
	http_response_code(200);


	function sentMessage($encodeJson,$datas)
	{
		$datasReturn = [];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $datas['url'],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $encodeJson,
		  CURLOPT_HTTPHEADER => array(
		    "authorization: Bearer ".$datas['token'],
		    "cache-control: no-cache",
		    "content-type: application/json; charset=UTF-8",
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		    $datasReturn['result'] = 'E';
		    $datasReturn['message'] = $err;
		} else {
		    if($response == "{}"){
			$datasReturn['result'] = 'S';
			$datasReturn['message'] = 'Success';
		    }else{
			$datasReturn['result'] = 'E';
			$datasReturn['message'] = $response;
		    }
		}

		return $datasReturn;
	}
?>