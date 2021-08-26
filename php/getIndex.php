<?php
include_once "../../conn/conn.php";

$connection = conectare_DB("thomas");

$produs = mysqli_real_escape_string($connection, str_replace(" ", "", $_POST['produs']));

$query = mysqli_query($connection, "SELECT Valori_metrologie FROM produse_trp WHERE Produs = '$produs'");

$rez = "";

while($num = mysqli_fetch_assoc($query)){
    $rez = $num['Valori_metrologie'];
}

mysqli_close($connection);

$rez = explode("|",$rez);

if(sizeof($rez)){
    $check_fav = $rez[0];
    $uncheck_reg = $rez[1];
    $ind_spc = $rez[2];
    echo json_encode(array("check_fav"=>$check_fav, "uncheck_reg"=>$uncheck_reg, "ind_spc"=>$ind_spc));
}else{
    echo json_encode(array("check_fav"=>"", "uncheck_reg"=>"", "ind_spc"=>""));
}

?>