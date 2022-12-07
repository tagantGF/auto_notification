<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
//require 'vendor/autoload.php';
$statut = $_GET['statut'];
$leMail = $_GET['mail'];
$numCommand = $_GET['numCommand'];
$libCmd = '';
if(!in_array($numCommand,[null,""])){
    $libCmd = "N° $numCommand";
}
//$leMail = "falahometest@gmail.com";

require 'PHPMailer/src/Exception.php';
 
/* Classe-PHPMailer */
require 'PHPMailer/src/PHPMailer.php';
/* Classe SMTP nécessaire pour établir la connexion avec un serveur SMTP */
require 'PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions

$tab = [
    "Votre colis est en cours de livraison"=>"En cours de livraison",
    "Livré conforme"=>"Colis livré",
    "Communication conforme - Pris en compte"=>"Colis enregistré chez le transporteur - En attente de ramassage",
    "Arrivage conforme"=>"Colis arrivé au centre de tri",
    "Position soldée - Retour/réexpédition sur instruction"=>"Colis en souffrance - Le colis n'a pas été livré.",
    "Non livré - Complément d'adresse"=>"Livraison retardée - Il manque une information (n° téléphone manquant ou adresse incomplète)"
];
foreach($tab as $key=>$val){
    if($key == $statut){
        $statut = $val;
        break;
    }
}
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();    //Send using SMTP
    $mail->SMTPAuth = true;   
    //Server settings
    $mail->SMTPDebug = 0;  //SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->Host = 'smtp.office365.com';                     //Set the SMTP server to send through
    //                                //Enable SMTP authentication
    $mail->Username = 'no-reply@groupe-feraud.com';                     //SMTP username
    $mail->Password = 'S@p@ig57';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
    $mail->Port = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
   
    //Recipients
    $mail->setFrom('no-reply@groupe-feraud.com', 'Suivi de commande Feraud');
    $mail->addAddress($leMail, 'Client');     //Add a recipient
    //$mail->addAddress('j.caline@groupe-feraud.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('Suivi-livraison@groupe-feraud.com');
    $mail->addBCC('y.bijaoui@groupe-feraud.com');

    //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = "Statut de votre commande $libCmd";
    $mail->Body = "Bonjour Madame, Monsieur,<br><br>
                    Ce mail est envoyé automatiquement pour vous avertir du statut de livraison, vous serez notifié à chaque changement d’état.<br><br>
                    Le statut de votre commande $libCmd est : <b>$statut</b><br><br>
                    Le transporteur du colis est : <b>Euro Coop Express</b><br><br>
                    Ne pas faire répondre, en cas de problème ou pour toutes questions, veuillez nous écrire à <a href='mailto:adv@groupe-feraud.com'>adv@groupe-feraud.com</a><br><br>
                    L’équipe Feraud vous souhaite une bonne journée.";
                    
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
   
    $mail->CharSet = 'UTF-8';
	$mail->Encoding = 'base64';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}