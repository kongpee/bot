<?php
 $LINEData = file_get_contents('php://input');
 $jsonData = json_decode($LINEData,true);
 $replyToken = $jsonData["events"][0]["replyToken"];
 $text = $jsonData["events"][0]["message"]["text"];
 
 $servername = "localhost";
 $username = "root";
 $password = "";
 $dbname = "mydb";
 $mysql = new mysqli($servername, $username, $password, $dbname);
 mysqli_set_charset($mysql, "utf8");
 
 if ($mysql->connect_error){
 $errorcode = $mysql->connect_error;
 print("MySQL(Connection)> ".$errorcode);
 }
 
 function sendMessage($replyJson, $token){
   $ch = curl_init($token["URL"]);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLINFO_HEADER_OUT, true);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array(
       'Content-Type: application/json',
       'Authorization: Bearer ' . $token["AccessToken"])
       );
   curl_setopt($ch, CURLOPT_POSTFIELDS, $replyJson);
   $result = curl_exec($ch);
   curl_close($ch);
return $result;
}
 
 $getUser = $mysql->query("SELECT * FROM `rent` WHERE `rent_lease`='$text'");
 $getuserNum = $getUser->num_rows;
 
 if ($getuserNum == "0"){
     $message = '{
     "type" : "text",
     "text" : "ไม่มีข้อมูลที่ต้องการ"
     }';
     $replymessage = json_decode($message);
 } else {
  
   while(
     $row = $getUser->fetch_assoc()){
     $question = $row['rent_lease'];
     $result = $row['rent_price'];
   }
   $replymessage["type"] = "text";
   $replymessage["text"] = $question." ".$result;
 }
 
 $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
 $lineData['AccessToken'] = "0PD9YyHN/SJzebQceZkL+AWCcL9ZC3qNPch7Yq3xp4rAiUWiHULqDMhyy0kswwug+avg95h09uRbbLhJ/7SHRSUaHP+kMBu0IpyeGx1bOg5vTibdpCm1EuHQbWrvqKKeNDcqDw1Zo1pci06CE4Y8xAdB04t89/1O/w1cDnyilFU=";
 $replyJson["replyToken"] = $replyToken;
 $replyJson["messages"][0] = $replymessage;
 
 $encodeJson = json_encode($replyJson);
 
 $results = sendMessage($encodeJson,$lineData);
 echo $results;
 http_response_code(200);