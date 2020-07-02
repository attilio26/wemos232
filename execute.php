<?php
//18-06-2020
//started on 06-04-2017
// La app di Heroku si può richiamare da browser con
//			https://myespot.herokuapp.com/


/*API key = 337086481:AAFZM670VVwr2q9DDqx1_XbHBOlVnQxSroY

da browser request ->   https://api.telegram.org/bot337086481:AAFZM670VVwr2q9DDqx1_XbHBOlVnQxSroY/getMe
           answer  <-   {"ok":true,"result":{"id":337086481,"first_name":"heroku","username":"heroku_bot"}}

riferimenti:
https://gist.github.com/salvatorecordiano/2fd5f4ece35e75ab29b49316e6b6a273
https://www.salvatorecordiano.it/creare-un-bot-telegram-guida-passo-passo/
*/

//------passaggio da getupdates a  WEBHOOK
//da browser request ->   https://api.telegram.org/bot337086481:AAFZM670VVwr2q9DDqx1_XbHBOlVnQxSroY/setWebhook?url=https://myespbot.herokuapp.com/execute.php
//					 answer  <-   {"ok":true,"result":true,"description":"Webhook was set"}
//          From now If the bot is using getUpdates, will return an object with the url field empty.
//------passaggio da webhook a  GETUPDATES
//da browser request ->   https://api.telegram.org/bot337086481:AAFZM670VVwr2q9DDqx1_XbHBOlVnQxSroY/setWebhook?url=
//					 answer  <-   {"ok":true,"result":true,"description":"Webhook was deleted"}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update){
  exit;
}

function clean_html_page($str_in){
	$startch = strpos($str_in,"</h2><h1>") + 1 ;								//primo carattere utile da estrarre
	$endch = strpos($str_in,"<br><footer>p");										//ultimo carattere utile da estrarre
	$str_in = substr($str_in,$startch,$endch - $startch);				// substr(string,start,length)
	$str_in = str_replace("<a href='?a="," ",$str_in);
	$str_in = str_replace("</a></h2><h2>"," ",$str_in);
	$str_in = str_replace("_  0'/>","_",$str_in);
	$str_in = str_replace("_  1'/>","_",$str_in);
	$str_in = str_replace("_  2'/>","_",$str_in);
	$str_in = str_replace("_  3'/>","_",$str_in);
	$str_in = str_replace("_  4'/>","_",$str_in);
	return $str_in;
}


$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";
// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
// converto tutti i caratteri alfanumerici del messaggio in minuscolo
$text = strtolower($text);
header("Content-Type: application/json");

//ATTENZIONE!... Tutti i testi e i COMANDI contengono SOLO lettere minuscole
$response = '';
$helptext = "List of commands :
/int_on   -> Lampada interna accesa
/int_off  -> Lampada interna spenta
/ext_on   -> Lampada veranda accesa
/ext_off  -> Lampada veranda spenta
/lock			-> Sblocco Elettroserratura
/azz      -> Azzeramento Ledcounter, Caleffi
/ts				-> ThingSpeak canale 88858
/lina     -> DS18B20, DHT11, BMP280, Ledcounter, Caleffi  <html>
";

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto   \n". $helptext;
}
//<-- Comandi ai rele
elseif(strpos($text,"int_on")){
	$resp = file_get_contents("http://dario95.ddns.net:28081/?a=2");
	$response = clean_html_page($resp);
}
elseif(strpos($text,"int_off")){
	$resp = file_get_contents("http://dario95.ddns.net:28081/?a=3");
	$response = clean_html_page($resp);
}
elseif(strpos($text,"ext_on")){
	$response = file_get_contents("http://dario95.ddns.net:28081/?a=0");
}
elseif(strpos($text,"ext_off")){
	$response = file_get_contents("http://dario95.ddns.net:28081/?a=1");
}
//<-- Azzeramento contatori slave
elseif($text=="/azz"){
	$response = file_get_contents("http://dario95.ddns.net:28081/azz");
}
//<-- Lettura parametri slave
elseif($text=="/lina"){
	$response = file_get_contents("http://dario95.ddns.net:28081/lina");
}
//<-- Sblocco elettroserratura
elseif(strpos($text,"lock")){
	$response = file_get_contents("http://dario95.ddns.net:28081/?a=4");
}
//<-- collegamento a ThingSpeak canale 88858 (fa riferimento a un file contenuto in Raspberry Wheezy)
elseif(strpos($text,"ts")){
	$response = file_get_contents("http://dario95.ddns.net:9080/linkTS88858.html");
}
//<-- Manda a video la risposta completa
elseif($text=="/verbose"){
	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname. "\n". $helptext ;
	$response = $response. "\n\n Heroku + dropbox gmail.com";
}

else
{
	$response = "Comando non valido!";
}

// la mia risposta è un array JSON composto da chat_id, text, method
// chat_id mi consente di rispondere allo specifico utente che ha scritto al bot
// text è il testo della risposta
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
// imposto la keyboard
// Gli EMOTICON sono a:     http://www.charbase.com/block/miscellaneous-symbols-and-pictographs
$parameters["reply_markup"] = '{ "keyboard": [["/int_on \ud83d\udd34", "/int_off \ud83d\udd35"],["/ext_on \ud83d\udd34", "/ext_off \ud83d\udd35"],["/lock \ud83d\udd11"],["/azz","/lina","/ts"]], "one_time_keyboard": false, "resize_keyboard": true}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);

?>