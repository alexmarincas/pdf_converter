<?php
$test = json_decode('[["3.900","4.000","S10 - CERC 710 - C","4 + 0 / - -0.1"],["3.900","4.000","S10 - CERC 710 - B","4 + 0 / - -0.1"],["0.000","0.250","S07 - PLTE 450","0 + 0.25 / - 0"],["0.000","0.200","S04 - PLTE 455","0 + 0.2 / - 0"]]');

// echo $test[0][0];

// echo number_format(2.512987, 2);
// echo "<br>";
// echo round(0.027000000000001, 3);

$sirIndexSPC = array('7', '10', '8');
echo 'Index SPC array (blue hearts): ';
echo "<br>";
var_dump($sirIndexSPC);
echo "<br>";
echo "<br>";

$greatestInd = max($sirIndexSPC);
echo "Largest requested index to be updated: ".$greatestInd."<br>";
echo "<br>";

$sir_masuratori = array('153,222,1215,122,2122', '2,3,4,5,6');
echo 'Demo "registered" array: ';
echo "<br>";
var_dump($sir_masuratori);
echo "<br>";
echo "<br>";

if($greatestInd > sizeof($sir_masuratori)-1){
    $a = $greatestInd - sizeof($sir_masuratori);
    echo "ADD ".$a." more lines<br>";
    for($x=0; $x<=$a; $x++){
        array_push($sir_masuratori, "N/A,N/A,N/A,N/A,$x");
    }
}

echo "<br>";
var_dump($sir_masuratori);
echo "<br>";
echo "<br>";
echo "Last line from the updated array: ".$sir_masuratori[$greatestInd];
echo "<br>";
echo "<br>";
echo "Size of the array after modification: ".sizeof($sir_masuratori);


?>