<?php

class XmlController {

    public static function upload_xml($file){

        try{
            $nome_arquivo = "xml-" . uniqid() . ".xml";
            $path = dirname(__FILE__) . "/../xmls/" . $nome_arquivo;

            $xml = simplexml_load_file($file);

            //dados da nota
            $data['CNPJ'] = utf8_decode($xml->NFe->infNFe->emit->CNPJ);
            $data['nProt'] = utf8_decode($xml->protNFe->infProt->nProt);
            $data['nNF'] = utf8_decode($xml->NFe->infNFe->ide->nNF);
            $data['dhEmi'] = utf8_decode($xml->NFe->infNFe->ide->dhEmi);
            $data['vNF'] = utf8_decode($xml->NFe->infNFe->total->ICMSTot->vNF);

            //dados destinatario
            $data['destCPF'] = utf8_decode($xml->NFe->infNFe->dest->CPF);
            $data['destCNPJ'] = utf8_decode($xml->NFe->infNFe->dest->CNPJ);
            $data['xNome'] = utf8_decode($xml->NFe->infNFe->dest->xNome);
            $data['xLgr'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->xLgr);
            $data['nro'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->nro);
            $data['xCpl'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->xCpl);
            $data['xBairro'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->xBairro);
            $data['cMun'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->cMun);
            $data['xMun'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->xMun);
            $data['UF'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->UF);
            $data['CEP'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->CEP);
            $data['cPais'] = utf8_decode($xml->NFe->infNFe->dest->enderDest->cPais);
            $data['IE'] = utf8_decode($xml->NFe->infNFe->dest->IE);
            $data['indIEDest'] = utf8_decode($xml->NFe->infNFe->dest->indIEDest);
            $data['email'] = utf8_decode($xml->NFe->infNFe->dest->email);
            $data['fone'] = utf8_decode($xml->NFe->infNFe->dest->fone);
            $data['nomeXml'] = $nome_arquivo;

            if($data['CNPJ'] != "09066241000884")
                die(json_encode(array("status"=> 3, 
                    "message"=> "CNPJ diferente de 09066241000884")));

            if(empty($data['nProt']))
                die(json_encode(array("status"=> 4, 
                    "message"=> "Nota não possui protocolo de autorização preenchido")));

            if(move_uploaded_file($_FILES['file']['tmp_name'], $path)){
                $conn = getConn();

                    $keys = array_keys($data);

                    $campos = implode(",", $keys);
                    $values = implode(",:", $keys);
                    $values = ":" . $values;

                    $sql = "INSERT INTO xml ($campos) VALUES ($values)"; 

                    $stid = $conn->prepare($sql);

                    foreach ($data as $key => $val) {
                        $stid->bindParam($key, $data[$key]);
                    }

                    $stid->execute();

                    die(json_encode(array("status"=> 1, 
                        "data" => $data,
                        "message"=> "Upload realizado com sucesso")));

                $conn = null;

            }else{
                die(json_encode(array("status"=> 5, 
                    "message"=> "Não foi possível fazer o upload do xml")));
            }
        }
        catch (PDOException $e){
            $error = '{"error":{"text":'. $e->getMessage() .'}}';
            die(json_encode(array("status"=> 2,
                "error"=>$error, 
                "message"=> "Erro no servidor"))); 
        }
    }
}
?>