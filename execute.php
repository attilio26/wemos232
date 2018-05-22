<?php
//09-05-2017
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

$response = '';

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto! \n List of commands : \n /temp -> temperatura da DS18B20
	/led -> Contatore impulsi led ENEL (reset ogni 20m) \n /rh -> umidita da DHT11
	/mb -> Pressione ATM da BMP280 \n /acq (reset ogni 20m) -> flusso acqua da mulinello Caleffi
	/meteo -> dati meteo CRISPIANO da openWeatherMap
	/verbose -> parametri del messaggio
	...";
}
elseif($text=="/temp"){
	$DS18B20 = file_get_contents('https://thingspeak.com/channels/88858/fields/1/last');
	$response = "Temperatura: ". $DS18B20. " Celsius";
}
elseif($text=="/led"){
	$LedC = file_get_contents('https://thingspeak.com/channels/88858/fields/2/last');
	$response = "ConsumoENEL: ". $LedC .  " WattOra";
}
elseif($text=="/rh"){
	$DHT11 = file_get_contents('https://thingspeak.com/channels/88858/fields/3/last');
	$response = "Umidita: ". $DHT11 .  " %";	
}
elseif($text=="/mb"){
	$BMP280 = file_get_contents('https://thingspeak.com/channels/88858/fields/4/last');
	$response = "Pressione: ". $BMP280 .  " mbar";	
}
elseif($text=="/acq"){
	$Caleffi = file_get_contents('https://thingspeak.com/channels/88858/fields/5/last'); 
	$response = "ConsACQUA: ". $Caleffi .  " x4 ml";	
}
elseif($text=="/meteo"){
	//http://api.openweathermap.org/data/2.5/weather?q=Crispiano,it&appid=842941aeb62129ebb1f279ad43af4b11&units=metric&cnt=7&lang=en
	$city="Crispiano";
	$ApiKey = "842941aeb62129ebb1f279ad43af4b11";
	$country="IT"; //Two digit country code
	$MeteoUrl="http://api.openweathermap.org/data/2.5/weather?q=".$city.",".$country."&appid=".$ApiKey  ."&units=metric&cnt=7&lang=en";
	//Reads entire file into a string
	/* JSON data is written as name/value pairs. A name/value pair consists of a field name (in double quotes), 
	followed by a colon, followed by a value: { "name":"value" }
	Response JSON from http request:
	
		{"coord":{"lon":17.23,"lat":40.61},
		"weather":[{"id":800,"main":"Clear","description":"clear sky","icon":"01d"}],
		"base":"stations",
		"main":{"temp":12.57,"pressure":1017,"humidity":81,"temp_min":11,"temp_max":15},
		"visibility":10000,
		"wind":{"speed":2.1,"deg":100},
		"clouds":{"all":0},
		"dt":1491552000,
		"sys":{"type":1,"id":5945,"message":0.003,"country":"IT","sunrise":1491539065,"sunset":1491585743},
		"id":3177808,
		"name":"Crispiano","cod":200}
	
	where	id					city Identification
			dt					Time of data receiving in unixtime GMT
			coord.lat coord.lng	city location
			name				city name
			main.temp			Temperature in Kelvin. Subtracted 273.15 
			main.humidity		Humidity in %
			main.temp_min 		Minimum and maximum temperature
			main.temp_max
			main.pressure		Atmospheric pressure in hPa
			wind.speed			Wind speed in mps		
			wind.deg			Wind direction in degrees
			clouds.all			Cloudiness in %
			rain.3h				Precipitation volume mm per 3 hours
			snow.3h				Precipitation volume mm per 3 hours
			weather
	*/
	$json=file_get_contents($MeteoUrl);	
	//Returns the value encoded in JavaScriptObjectNotation data-interchange format in appropriate PHP type
	$data=json_decode($json,true);
//	echo $city.",".$country."   ";
	//Get Temperature in Celsius
	$tc = $data['main']['temp'];
	//Get current pressure in millibar
	$mbar = $data['main']['pressure'];
	//Get current humidity in %
	$um = $data['main']['humidity'];
	//Get weather condition
//	echo $data['weather'][0]['main']."  ";
	//Get cloud percentage
	$cloud  =  $data['clouds']['all'];
	//Get wind speed
	$speed = $data['wind']['speed'];	
	//Get wind direction
	$dir =  $data['wind']['deg'];	
	$response = "GradiC: ". $tc . "\nrh: ". $um  ."\nPress: " . $mbar. "\nNubi: ". $cloud .
	"\nVento: ". $speed ."  dir: ". $dir ."\n" ;	
}
//<-- Manda a video la risposta completa
elseif($text=="/verbose"){
	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname ;		
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
$parameters["reply_markup"] = '{ "keyboard": [["/temp", "/mb", "/rh", "/meteo"], ["/led", "/acq", "/verbose"]], "one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);

?>