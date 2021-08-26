<?php
include_once "../../conn/conn.php";

$connection = conectare_DB("thomas");

$produs = mysqli_real_escape_string($connection, $_POST['produs']);
$cav = mysqli_real_escape_string($connection, $_POST['cav']);
$data = mysqli_real_escape_string($connection, $_POST['data']);
$ora = mysqli_real_escape_string($connection, $_POST['ora']);
$id = mysqli_real_escape_string($connection, $_POST['id']);
$indProd = mysqli_real_escape_string($connection, $_POST['ind_prod']);
$valoriMasurate = mysqli_real_escape_string($connection, $_POST['valoriMasurate']);
$valoriSPC = mysqli_real_escape_string($connection, $_POST['valoriSPC']);
$indSPC = mysqli_real_escape_string($connection, $_POST['indSPC']);
$toleranteClient = mysqli_real_escape_string($connection, $_POST['toleranteClient']);

if($produs){
    mysqli_query($connection, "UPDATE produse_trp SET Valori_metrologie='$indProd' WHERE Produs='$produs'");
    mysqli_close($connection);
    // echo $indProd." - ".$valoriSPC;
    echo json_encode(array("status"=>200, "response"=>"Actualizare realizata cu succes!"));
}else{
    echo json_encode(array("status"=>400, "error" => "Nu ati mentionat nici un produs!"));
}


?>