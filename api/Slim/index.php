<?php

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'config.php';
require 'Slim/Slim.php';
require 'fcmpushmessage.php';
use Sigmarest\Config\FCMPushMessage;

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->post('/alerta','alerta'); 
$app->get('/alerta','alerta'); 
$app->post('/login','login'); 
$app->post('/cadastrar','cadastrar'); 
$app->post('/dispositivos','dispositivos'); 
$app->post('/campainhas','campainhas'); 
$app->post('/recuperar_senha','recuperar_senha');
$app->get('/recuperar_senha','recuperar_senha');
$app->post('/resetar_senha','resetar_senha');
$app->get('/resetar_senha','resetar_senha');




$app->run();

function alerta() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $clientDetails = json_decode(file_get_contents("http://ipinfo.io/$ip/json"));

    $ip_request=$clientDetails->ip;
    $hostname_request=$clientDetails->hostname;
    $city_request=$clientDetails->city;
   

    $city_request =mb_convert_encoding($city_request, 'Windows-1252', 'UTF-8');
    $city_request=utf8_encode( $city_request);
    $city_request= tira_Acentos($city_request);

    

    $region_request=$clientDetails->region;
    $loc_request=$clientDetails->loc;
    $org_request=$clientDetails->org;
    $postal_request=$clientDetails->postal;
    $timezone_request=$clientDetails->timezone;
    $country_request=$clientDetails->country;
  
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->post(''); 
    $paramValueGet = $request->get('');

    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
   
    $email_limpo=htmlspecialchars($email_limpo);
    $email_limpo=htmlentities($email_limpo);
 
    $serial=$paramValuePost['serial'];
    $hora=$paramValuePost['hora'];
    if (filter_var($email_limpo, FILTER_VALIDATE_EMAIL)) {
        $continue=true;
    }
    else{
        $continue=false;
    }


    $hora_valida=valid_hour($hora);
  
    if($hora_valida){ $continue=true;}else{ $continue=false;}
    if ($continue){
        try {
            $db = getDB();
            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $login = $stmt1->fetchAll(PDO::FETCH_OBJ);
            $uid=$login[0]->ID;
         
            if(sizeof($login) > 0){
                //Achou registro de usuario, busca os dispositivos
                $sql1 = "SELECT CFM_ID FROM DISPOSITIVOS WHERE USER_ID = :UID";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("UID", $uid,PDO::PARAM_STR);
                $stmt1->execute();
                $dispositivos = $stmt1->fetchAll(PDO::FETCH_OBJ);
                if(sizeof($dispositivos) > 0){echo "achou dispositivo";}else{echo "nao achou dispositivo";}
                //gera o id 
                $ID = microtime(true);
                $ID = str_replace(".", "", $ID);
                // nao existe insere no banco
                $data_cadastro=date('Y-m-d H:i:s');
                $sql1="INSERT INTO LOG_API(ID,DATA,IP,HOSTNAME, CIDADE,LOCALIZACAO,CEP,PAIS,EMAIL,SERIAL,HORA) VALUES (:ID,:DATA,:IP,:HOSTNAME,:CIDADE,:LOCALIZACAO,:CEP,:PAIS,:EMAIL,:SERIAL,:HORA)";
                $stmt1 = $db->prepare($sql1);
                
                $stmt1->bindParam("ID", $ID,PDO::PARAM_STR);
                $stmt1->bindParam("DATA", $data_cadastro,PDO::PARAM_STR);
                $stmt1->bindParam("IP", $ip_request,PDO::PARAM_STR);
                $stmt1->bindParam("HOSTNAME", $hostname_request,PDO::PARAM_STR);
                $stmt1->bindParam("CIDADE", $city_request,PDO::PARAM_STR);
                $stmt1->bindParam("LOCALIZACAO", $loc_request);
                $stmt1->bindParam("CEP", $postal_request,PDO::PARAM_STR);
                $stmt1->bindParam("PAIS", $country_request,PDO::PARAM_STR);
                $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
                $stmt1->bindParam("SERIAL", $serial,PDO::PARAM_STR);
                $stmt1->bindParam("HORA", $hora,PDO::PARAM_STR);
                $stmt1->execute();
                $resultado_envio=enviar($dispositivos, $serial);
              
                echo json_encode(array("status"=> 1, 
                "message"=> "Alerta enviado",
                "serial"=>$serial));
                
            $db = null;
            
            
        }
        }
        catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }

        
    }else{
        echo "ERRO";
    exit();
    }
}

function login(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->post(''); 

    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
    $senha=$paramValuePost['senha'];

    try {
        $db = getDB();

            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $login = $stmt1->fetchAll(PDO::FETCH_OBJ);

            if(sizeof($login) > 0){
                $sql2 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL AND SENHA = :SENHA";
                $stmt2 = $db->prepare($sql2);
                $stmt2->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
                $stmt2->bindParam("SENHA", $senha,PDO::PARAM_STR);
                $stmt2->execute();
                $login = $stmt2->fetchAll(PDO::FETCH_OBJ);
                if(sizeof($login) > 0){
                    echo json_encode(array("status"=> 1, 
                    "message"=> "Usuário encontrado"));                
                }else{
                    echo json_encode(array("status"=> 2, 
                    "message"=> "Senha incorreta"));
                }
            }else{
                echo json_encode(array("status"=> 2, 
                "message"=> "E-mail não cadastrado"));
            }

        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function cadastrar(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->post(''); 
    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
    $senha=$paramValuePost['senha'];
    $nome=$paramValuePost['nome'];
    $telefone=$paramValuePost['telefone'];
    try {
        $db = getDB();

            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $cadData = $stmt1->fetchAll(PDO::FETCH_OBJ);
            $uid=$cadData[0]->ID;
            // nao existe insere no banco
            if(sizeof($cadData) == 0){
                //gera o id 
                $ID = microtime(true);
                $ID = str_replace(".", "", $ID);
                $data_cadastro=date('Y-m-d H:i:s');
                $sql2="INSERT INTO USUARIOS(ID,EMAIL,SENHA,DATA_CADASTRO,NOME,TELEFONE) VALUES (:ID,:EMAIL,:SENHA,:DATA_CADASTRO,:NOME,:TELEFONE)";
                $stmt2 = $db->prepare($sql2);
                
                $stmt2->bindParam("ID", $ID,PDO::PARAM_STR);
                $stmt2->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
                $stmt2->bindParam("SENHA", $senha,PDO::PARAM_STR);
                $stmt2->bindParam("DATA_CADASTRO", $data_cadastro,PDO::PARAM_STR);
                $stmt2->bindParam("NOME", $nome,PDO::PARAM_STR);
                $stmt2->bindParam("TELEFONE", $telefone,PDO::PARAM_STR);
                $stmt2->execute();

                echo json_encode(array("status"=> 1, 
                "message"=> "Usuário cadastrado"));
            }else{

                echo json_encode(array("status"=> 2, 
                "message"=> "Usuário já existe"));
            }
           
        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function dispositivos(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->post(''); 
    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
    $fcm_id=$paramValuePost['fcm_id'];

    try {
        $db = getDB();

            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $data = $stmt1->fetchAll(PDO::FETCH_OBJ);
            $uid=$data[0]->ID;

            $sql2 = "SELECT * FROM DISPOSITIVOS WHERE CFM_ID = :CFM_ID";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam("CFM_ID", $fcm_id,PDO::PARAM_STR);
            $stmt2->execute();
            $data = $stmt2->fetchAll(PDO::FETCH_OBJ);

            // nao existe insere no banco
            if(sizeof($data) == 0){
                //gera o id 
                $ID = microtime(true);
                $ID = str_replace(".", "", $ID);
                $data_cadastro=date('Y-m-d H:i:s');
                $sql3="INSERT INTO DISPOSITIVOS(ID,USER_ID,CFM_ID,DATA_CADASTRO) VALUES (:ID,:USER_ID,:CFM_ID,:DATA_CADASTRO)";
                $stmt3 = $db->prepare($sql3);
                
                $stmt3->bindParam("ID", $ID,PDO::PARAM_STR);
                $stmt3->bindParam("USER_ID", $uid,PDO::PARAM_STR);
                $stmt3->bindParam("CFM_ID", $fcm_id,PDO::PARAM_STR);
                $stmt3->bindParam("DATA_CADASTRO", $data_cadastro,PDO::PARAM_STR);
                $stmt3->execute();
                registra_token($fcm_id);
                echo json_encode(array("status"=> 1, 
                "message"=> "Dispositivo cadastrado"));
            }else{

                echo json_encode(array("status"=> 2, 
                "message"=> "Dispositivo já existe"));
            }
           
        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function campainhas(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->post(''); 
    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
    $serial=$paramValuePost['serial'];
    $nome=$paramValuePost['nome'];
    
    try {
        $db = getDB();

            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $data = $stmt1->fetchAll(PDO::FETCH_OBJ);
            $uid=$data[0]->ID;

            $sql2 = "SELECT * FROM CAMPAINHAS WHERE NOME = :NOME";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam("NOME", $nome,PDO::PARAM_STR);
            $stmt2->execute();
            $data = $stmt2->fetchAll(PDO::FETCH_OBJ);

            // nao existe insere no banco
            if(sizeof($data) == 0){
                //gera o id 
                $ID = microtime(true);
                $ID = str_replace(".", "", $ID);
                $data_cadastro=date('Y-m-d H:i:s');
                $sql3="INSERT INTO CAMPAINHAS(ID,USER_ID,SERIAL,NOME,DATA_CADASTRO) VALUES (:ID,:USER_ID,:SERIAL,:NOME,:DATA_CADASTRO)";
                $stmt3 = $db->prepare($sql3);
                
                $stmt3->bindParam("ID", $ID,PDO::PARAM_STR);
                $stmt3->bindParam("USER_ID", $uid,PDO::PARAM_STR);
                $stmt3->bindParam("SERIAL", $serial,PDO::PARAM_STR);
                $stmt3->bindParam("NOME", $nome,PDO::PARAM_STR);
                $stmt3->bindParam("DATA_CADASTRO", $data_cadastro,PDO::PARAM_STR);
                $stmt3->execute();
                registra_token($fcm_id);
                echo json_encode(array("status"=> 1, 
                "message"=> "Campainha cadastrada"));
            }else{

                echo json_encode(array("status"=> 2, 
                "message"=> "Campainha já existe"));
            }
           
        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function recuperar_senha(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->get(''); 

    // sanitiza as variaveis
    $email_limpo=$paramValuePost['email'];
 
    try {
        $db = getDB();

            $sql1 = "SELECT * FROM USUARIOS WHERE EMAIL = :EMAIL";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("EMAIL", $email_limpo,PDO::PARAM_STR);
            $stmt1->execute();
            $login = $stmt1->fetchAll(PDO::FETCH_OBJ);
            $uid=$login[0]->ID;
            if(sizeof($login) > 0){
               
                // achou usuário, gera token de recuperação e grava na tabela de usuarios, com validade de 1 hora.
                $token = openssl_random_pseudo_bytes(64);
               //Convert the binary data into hexadecimal representation.
                $token = bin2hex($token);
                $data_validade=date('Y-m-d', strtotime(' +1 day'));
              
                echo $token;
              
          
                $sql = "UPDATE USUARIOS  SET TOKEN_RECUPERA_SENHA=:token,VALIDADE_TOKEN=:validade_token  WHERE ID=:uid";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("token", $token,PDO::PARAM_STR);
                $stmt->bindParam("validade_token", $data_validade,PDO::PARAM_STR);
                $stmt->bindParam("uid", $uid,PDO::PARAM_STR);
                $stmt->execute();
                echo json_encode(array("status"=> 1, 
                "message"=> "E-mail enviado"));
                $db = null;
            }else{
                echo json_encode(array("status"=> 2, 
                "message"=> "E-mail não cadastrado"));
            }

        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function resetar_senha(){
    $request = \Slim\Slim::getInstance()->request();
    $paramValuePost = $request->get(''); 

    // sanitiza as variaveis
    $token=$paramValuePost['token'];
    echo strlen($token);
    if(strlen($token)<128){

        echo json_encode(array("status"=> 2, 
                "message"=> "Token inválido"));
    }else{
        echo "token ok";
        try {
            $db = getDB();
    
                $sql1 = "SELECT * FROM USUARIOS WHERE TOKEN_RECUPERA_SENHA = :token";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("token", $token,PDO::PARAM_STR);
                $stmt1->execute();
                $login = $stmt1->fetchAll(PDO::FETCH_OBJ);
                $data_validade=$login[0]->VALIDADE_TOKEN;


                if(sizeof($login) > 0){
                   
                    // achou usuário, token, verifica validade.
                  
                   // $data_validade=date('Y-m-d', strtotime(' +1 day'));
                   $data_agora=date('Y-m-d');
                    echo $data_validade;
                    $data_agora= strtotime($data_agora);
                    $data_validade= strtotime($data_validade);

                   if($data_agora>$data_validade){
                       echo "data maior";
                   }else{
                       echo "data menor";
                   }

                  
              
                   
                    $db = null;
                }else{echo '{"error":{"text":'. $e->getMessage() .'}}';
                
    
            $db = null;
        }
        catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
    }


   
}
function enviar($device, $serial){
    $server_key = 'AAAAxGgJoBE:APA91bGROE0K8MyJYICHS-ZO9-6CDU8EJJn1llBfPlU2LjO5nL_6lZq41dDgjCE1Fs3wj_iH1zXaR9HB7vmrY0DDR0V5jUkszGr8zAdtYVolBp85sKGVnbn4U9LYBlFzYeuKdvon4S1W';
    $msg="Campainha ".$serial." tocou";

    try {

        $db = getDB();
        $sql1 = "SELECT * FROM CAMPAINHAS WHERE SERIAL = :SERIAL";
        $stmt1 = $db->prepare($sql1);
        $stmt1->bindParam("SERIAL", $serial,PDO::PARAM_STR);
        $stmt1->execute();
        $data = $stmt1->fetchAll(PDO::FETCH_OBJ);
    
        $id = $serial;

        if(sizeof($data) > 0) $id = $data->NOME;
    
        foreach ($device as $d){
            $fcmdata = [
                'id' => $id,
                'type' => 'ABC',
                'description' => $msg
            ];
        
            $fields = array(
                'to'  => $d->CFM_ID,
                'data' => $fcmdata,
            );
        
            $headers = array(
                'Content-Type:application/json',
                    'Authorization:key='.$server_key
                );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
        }

        $db = null;
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}
function registra_token($device)
{
    $server_key = 'AAAAxGgJoBE:APA91bGROE0K8MyJYICHS-ZO9-6CDU8EJJn1llBfPlU2LjO5nL_6lZq41dDgjCE1Fs3wj_iH1zXaR9HB7vmrY0DDR0V5jUkszGr8zAdtYVolBp85sKGVnbn4U9LYBlFzYeuKdvon4S1W';

    
      $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
      //$url = "https://iid.googleapis.com/iid/v1:batchRemove";
      $fields['registration_tokens'] = array($device);
      $fields['to'] = '/topics/campainha-web';
      $headers = array(
      'Content-Type:application/json',
          'Authorization:key='.$server_key
      );
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
      $result = curl_exec($ch);
      curl_close($ch);
}


function numbers_only($str, $exception = '')
{
    return preg_replace('#[^0-9'.$exception.']#', '', mb_convert_kana($str, 'n'));
}

function valid_hour($n)
{
    $n = str_replace('：', ':', $n);
    $n = numbers_only($n, ':');

    $arr = explode(':', $n);
    if (count($arr) == 2) {
        $h = intval($arr[0]);
        $m = intval($arr[1]);
        if ($h <= 23 && $h >= 0 && $m <= 59 && $m >= 0) {
            return str_pad($h, 2, '0', STR_PAD_LEFT).':'.str_pad($m, 2, '0', STR_PAD_LEFT);
        }
    }
    return false;
}
function tira_Acentos($string){
$comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
$semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U');
$string= str_replace($comAcentos, $semAcentos, $string);
return $string;
}

function anti_sql($string){
    $string=htmlspecialchars($string);
    $string=htmlentities($string);
    $string=strip_tags($string);
    return $string;
}

?>