<?php
include_once "../../conn/conn.php";

$connection = conectare_DB("thomas");
$connectionSPC = conectare_DB("spc");
$connectionSPCmetrologie = conectare_DB("spc_masuratori");

// GET THE NAME BASED ON THE STAMP CODE
function stampToName($stampile){
    $pdo = conectare_DB('productie');
    $arr = explode(",",str_replace(" ","",$stampile));
    $persoane = array();

    for($x=0; $x<sizeof($arr); $x++){
        $stmt = mysqli_query($pdo, "SELECT Nume, Prenume FROM personal WHERE Stampila = '$arr[$x]' OR Stampila_veche = '$arr[$x]' ");
        if( mysqli_num_rows($stmt) ){                    
            while($num = mysqli_fetch_assoc($stmt) ){
                $numeIntreg = $num['Nume']." ".$num['Prenume'];
                array_push($persoane, $numeIntreg);
            }
        }
    }

    mysqli_close($pdo);

    if(sizeof($persoane)){
        return implode(", ", $persoane);
    }else{
        return $stampile;
    }

}

// GET PROJECT RESPONSIBLE INFO
function getResponsibleEmail($produs){
    $conn = conectare_DB('thomas');
    $query = mysqli_query($conn, "SELECT Responsabil, Email FROM proiecte WHERE proiecte.Proiect = (SELECT Proiect FROM produse_trp WHERE Produs = '$produs')");
    $responsabil = '';
    $email = '';
    while( $num = mysqli_fetch_assoc($query) ){
        $responsabil = $num['Responsabil'];
        $email = $num['Email'];
    }

    mysqli_close($conn);
    
    return array($responsabil, $email);
}

// CONVERT DATA
function convertData($data){
    $luni = array("Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie");
    $arr = explode("-", $data);                            
    return sizeof($arr) === 3 ? intval($arr[2])." ".$luni[ intval($arr[1])-1 ]." ".$arr[0] : $data;
}

function getLuna($data){
    $sir = explode("-", $data);
    $luni = array("Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie");
    $m = intval($sir[1])-1;                       
    return $luni[$m];
}

function formatData($data){    
    $arr = explode(".", $data);                            
    return sizeof($arr) === 3 ? $arr[2]."-".$arr[1]."-".$arr[0] : $data;
}

// VARIABLES
$produs = mysqli_real_escape_string($connection, $_POST['produs']);
$cavitate = mysqli_real_escape_string($connection, $_POST['cav']);
$data_productiei = formatData( mysqli_real_escape_string($connection, $_POST['data']) );
$ora = str_replace(".", ":", mysqli_real_escape_string($connection, $_POST['ora']) );
$id = mysqli_real_escape_string($connection, $_POST['id']);
$indProd = mysqli_real_escape_string($connection, $_POST['ind_prod']);
$controlor_metrolog = mysqli_real_escape_string($connection, $_POST['metrolog']);
$obs = mysqli_real_escape_string($connection, $_POST['obs']);
$masina = $_POST['masina'];
$luna = getLuna($data_productiei);

if($masina === "QC_74 - DEA Performance"){
    $masina = 1;
}else{
    $masina = 0;
}

if(stripos("&", $cavitate)===false){

    if($produs && $id){

        $valoriMasurate = $_POST['valoriMasurate'];
        $valoriSPC = $_POST['valoriSPC'];
        $indSPC = $_POST['indSPC'];
        $toleranteClient = $_POST['toleranteClient'];

        $query_produs = mysqli_query($connection, "SELECT Cavitati, Timp_masurare FROM produse_trp WHERE Produs='$produs'");

        $cavitati = 0;
        $timp_masurare = 0;

        while($num = mysqli_fetch_assoc($query_produs)){
            $cavitati = $num['Cavitati'];
            $timp_masurare = $num['Timp_masurare'];
        }

        mysqli_query($connection, "UPDATE produse_trp SET Valori_metrologie='$indProd' WHERE Produs='$produs'");
        mysqli_close($connection);
        
        // UPDATE SPC BASED ON THE ID
        // READ 
        $get_spc_string = mysqli_query($connectionSPC, "SELECT Masuratori, OperatorQA, ObservatiiQA FROM masuratori WHERE id='$id'");
        if( mysqli_num_rows( $get_spc_string ) ){
            while($num = mysqli_fetch_assoc($get_spc_string)){
                $masuratori = $num['Masuratori'];
                $controlor = $num['OperatorQA'];
                $observatiiQA = $num['ObservatiiQA'];
            }

            // REPLACE VALUES
            $sir_masuratori = explode("%", $masuratori);
            $sirIndexSPC = json_decode($indSPC);
            $sirValoriSPC = json_decode($valoriSPC);
            $sirToleranteClient = json_decode($toleranteClient);

            $sample = 0;
            $mostra = 1;

            $infoCav = str_ireplace("c", "", $cavitate);
            
            if(stripos("+", $infoCav)){
                $cav = 1;
            }else{
                $cav = $infoCav;

                if(stripos($infoCav, ".")){
                    $sir = explode(".", $infoCav);
                    $cav = $sir[0];
                    $mostra = $sir[1];
                    $sample = $mostra - 1;
                }
            }

            $email_body = "";

            // CHECK IF IN THE REGISTERD ARRAY OF VALUES EXISTS THE LARGEST INDEX WHICH MUST BE UPDATED, OTHERWISE INCREASE THE LENGTH OF THE ARRAY
            $greatestInd = max($sirIndexSPC);

            if($greatestInd > sizeof($sir_masuratori)-1){
                $a = $greatestInd - sizeof($sir_masuratori);
                for($x=0; $x<$a; $x++){
                    array_push($sir_masuratori, "N/A,N/A,N/A,N/A,N/A");
                }
            }

            for($x=0; $x<sizeof($sirIndexSPC); $x++){
                $i = $sirIndexSPC[$x];
                
                $linie = explode(",", $sir_masuratori[$i]);
                

                $val = $sirValoriSPC[$x];
                $linie[$sample] = $val;

                $sir_masuratori[$i] = implode(",", $linie);

                // COMPARE VALUES AGAINST LIMITS AND BUILD EMAIL BODY IF NECESSARY
                $min = $sirToleranteClient[$x][0];
                $max = $sirToleranteClient[$x][1];
                $descriere = $sirToleranteClient[$x][2];
                $tolerante = $sirToleranteClient[$x][3];

                if($val > $max || $val < $min){
                    $bigger = true;

                    if($val > $max){
                        $dif = $val - $max;
                    }else{
                        $dif = $min - $val;
                        $bigger = false;
                    }

                    $email_body .= "<tr>";
                        $email_body .= "<td class='big'>".$descriere."<br>".$tolerante."<br>( ".$min." ... ".$max." )</td>";
                        $email_body .= "<td>".($mostra)."</td>";
                        $email_body .= $bigger ? "<td></td>" : "<td class='red'>".round($dif, 3)."</td>";
                        $email_body .= "<td class='med'>".$val."</td>";
                        $email_body .= $bigger ? "<td class='red'>".round($dif, 3)."</td>" : "<td></td>";
                    $email_body .= "</tr>";
                }
            }

            $sir_masuratori_string = implode("%", $sir_masuratori);
            
            if(stripos($controlor, $controlor_metrolog) === false){
                $controlor.=",".$controlor_metrolog;
            }

            if($obs){
                if($observatiiQA){
                    $observatiiQA.="; ".$obs;
                }else{
                    $observatiiQA = $obs;
                }
            }

            // UPDATE
            mysqli_query($connectionSPC, "UPDATE masuratori SET Masuratori='$sir_masuratori_string', OperatorQA='$controlor', ObservatiiQA='$observatiiQA' WHERE id='$id'");
            if($email_body !== ""){
                mysqli_query($connectionSPC, "UPDATE masuratori SET NOK='1' WHERE id='$id'");
            }
            mysqli_close($connectionSPC);

            $nume_controlor_metrolog = stampToName( $controlor_metrolog );

            // CHECK IF EMAIL BODY,  IF YES SEND EMAIL
            if($email_body !== ""){
                                
                require '../../PHPMailer/src/ConnectSettings.php';
                
                $body  = "<style>.valori th{background-color:#dadada;width:80px;border:1px solid #999;} .valori .big{width:200px;} .valori td{border:1px solid #999; width:80px;} .valori .med{width:110px;} .red{background-color:#ff9090;}#logo{display:block;height:70px;width:auto;}";
                $body .= ".info th{text-align: left;border:1px solid #999;padding:0 10px;background-color:#dadada;font-weight:normal;} .info td{padding-left:10px;margin-left:10px;text-align:left;border-bottom:1px solid #999;}";
                $body .= "</style>";
                
                $body .= "<h3>Valori SPC ".$produs.",</h3>";
                $body .= "<br>";
                $body .= "<table class='valori' style='text-align: center'>";
                $body .= "<tr><th class='big'>Operație</th><th class>Mostra</th><th>-</th><th class='med'>Valoare</th><th>+</th></tr>";
                $body .= $email_body;
                $body .= "</table>";
                $body .= "<br>";
                $body .= "<table class='info' style='text-align: center'>";
                $body .= "<tr><th>Inginer metrolog</th><td>".$nume_controlor_metrolog."</td></tr>";                                                        
                $body .= "<tr><th>Data injectării</th><td>".convertData( $data_productiei )."</td></tr>";
                $body .= "<tr><th>Ora injectării</th><td>".$ora."</td></tr>";
                if($obs){
                    $body .= "<tr><th>Observații</th><td>".$obs."</td></tr>";
                }
                $body .= "<tr><th>ID înregistrare</th><td>".$id."</td></tr>";
                $body .= "</table>";
                
                $body .= "<br><img src='cid:image_cid' id='logo' alt='Logo Thomas-Tontec'/>";
                
                $mail->setFrom("spc_metrologie@thomas-tontec.com", 'Alerte SPC metrologie');
                
                $mail->addCustomHeader('MIME-Version', '1.0');
                $mail->addCustomHeader('Content-type', 'text/html');
                
                $infoEmail = getResponsibleEmail($produs);
                if($infoEmail[1]){
                    $mail->addAddress($infoEmail[1], $infoEmail[0]);
                    $mail->addCC('Mirel.Seling@thomas-tontec.com', 'Mirel Seling');
                }else{
                    $mail->addAddress('Mirel.Seling@thomas-tontec.com', 'Mirel Seling');
                }

                $mail->addCC('tudor.petrescu@thomas-tontec.com', 'Tudor Petrescu');
                $mail->addCC('Catalin.Bogdan@thomas-tontec.com', 'Catalin Bogdan');
                $mail->addCC('Claudia.Duma@thomas-tontec.com', 'Claudia Duma');
                $mail->addCC('Claudiu.bumbuc@thomas-tontec.com', 'Claudiu Robert Bumbuc');
                $mail->addCC('Cosmin.SZARVADI@thomas-tontec.com', 'Cosmin Szarvadi');
                // $mail->addCC('Cristian.Abalanoaei@thomas-tontec.com', 'Cristian Abalanoaei');
                $mail->addCC('Csaba.Solomon@thomas-tontec.com', 'Csaba Solomon');
                // $mail->addCC('daniela.suteu@thomas-tontec.com', 'Daniela Suteu');
                $mail->addCC('diana.gingioveanu@thomas-tontec.com', 'Diana Gingioveanu');
                $mail->addCC('diana.harsa@thomas-tontec.com', 'Diana Harsa');
                // $mail->addCC('dorina.hort@thomas-tontec.com', 'Dorina Hort');
                $mail->addCC('eniko.nemethi@thomas-tontec.com', 'Eniko Nemethi');
                $mail->addCC('Florin.Hociung@thomas-tontec.com', 'Florin Hociung');
                // $mail->addCC('Ioana.Codarcea@thomas-tontec.com', 'Ioana Codarcea');
                $mail->addCC('ioana.fizesan@thomas-tontec.com', 'Ioana Fizesan');
                $mail->addCC('Ion.Rata@thomas-tontec.com', 'Ion Rata');
                $mail->addCC('ionut.suciu@thomas-tontec.com', 'Ionut Iulian Suciu');
                $mail->addCC('cosmin.ababei@thomas-tontec.com', 'Cosmin Ababei');
                $mail->addCC('lacrima.cristina.jascau@thomas-tontec.com', 'Cristina Jascau');
                // $mail->addCC('Laura.Lapustea@thomas-tontec.com', 'Laura Lapustea');
                // $mail->addCC('Loredana.Duma@thomas-tontec.com', 'Loredana Duma');
                // $mail->addCC('parschiva.iavorenciuc@thomas-tontec.com', 'Paraschiva Iavorenciuc');
                $mail->addCC('paul.palincas@thomas-tontec.com', 'Paul Palincas');
                $mail->addCC('raul.lerint@thomas-tontec.com', 'Raul Lerint');
                $mail->addCC('Sanda.Abrudan@thomas-tontec.com', 'Sanda Abrudan');
                // $mail->addCC('stefano.gerardi@thomas-tontec.com', 'Stefano Gerardi');
                $mail->addCC('tunde.halsz@thomas-tontec.com', 'Tunde Halasz');
                // $mail->addCC('alex.marincas@thomas-tontec.com', 'Alexandru Marincas');
                
                $mail->isHTML(true);
                
                $mail->Subject = "Cote NOK metrologie: ".$produs.", cavitatea ".$cav;
                $mail->addEmbeddedImage('../../media/poze/thomas-tontec.png', 'image_cid');
                $mail->Body = $body;
                
                if(!$mail->send()){
                    $response = 'Actualizare realizata cu succes. Eroare la trimiterea email-ului...';
                }else{
                    $response = 'Actualizare realizata cu succes. Email trimis!';
                }
                
            }else{
                $response = 'Actualizare realizata cu succes!';
            }
            
            // STORE ALL VALUES INTO spc_masuratori DB - a new column must be created
            mysqli_query($connectionSPCmetrologie, "INSERT INTO masuratori (Produs, Cavitati, Data_productiei, Ora_injectarii, Luna, Stadiu, Masina, Pentru, Nume, Data_finalizarii, Timp_masurare, Program, SPC, Valori_masurate) VALUES ('$produs', '1', '$data_productiei', '$ora', '$luna', '2', '$masina', '0', '$nume_controlor_metrolog', NOW(), '$timp_masurare', '0', '1', '$valoriMasurate' )");

            mysqli_close($connectionSPCmetrologie);

            // $response = $luna;
            // $response = $data_productiei;
            // $response = $controlor;
            // $response = $mostra;
            // $response = $sample;
            // $response = $infoCav;
            // $response = $responsabil." - ".$email; 
            // $response = $masuratori." - after - ".$sir_masuratori_string;

            echo json_encode(array("status"=>200, "response"=>$response));
            
        }else{
            mysqli_close($connectionSPC);
            echo json_encode(array("status"=>400, "error" => "ID-ul mentionat nu exista!"));
        }

    }else{
        echo json_encode(array("status"=>400, "error" => "Nu ati mentionat nici un produs!"));
    }
}else{
    echo json_encode(array("status"=>400, "error" => "Raportul trebuie sa reprezinte o singura cavitate!"));
}

?>