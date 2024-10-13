<?php
include_once "../../conn/connPDO.php";

$connection = conectare_DB("thomas");

$produs = str_replace(" ", "", $_POST['produs']);

$query = $connection->prepare("SELECT Valori_metrologie FROM produse_trp WHERE Produs = :produs");
$stmt->execute(['produs' => $produs]);
$query = $stmt->fetchAll();

$rez = "";

if($stmt->rowCount()){
    foreach($query as $num ){
        $rez = $num['Valori_metrologie'];
    }
}

$rez = explode("|",$rez);

if(sizeof($rez)){
    $check_fav = $rez[0];
    $uncheck_reg = $rez[1];
    $ind_spc = $rez[2];
    echo json_encode(array("check_fav"=>$check_fav, "uncheck_reg"=>$uncheck_reg, "ind_spc"=>$ind_spc));
}else{
    echo json_encode(array("check_fav"=>"", "uncheck_reg"=>"", "ind_spc"=>""));
}

$stmt = null;
$connection = null;