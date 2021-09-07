<?php
include_once "../../conn/conn.php";
$connection = conectare_DB("thomas");

$produs = mysqli_real_escape_string($connection, $_POST['produs']);
$indProd = mysqli_real_escape_string($connection, $_POST['ind_prod']);

if($produs){

    mysqli_query($connection, "UPDATE produse_trp SET Valori_metrologie='$indProd' WHERE Produs='$produs'");
        
    echo json_encode(array("status"=> 200, "msg"=>"Template salvat cu succes!"));
}else{    
    echo json_encode(array("status"=> 203, "msg"=>"Nu ati mentionat produsul!"));
}

mysqli_close($connection);
?>