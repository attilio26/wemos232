<?php
//28-11-2018
//started on 01-06-2017
// La app di Heroku si puo richiamare da browser con
//			https://wemos485.herokuapp.com/
/*API key = 315635925:AAFHPIBs9_aGXqv2_IBQPVJWYcAFM-tWsWU
da browser request ->   https://wemos485.herokuapp.com/register.php
           answer  <-   {"ok":true,"result":true,"description":"Webhook is already set"}
In questo modo invocheremo lo script register.php che ha lo scopo di comunicare a Telegram
l’indirizzo dell’applicazione web che risponderà alle richieste del bot.
da browser request ->   https://api.telegram.org/bot315635925:AAFHPIBs9_aGXqv2_IBQPVJWYcAFM-tWsWU/getMe
           answer  <-   {"ok":true,"result":{"id":315635925,"first_name":"wemos485","username":"wemos485_bot"}}
*********
	https://wemos485.herokuapp.com/register.php
											è questo che permette a Telegram di essere linkato a Heroku 
	
*********			   
		   
riferimenti:
https://gist.github.com/salvatorecordiano/2fd5f4ece35e75ab29b49316e6b6a273
https://www.salvatorecordiano.it/creare-un-bot-telegram-guida-passo-passo/
--- http://www.andreaminini.com/telegram/come-pubblicare-automaticamente-su-telegram-tramite-ifttt
*/
$content = file_get_contents("php://input");
$update = json_decode($content, true);
if(!$update)
{
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
// Note our use of ===.  Simply == would not work as expected
// because the position of '/' was the 0th (first) character
if(strpos($text, "/start") === 0 || $text=="/inf" || $text == "help"){
	$response = "Ciao $firstname, benvenuto! \n List of commands : 
	/bed  -> Lettura stazione1
	/din  -> Lettura stazione2  
	/ktc 	-> Lettura stazione3
	/lvg 	-> Lettura stazione4 
	/blr 	-> Lettura stazione5
	/hpg	-> Lettura stazione6
  /off  -> Spegne tutti i rele	... su bus RS485  \n
	/fsh1 -> Lampada Pesci ON  \n /fsh0 -> Lampada Pesci OFF
	/lob1 -> Lampada Atrio ON  \n /lob0  -> Lampada Atrio OFF
	/bth1 -> Lamp garden ON \n /bth0 -> Lamp garden OFF \n
	/lina     -> DS18B20, DHT11, Ledcounter, Caleffi StatoRele
	/i1_e0 -> Lampada veranda OFF  Lampada ingresso ON 
	/i0_e1 -> Lampada veranda ON   Lampada ingresso OFF 
	/1bth -> Lampade Lina ON \n /0bth -> Lampade Lina OFF
	/rasp -> RASPI webServer
	/myip	-> Indirizzo di rete attuale
	/000  -> farinemill web site
	/altr -> altervista website
	/inf -> parametri del messaggio \n
	chatId ".$chatId. "\n messId ".$messageId. "\n user ".$username. "\n lastname ".$lastname. "\n firstname ".$firstname ;		
}
//------------
//<-- Lettura parametri slave1
elseif(strpos($text,"bed")){
	$response = file_get_contents("http://dario95.ddns.net:8083/letto");
}
//<-- Lettura parametri slave2
elseif(strpos($text,"din")){
	$response = file_get_contents("http://dario95.ddns.net:8083/pranzo");
}
//<-- Lettura parametri slave3
elseif(strpos($text,"ktc")){
	$response = file_get_contents("http://dario95.ddns.net:8083/cucina");
}
//<-- Lettura parametri slave4
elseif(strpos($text,"lvg")){
	$response = file_get_contents("http://dario95.ddns.net:8083/salotto");
}
//<-- Lettura parametri slave5
elseif(strpos($text,"blr")){
	$response = file_get_contents("http://dario95.ddns.net:8083/caldaia");
}
//<-- Lettura parametri slave6
elseif(strpos($text,"hpg")){
	$response = file_get_contents("http://dario95.ddns.net:8083/heatplug");
}
//<-- Rele degli slaves tutti a riposo
elseif(strpos($text,"off")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/0/0");
}
//------------
//<-- Accensione rele1 su ESP01_lamp  	Cambiata la porta del router dopo tentativi da 8082  31/10/2017
elseif(strpos($text,"fsh1")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio1/1");
}
//<-- Spegnimento rele1 su ESP01_lamp	Cambiata la porta del router dopo tentativi da 8082  31/10/2017
elseif(strpos($text,"fsh0")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio1/0");
}
//<-- Accensione rele2 su ESP01_lamp	Cambiata la porta del router dopo tentativi da 8082  31/10/2017
elseif(strpos($text,"lob1")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio2/1");
}
//<-- Spegnimento rele2 su ESP01_lamp	Cambiata la porta del router dopo tentativi da 8082  31/10/2017
elseif(strpos($text,"lob0")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio2/0");
}
//<-- Accensione rele1 + rele2 su ESP01_lamp	 
elseif(strpos($text,"bth1")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio3/1");
}
//<-- Spegnimento rele1 + rele2 su ESP01_lamp	 
elseif(strpos($text,"bth0")){
	$response = file_get_contents("http://dario95.ddns.net:28082/gpio3/0");
}
//------------
//<-- Accensione rele su wemos232	Cambiata la porta del router dopo tentativi da 8081  31/10/2017
//<-- Lettura parametri da wemos232
elseif($text=="/lina"){
	$response = file_get_contents("http://dario95.ddns.net:28081/lina");
}
elseif($text=="/i1_e0"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/2");
}
elseif($text=="/i0_e1"){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/1");
}
//<-- Accensione rele2 + rele1 su wemos232	
elseif(strpos($text,"1bth")){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/3");
}
//<-- Spegnimento rele2 + rele1 su wemos232	
elseif(strpos($text,"0bth")){
	$response = file_get_contents("http://dario95.ddns.net:28081/rele/0");
}
//<-- Manda a video la risposta completa
elseif($text=="/inf"){
//	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname. "   ".
//	"TS  ".$date."   testo  ".$text;
	$response = file_get_contents("http://dario95.ddns.net:28081?");
}
//<-- collegamento a web server locale RASPI2
elseif(strpos($text,"rasp")){
	$response = file_get_contents("http://dario95.ddns.net:9080/link.html");
}
//<-- collegamento a web hosting farinemill.000webhostapp.com
elseif(strpos($text,"000")){
	$response = file_get_contents("http://dario95.ddns.net:9080/link000.html");
}
//<-- collegamento a web hosting attilio26.altervista.org
elseif(strpos($text,"altr")){
	$response = file_get_contents("http://dario95.ddns.net:9080/link_altervista.html");
}
//<-- indirizzo di rete attuale
elseif(strpos($text,"/myip")){
	$response = file_get_contents("http://ip.42.pl");
}
else
{
	$response = "Unknown command!";			//<---Capita quando i comandi contengono lettere maiuscole
}
// la mia risposta è un array JSON composto da chat_id, text, method
// chat_id mi consente di rispondere allo specifico utente che ha scritto al bot
// text è il testo della risposta
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
//   	ReplyKeyboardMarkup     imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [
	["/bed","/din","/ktc","/lvg","/blr","/hpg","/off"],
	["/fsh1","/fsh0","/lob1","/lob0","/bth1","/bth0"],
	["/lina","/i1_e0","/i0_e1","/1bth","/0bth"],
	["/rasp","/altr","/myip"]], 
	"resize_keyboard": true, 
	"one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>