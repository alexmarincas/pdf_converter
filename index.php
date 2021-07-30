<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Converter</title>
    <link rel="shortcut icon" href="../Media/poze/sigla trp.ico"/>
    <link rel="stylesheet" href="css/alertify.css">
    <link rel='stylesheet' type='text/css' href='css/themes/default.min.css'/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/js/all.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- <style>
        @media print{@page {size: landscape}}
    </style> -->
</head>
<body>

<div id="container">
    <div id="meniu">
        <button id="prev">Prev</button>
        <p><span id="page-num"></span> / <span id="pages"></span></p>
        <button id="next">Next</button>
    </div>
    <div class="titlu_holder">
        <input type="text" class="fields" id="produs" title="Denumire produs" placeholder="Denumire produs">
        <input type="text" class="fields" id="cavitate" title="Cavitate" placeholder="Cavitate">
        <input type="text" class="fields" id="data" title="Data" placeholder="Data">
        <input type="text" class="fields" id="ora" title="Ora" placeholder="Ora">
        <input type="text" class="fields" id="injectare" title="Injectare" placeholder="Injectare">
        <button id="save" class="inactiv">Salveaza</button>
    </div>
    <div class="canvas_holder" class="ui-widget-content">
        <canvas id="pdf-render"></canvas>
    </div>    
    <div id="output" class="ui-widget-content"></div>
    <div class="area">
        <i class="fas fa-upload"></i>
        <input type="file" id="upload" />
    </div>
</div>


    <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="js/alertify.min.js"></script>
    <script src="js/index.js"></script>
</body>
</html>