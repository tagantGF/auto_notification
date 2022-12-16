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
            $recup0 = $manager->selectionUnique2('numCommand',array('*'),"marqueur_insert_cmd <> ''");
            $recupAll = $manager->selectionUnique2('numCommand',array('*'),"");
            if(true){
                $nbElmt = 1000;
                $fois = $nbElmt/1000;
                $indicateur = 0;
                $tt = array();
                for($a=0;$a<$fois;$a++){
                    if(count($recup0) > 0){
                        $indice = (int)($recup0[0]->marqueur_insert_cmd);
                        if($indice == 30){
                            $i = ($indice*1000)+1;
                            $table0 = array(
                                'marqueur_insert_cmd'=>"1"
                            );
                            $manager->modifier('numCommand',$table0,"marqueur_insert_cmd='$indice'");
                        }else{
                            $i = ($indice*1000)+1;
                            $ic = $indice+1;
                            $table0 = array(
                                'marqueur_insert_cmd'=>"$ic"
                            );
                            $manager->modifier('numCommand',$table0,"marqueur_insert_cmd='$indice'");
                        }
                    }else{
                        $i = ($indicateur*1000)+1;
                        $table0 = array(
                            'marqueur_insert_cmd'=>"1"
                        );
                        $manager->modifier('numCommand',$table0,"num_cmd=1");
                    }
                    $url = "http://www.quincaillerie-feraud.fr/yzyapi/1.0.0/commandes?curseur=$i&limite=1000"; //AA001 for one user
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
                            if($k == "numero_bl"){
                                $diff = dateDifference($datetime);
                                if($diff < 3*30){
                                    $recupT = $manager->selectionUnique2('numCommand',array('*'),"bl='$v'");
                                    if(count($recupT) < 1){
                                        $table = array(
                                            'bl'=>"$v",
                                            'ncommand'=>$u,
                                            'code_clt'=>"$code",
                                            'code_chantier'=>"$code_chantier"
                                        );
                                        //  echo '<pre>';
                                        //     print_r($table);
                                        // echo '</pre>';
                                        $manager->insertion('numCommand',$table,'');
                                    }
                                }else{
                                    $manager->supprimer('numCommand',"bl='$v'");
                                }
                            }
                            else if($k == "numero"){
                                $u = $v;
                            }
                            else if($k == "client"){
                                $code = $v->code;
                            }else if($k == "chantier"){
                                if($v->code){
                                    $code_chantier = $v->code;
                                }
                            }
                            else if($k == "date_commande"){
                                $datetime = $v;
                            }
                        }
                    }
                    $indicateur++;
                }
                echo 'modificaton faite!';
            }
        }
        saveCommandNumber($manager);	
	//***********************************get all users************************ */

 ?>
