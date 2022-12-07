<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: text/html; charset=utf-8");
	include_once('model/bigModelForMe.php');
	//**************************************get all commands status ********************************** */
	$clients = getUser('',$manager);
	$lesMails = array();
	$commandes = array();
	function getStatus(){
		$tab_status = array();

		$url = "http://eurocoop.teliway.com/appli/veurocoop/tracking/suivi.php?code=eurocoop&clef=313339&sMode=remettant&rem=FERAUD&sLogVersion=veurocoop&sNomRepertoire=eurocoop&idDo=121&iRemont=1";
		
		$html = file_get_contents($url);

		$start = stripos($html, 'class="listeGestion tfTable"');

		$end = stripos($html, '<table>', $offset = $start);

		$length = $end - $start;

		$htmlSection = substr($html, $start, $length);
		$htmlSection = explode("récep.", $htmlSection);
		array_shift($htmlSection);
		function get_between_data($string, $start, $end)
		{
			$pos_string = stripos($string, $start);
			$substr_data = substr($string, $pos_string);
			$string_two = substr($substr_data, strlen($start));
			$second_pos = stripos($string_two, $end);
			$string_three = substr($string_two, 0, $second_pos);
			// remove whitespaces from result
			$result_unit = trim($string_three);
			// return result_unit
			return $result_unit;
		}
		foreach ($htmlSection as $item) {
			$nb = 0;
			$rf = strtok($item,"Réf");
			foreach($htmlSection as $key2=>$item2){
				$rf2 = strtok($item2,"Réf");
				if($rf == $rf2 && $nb == 0){
					$nb++;
				}else if($rf == $rf2 && $nb >= 1){
					unset($htmlSection[$key2]);
					break;
				}
			}
		}
		
		foreach($htmlSection as $item){
			$monElmt = strip_tags($item);
			$status = get_between_data($monElmt,"kg","(");

			$ref = get_between_data($monElmt,"Réf. Exp","QUINCAILLERIE FERAUD");
			$ref = str_replace(":","",$ref);
			$ref =  str_replace(".","",$ref);

			$destinataire = get_between_data($monElmt,"FR-13011 MARSEILLE 11","UM : ");

			$ar = array($status,$ref,$destinataire);
			$tab_status[] = $ar;
		}
		return $tab_status;
	}

	//********************************************************************************************************* */

	//*****************************************save in bdd ********************************** */
	try{
		$mesStatus = getStatus();
		$clients = $clients->clients;
		foreach($mesStatus as $key2=>$item2){
			$valeurinit = str_replace("&nbsp;","",$item2[1]);
			$recup = $manager->selectionUnique2('suivi_expedition',array('*'),"ref='$valeurinit'");
			if(count($recup) == 0){
				if($item2[0] != '' && $item2[1] != '' && $item2[2] != ''){
					$emaill = '';
					for($a=0;$a<count($clients);$a++){
						$nom = $clients[$a]->adresse->nom;
						$ligne = $clients[$a]->adresse->ligne_1; //adresse
						$code_postal = "FR-".$clients[$a]->adresse->code_postal;
						$ville = $clients[$a]->adresse->ville;

						$add = "$nom$ligne$code_postal$ville";
						$compare1 = mb_strtoupper($add, 'UTF-8');
						$compare1 = str_replace(" ","", $compare1);
						
						$compare2 = mb_strtoupper($item2[2], 'UTF-8');
						$compare2 = str_replace(" ","", $compare2);
						 if($compare1 == $compare2){
							$code_client = $clients[$a]->code;
							$leClient = getUser($code_client,$manager);
							$emaill = $leClient->client->email;
							unset($clients[$a]);
							$a = count($clients);
						}
					}
					if($emaill != ""){
						$rr = str_replace("&nbsp;","",$item2[1]);
						
						if($item2[0] == "Livré conforme"){
							$table = array(
								'statut'=>"$item2[0]",
								'ref'=>$rr,
								'mail'=>"$emaill",
								'client_info'=>"$item2[2]",
								'send'=>"true"
							);
							if($item2[0] != ''){
								if(intval($rr) != 0){
									//$bl = substr("$rr", 2);
									$bl = intval($rr);
								}else{
									$bl = $rr;
								}
								$numcmd = $manager->selectionUnique2('numCommand',array('*'),"bl LIKE '%$bl%'");
								$numcmd = $numcmd[0]->ncommand;
								redirectTo($item2[0],$emaill,$numcmd);
							}
							$manager->insertion('suivi_expedition',$table,'');
						}else{
							$table = array(
								'statut'=>"$item2[0]",
								'ref'=>$rr,
								'mail'=>"$emaill",
								'client_info'=>"$item2[2]",
								'send'=>"false"
							);
							if($item2[0] != ''){
								if(intval($rr) != 0){
									//$bl = substr("$rr", 2);
									$bl = intval($rr);
								}else{
									$bl = $rr;
								}
								$numcmd = $manager->selectionUnique2('numCommand',array('*'),"bl LIKE '%$bl%'");
								$numcmd = $numcmd[0]->ncommand;
								redirectTo($item2[0],$emaill,$numcmd);
							}
							$manager->insertion('suivi_expedition',$table,'');
						}
					}
				}
			}else{
				if($item2[0] != $recup[0]->statut){
					if($item2[0] == "Livré conforme"){
						$table = array(
							'statut'=>"$item2[0]",
							'send'=>"true"
						);
						$num_exp = $recup[0]->num_exp;
						$maill = $recup[0]->mail;
						$bl =  $recup[0]->ref;
						if(intval($bl) != 0){
							//$bl = substr("$bl", 2);
							$bl = intval($bl);
						}
						$numcmd = $manager->selectionUnique2('numCommand',array('*'),"bl LIKE '%$bl%'");
						$numcmd = $numcmd[0]->ncommand;
						redirectTo($item2[0],$maill,$numcmd);
						$manager->modifier('suivi_expedition',$table,"num_exp=$num_exp");
					}else{
						$table = array(
							'statut'=>"$item2[0]",
							'send'=>"false"
						);
						$maill = $recup[0]->mail;
						$num_exp = $recup[0]->num_exp;
						$bl =  $recup[0]->ref;
						if($item2[0] != ''){
							if(intval($bl) != 0){
								//$bl = substr("$bl", 2);
								$bl = intval($bl);
							}
							$numcmd = $manager->selectionUnique2('numCommand',array('*'),"bl LIKE '%$bl%'");
							$numcmd = $numcmd[0]->ncommand;
							redirectTo($item2[0],$maill,$numcmd);
						}
						$manager->modifier('suivi_expedition',$table,"num_exp=$num_exp");
					}
				}
			}
		}
		echo 'Entrée ajoutée dans la table';
	}catch(PDOException $e){
		echo "Erreur : " . $e->getMessage();
	}
	//****************************************************************************************** */

	//************************************get api key ************************************* */
		function getApi($manager){
			$recup = $manager->selectionUnique2('api',array('*'),'');
			$t = $recup[0]->letemps;
			$pp = intval(time());
			$diff = $pp-$t;
			if(count($recup) > 0 && $diff < 10*3600){
				return $recup[0]->valeur;
			}else{
				// init curl object        
				$ch = curl_init();
				// define options
				$optArray = array(
					CURLOPT_URL => 'http://www.quincaillerie-feraud.fr/yzyapi/1.0.0/login?username=ITFERAUD&password=PASS4FERO',
					CURLOPT_RETURNTRANSFER => true
				);
				// apply those options
				curl_setopt_array($ch, $optArray);
				// execute request and get response
				$result = curl_exec($ch);
				// also get the error and response code
				$errors = curl_error($ch);
				$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				// var_dump($errors);
				// var_dump($response);
				$result = json_decode($result);
				$api_key = $result->api_key;
				if(count($recup) > 0){
					$table = array(
						'valeur'=>"$api_key",
						'letemps'=>time(),
					);
					$manager->modifier('api',$table,"letemps=$t");
				}else{
					$table = array(
						'valeur'=>"$api_key",
						'letemps'=>time(),
					);
					$manager->insertion('api',$table,'');
				}
				return $api_key;
			}
		 }
					
	//***********************************get all users************************ */
			function getUser($code,$manager){
				$url = "http://www.quincaillerie-feraud.fr/yzyapi/1.0.0/clients/$code"; //AA001 for one user
				$apiKey = getApi($manager);   // should match with Server key
				$headers = array(	
					'Authorization: '.$apiKey
				);
				// Send request to Server
				$ch2 = curl_init();
				// To save response in a variable from server, set headers;
				curl_setopt( $ch2, CURLOPT_URL, $url);
				curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
					"X-API-Key: $apiKey",
					"customer-header2:value2"
				));
				// Get response
				$responseUsers = curl_exec($ch2);
				curl_close($ch2); 
				// Decode
				$client = json_decode($responseUsers);
				//echo "mon email : ".$clients->client->email;
				return $client;
			}

		function redirectTo($statut,$email,$numcmd){
				$ch = curl_init();
				// define options
				$optArray = array(
					//https://feraud-color.fr//mails/testMonMail.php?statut=$statut&mail=$email

					CURLOPT_URL => "https://it-feraud.com/auto_notification/send_mail.php?statut=$statut&mail=$email&numCommand=$numcmd",
					CURLOPT_RETURNTRANSFER => true
				);
				// apply those options
				curl_setopt_array($ch, $optArray);
			
				// execute request and get response
				$result = curl_exec($ch);
			
				// also get the error and response code
				$errors = curl_error($ch);
				$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
		}
	//***************************************************************************************** */

 ?>
