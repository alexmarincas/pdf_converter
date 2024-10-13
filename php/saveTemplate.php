<?php
include_once "../../conn/connPDO.php";
$connection = conectare_DB("thomas");

$produs = $_POST['produs'];
$indProd = $_POST['ind_prod'];

if($produs){
    $stmt = $connection->prepare("UPDATE produse_trp SET Valori_metrologie = :indProd WHERE Produs = :produs");
    $stmt->execute(["ind_prod"=>$indProd, "produs"=>$produs]);
        
    echo json_encode(array("status"=> 200, "msg"=>"Template salvat cu succes!"));
}else{    
    echo json_encode(array("status"=> 203, "msg"=>"Nu ati mentionat produsul!"));
}

$stmt = null;
$connection = null;