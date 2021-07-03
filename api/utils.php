<?php

function url(){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function getByPrimaryKey($cod, $primary_key, $table){
    $db = getDB();

        $sql = "SELECT * FROM $table WHERE $primary_key = :$primary_key";
        $stid = oci_parse($db, $sql);
        oci_bind_by_name($stid, ":$primary_key", $cod);
        ora_execute($stid);

        $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);

    oci_free_statement($stid);
    oci_close($db);

    return $row;
}

function insert($data, $table){
    $db = getDB();

        $keys = array_keys($data);

        $campos = implode(",", $keys);
        $values = implode(",:", $keys);
        $values = ":" . $values;

        $sql = "INSERT INTO $table ($campos) VALUES ($values)"; 

        ora_execute(oci_parse($db, "ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'"));
        $stid = oci_parse($db, $sql);
        foreach ($data as $key => $val) {
            if (is_object($val)) {
                oci_bind_by_name($stid, $key, $data[$key], -1, SQLT_BLOB);
            } else {
                oci_bind_by_name($stid, $key, $data[$key]);
            }
        }
        ora_execute($stid);

    oci_free_statement($stid);
    oci_close($db);
}

function deleteByKey($cod, $key, $table){
    $db = getDB();

        $sql = "DELETE FROM $table WHERE $key = :$key";
        $stid = oci_parse($db, $sql);
        oci_bind_by_name($stid, ":$key", $cod);
        ora_execute($stid);

    oci_free_statement($stid);
    oci_close($db);
}

function generate_OS_CODIGO() {
    $generator_name = 'GNR_OS_CODIGO';

    $db = getDB();

        //Pega próximo código da OS em generator
        $sql = "SELECT $generator_name.NEXTVAL AS VALOR FROM DUAL";
        $stid = oci_parse($db, $sql);
        ora_execute($stid);
        $fa = oci_fetch_assoc($stid);
        $os_codigo = $fa['VALOR'];


        $sql = 'SELECT MOD_CODOS FROM PARAMS_OS';
        $stid = oci_parse($db, $sql);
        ora_execute($stid);
        oci_fetch_all($stid, $params_os, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);

        //verifica mod_codos para criar código da os
        //0 usa generator apenas
        //1 usa negócio + generator + yy(ano)

        if($params_os[0]['MOD_CODOS'] === "1"){
            $sql = 'SELECT CODOS FROM CFGSIGMA';
            $stid = oci_parse($db, $sql);
            ora_execute($stid);
            oci_fetch_all($stid, $cfgsigma, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);

            $os_codigo = $cfgsigma[0]['CODOS'] . "-" . $os_codigo . "-" . date("y");
        }

    oci_free_statement($stid);
    oci_close($db);

    return $os_codigo;
}

function ora_execute($stid){
    if (false === oci_execute($stid)) {
        $error = oci_error($stid);
        $message_err = $error['message'];
        die(json_encode(array("status"=> 500,
            "error"=> $message_err, 
            "message"=> "Erro no servidor")));
    };
}

?>