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
		$temp2 = number_format($temp,2);
		$rep_msg['text'] = "อุณหภูมิตอนนี้ $temp2 องศา";
		$rep_msg['type']='text';
	}else if($recv_msg == "ความชื้น") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$hum = $strRet->feeds[0]->field2;
		$hum2 = number_format($hum,0);
		$rep_msg['text'] = "ความชื้นสัมพัทธ์ในอากาศ $hum2 %";
		$rep_msg['type']='text';
	}else if($recv_msg == "ฝน") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$rain = $strRet->feeds[0]->field6;
		if ($rain >= 90.1){
			$lv_rain = "ฝนตกหนักมาก";
		}else if ($rain >= 35.1){
			$lv_rain = "ฝนตกหนัก";
		}else if ($rain >= 10.1){
			$lv_rain = "ฝนตกปานกลาง";
		}else if ($rain >= 0.1){
			$lv_rain = "ฝนตกเล็กน้อย";
		}else {
			$lv_rain = "ฝนไม่ตก";
		}
		$rep_msg['text'] = $lv_rain;
		$rep_msg['type']='text';
	}else if($recv_msg == "คุณภาพอากาศ") {
		$url = "https://api.thingspeak.com/channels/1555446/feeds.json?results=1";
		$strRet = file_get_contents($url);
		$strRet = json_decode($strRet);
		$pm = $strRet->feeds[0]->field3;
		if ($pm >= 201){
			$lv_pm = "คุณภาพอากาศมีผลกระทบต่อสุขภาพ";
		}else if ($pm >= 101){
			$lv_pm  = "คุณภาพอากาศเริ่มมีผลกระทบต่อสุขภาพ";
		}else if ($pm >= 51){
			$lv_pm  = "คุณภาพอากาศปานกลาง";
		}else if ($pm >= 26){
			$lv_pm  = "คุณภาพอากาศดี";
		}else {
			$lv_pm = "คุณภาพอากาศดีมาก";
		}
		$rep_msg['text'] = $lv_pm;
		$rep_msg['type']='text';
	}else if($recv_msg == "รูปภาพสถานที่") {
		$rep_msg['originalContentUrl'] = "https://firebasestorage.googleapis.com/v0/b/esp-firebase-demo-c8454.appspot.com/o/data%2Fphoto.jpg?alt=media&token=4415c22a-a0ba-4813-a7c0-5691f71ed343";
		$rep_msg['previewImageUrl'] = "https://firebasestorage.googleapis.com/v0/b/esp-firebase-demo-c8454.appspot.com/o/data%2Fphoto.jpg?alt=media&token=4415c22a-a0ba-4813-a7c0-5691f71ed343";
		$rep_msg['type']='image';
	}else if($recv_msg == "Dashboard") {
		$rep_msg['text'] = "https://lab-iot.herokuapp.com/";
		$rep_msg['type']='text';
	}else{
		$nsend = "กรุณาพิมพ์คำสั่ง ดังนี้ \n - อุณหภูมิ \n - ฝน \n - คุณภาพอากาศ \n - รูปภาพสถานที่ \n - Dashboard \n ขอบคุณครับ";
		$rep_msg['text'] = $nsend;
		$rep_msg['type']='text';
	}
		

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