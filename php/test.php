<?php
$test = json_decode('[["3.900","4.000","S10 - CERC 710 - C","4 + 0 / - -0.1"],["3.900","4.000","S10 - CERC 710 - B","4 + 0 / - -0.1"],["0.000","0.250","S07 - PLTE 450","0 + 0.25 / - 0"],["0.000","0.200","S04 - PLTE 455","0 + 0.2 / - 0"]]');

// echo $test[0][0];

echo number_format(2.512987, 2);
echo "<br>";
echo round(0.027000000000001, 3);


?>