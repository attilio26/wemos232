<?php
//04-09-2018
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
/on_on    -> Interno ON  Veranda ON
/Ion_Eoff -> Interno ON  Veranda OFF
/Ioff_Eon -> Interno OFF Veranda ON
/off_off  -> Interno OFF Veranda OFF
/mis      -> Lettura DS18B20, DHT11, BMP280, Ledcounter, Caleffi
/lina     -> DS18B20, DHT11, BMP280, Ledcounter, Caleffi  <html>
";

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto   \n". $helptext;
}
//<-- Comandi ai rele
elseif($text=="/on_on"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/3");
}
elseif($text=="/ion_eoff"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/2");
}
elseif($text=="/ioff_eon"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/1");
}
elseif($text=="/off_off"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/0");
}
//<-- Lettura parametri slave
elseif($text=="/mis"){
	$response = file_get_contents("http://dario95.ddns.net:28081/mis");
}
//<-- Lettura parametri slave
elseif($text=="/lina"){
	$response = file_get_contents("http://dario95.ddns.net:28081/lina");
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
$parameters["reply_markup"] = '{ "keyboard": [["\ud83d\ude08", "/ion_eoff"],["/ioff_eon", "/off_off"],["/mis","/lina","/verbose"]], "one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);

?>