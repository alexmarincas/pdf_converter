<?php
include_once "../../conn/connPDO.php";

function getData($str){
    list($zi, $luna, $an) = explode(".", $str);
    return "$an-$luna-$zi";
}

$connection = conectare_DB('spc');

$produs = $_POST['produs'];

$cav = str_ireplace("c", "", $_POST['cavitate']);
list($cavitate) = explode(".", $cav);

$data = getData( $_POST['data'] );

$ora = str_replace(".", ":", $_POST['ora']);

$stmt = $connection->prepare("SELECT id FROM masuratori WHERE Produs = :produs AND Cavitate = :cavitate AND Date_time LIKE :data_time AND Ora_injectarii = :ora AND Sters='0' ORDER BY id DESC LIMIT 1");
$stmt->execute(['produs' => $produs, 'cavitate' => $cavitate, 'data_time' => $data.'%', 'ora' => $ora ]);
$query = $stmt->fetchAll();

if($stmt->rowCount()){
    foreach($query as $num ){
        $id = $num['id'];
    }
    echo json_encode(array('status'=>200, 'id'=>$id));
}else{
    echo json_encode(array("status"=>204, "error"=>"No content!", "data" => array($produs, $cavitate, $data, $ora) ));
}

$stmt = null;
$connection = null;


?>