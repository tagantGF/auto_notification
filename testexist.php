<?php
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: text/html; charset=utf-8");
	include_once('model/bigModelForMe.php');
	
	//************************************get api key ************************************* */
		function getApi($manager){
			$recup = $manager->selectionUnique2('api',array('*'),'');
			return  $recup[0]->valeur;
		 }
	//************************************************************************************** */
        function dateDifference($datetime){
            $datetime1 = new DateTime($datetime); // Date dans le passé
            $datetime2 = new DateTime(date("Y-m-d"));   // Date du jour (2018-09-07 16:10:21)
            $interval = $datetime1->diff($datetime2);
            return intval($interval->days);
        }
        function saveCommandNumber($manager){
            if(true){
                    $url = "http://www.quincaillerie-feraud.fr/yzyapi/1.0.0/commandes"; //AA001 for one user
                    $apiKey = '535069504caac753ed1fc3651ce7d129'; //getApi($manager);   // should match with Server key
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
                        "customer-header2:value2",
                    ));
                    // Get response
                    $responseUsers = curl_exec($ch2);
                    curl_close($ch2); 
                    // Decode
                    $recup = json_decode($responseUsers);
                    $compte = 0;
                    // echo '<pre>';
                    //     print_r($recup->commandes);
                    // echo '</pre>';
                    foreach($recup->commandes as $key=>$val){
                        $compte++;
                        $u = "";
                        $t = "";
                        $code = "";
                        $code_chantier = "";
                        $datetime = 0;
                        $id=0;
                        foreach($val as $k=>$v){
                            // if($k == "client"){
                            //     $code = $v->code;
                            //     if($code == 'AA001'){
                            //         echo 'yes';
                            //     }
                            // }
                            if($k == 'reference'){
                                echo $v.'<br>';
                            }
                        }
                    }
                echo 'modificaton faite!';
            }
        }
        saveCommandNumber($manager);	
	//***********************************get all users************************ */

 ?>
