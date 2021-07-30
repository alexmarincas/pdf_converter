<?php
include_once "../../conn/conn.php";

$connection = conectare_DB("thomas");

$produs = mysqli_real_escape_string($connection, $_POST['produs']);
$cav = mysqli_real_escape_string($connection, $_POST['cav']);
$data = mysqli_real_escape_string($connection, $_POST['data']);
$ora = mysqli_real_escape_string($connection, $_POST['ora']);
$injectare = mysqli_real_escape_string($connection, $_POST['injectare']);
$indProd = mysqli_real_escape_string($connection, $_POST['ind_prod']);
$valoriMasurate = mysqli_real_escape_string($connection, $_POST['valoriMasurate']);

if($produs){
    mysqli_query($connection, "UPDATE produse_trp SET Valori_metrologie='$indProd' WHERE Produs='$produs'");
    mysqli_close($connection);
    echo $indProd;
}else{
    echo "Nu ati mentionat nici un produs!";
}


?>