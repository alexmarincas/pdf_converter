
$(".canvas_holder").resizable();

const url = "./docs/pdf.pdf";

let pdfDoc = null,
    pageNum = 1,
    scale = 1.5,
    isPortrait = true,
    textItems = [],
    pageIsRendering = false,
    pageNumIsPending = null,
    controlor = "";

let sirIndexSPC = []
let sirIndexPDF = []
let sirValori = []

const canvas = document.querySelector('#pdf-render'),
      ctx = canvas.getContext('2d');

// Render the page
const renderPage = num => {
    pageIsRendering = true;

    // Get page
    pdfDoc.getPage(num).then(page => {

        let viewport = page.getViewport({scale});
        
        if(viewport.width < viewport.height){
            isPortrait = true;
            scale = 1.5;
        }else{
            isPortrait = false;
            scale = 1;
        }

        // Set scale
        viewport = page.getViewport({scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx,
            viewport
        }

        page.render(renderContext).promise.then(()=>{
            pageIsRendering = false;

            if(pageNumIsPending !==null){
                renderPage(pageNumIsPending);
                pageNumIsPending = null;
            }
        });

        // Output current page
        document.querySelector('#page-num').textContent = num;
    });
};

// VERIFICARE EXCEPTII FORMAT MINIM
const eFaraMin = elem =>{
    let sir = ["COAX", "LOCA", "BT.S", "PLAN", "RECT", "PERP", "PARA", "CYL", "CIRC", "FLTN", "Val."];
    let rezultat = false;
    sir.forEach(ex => {
        if(ex==elem){
            rezultat = true;
        }
    });
    return rezultat;
};

let produs = "";


// Get PDF content as text
const text = ()=>{
    document.querySelector('#output').innerHTML = "";
    let titluNesetat = true;
    let contor = 0;

    textItems = [];

    let x = 1;

    const myLoop = (x) =>{
        
            pdfDoc.getPage(x).then(page => {
                page.getTextContent().then(function (textContent) { 
    
                    // INCEPUT DE PAGINA
                    document.querySelector('#output').innerHTML += `<h3>Pagina ${(textItems.length+1)}</h3>`;
                    
                    textItems.push(textContent.items);
                    if(titluNesetat){

                        $(".fields").val("");

                        let titlu = isPortrait ? textContent.items[6].str.split("-") : textContent.items[4].str.split("-");
                        controlor = isPortrait ? textContent.items[2].str : textContent.items[1].str;
    
                        let ids = titlu.length === 4 ?  ["produs", "cavitate", "data", "ora"] : ["produs", "data", "ora"];

                        
                        titlu.forEach( (el,i) =>{
                            let element = el.replace(/\s/g, "");
                            if(i==0){
                                element = element.replace("_", "/");
                                produs = element;
                            }else{
                                element = element.replace("_", ".")
                                element = element.replace("/", ".")
                                element = element.replace("-", ".")
                                element = element.replace(",", ".")
                            }
                            if(typeof ids[i] !== 'undefined'){
                                document.querySelector(`#${ids[i]}`).value = element;
                            }                        
                        });

                        $("#metrolog").val( isPortrait ? textContent.items[2].str : textContent.items[1].str)
                        $("#masina").val( isPortrait ? textContent.items[18].str : textContent.items[13].str)
                        
                        if( $("#masina").val() === " " ){                            
                            $("#masina").val( textContent.items[19].str)
                        }

                        console.log($("#masina").val())
                                                                        
                        titluNesetat = false;
                    }
                    
                    for(let y=0; y<textContent.items.length; y++){
                        
                        let val = textContent.items[y].str;
                        let nrVal = Number(val);
    
                        if (val===" "){ nrVal = "NaN"; }
                        if (val===""){ nrVal = "NaN"; }

                        // console.log("loop 5 : "+val+" index : "+y);

                        // ELIMINARE INTERPRETARE ERONATA NOMINAL LA y+=5, EX: 
                        // MAX - C1 / REP 
                        // 1 (era interpretat ca si nominal)
                        // IN LOC DE : MAX - C1 / REP1
                        let conditie = true;
                        if( (y+1)<textContent.items.length){
                            let next = textContent.items[y+1].str;
                            
                            if(isNaN(next) && !isNaN(val) || isNaN(next) && isNaN(val)){ 
                                conditie = false;
                                // console.log(`%c${val} %c${textContent.items[y+1].str}`, `color: blue`, `color: red`);
                            }
                        }

                        // CONDITIE E.F.
                        if(y){
                            if(textContent.items[y-1].str==="E.F."){
                                y+=2;
                                conditie = false;
                                // console.log(`%c${textContent.items[y-1].str} | ${val}`, `color: orange`);
                            }
                        }
                        
                        
                        if(conditie){
                            if(!isNaN(nrVal)){                                

                                // console.log(`%c${textContent.items[y-2].str} | ${val}`, `color: orange`);
                                // console.log(`%c${nrVal} | ${y}`, `color: green`);
                                
                                let nominal = nrVal;
                                let masurat =  isPortrait ? Number(textContent.items[y+2].str) : Number(textContent.items[y+2].str);
                                let info_cav =  textContent.items[y-6].str;

                                // console.log(textContent.items[y-4].str, textContent.items[y-6].str, textContent.items[y-7].str, textContent.items[y-8].str)
        
                                let tolMin, tolMax = '';
        
                                try{

                                    let p = textContent.items[y-2].str.replace(/\s/g, "")

                                    // console.log("nominal 0 => pag: "+x+" : "+p+", linia = "+y, isPortrait);
        
                                    if(eFaraMin(p)){
                                        tolMin = 0;
                                        tolMax = Number(textContent.items[y+4].str)
                                        if(tolMax === 0){
                                            // console.log('hopa')
                                            tolMax = Number(textContent.items[y+6].str)
                                        }

                                        if(tolMax === 0){
                                            // console.log('hopa')
                                            tolMax = textContent.items[y+3].str
                                        }
                                    }else{
                                        tolMin = textContent.items[y+4].str
                                        tolMax = textContent.items[y+6].str

                                        if(tolMin === " " || tolMin === ""){
                                            tolMin = textContent.items[y+3].str
                                        }
    
                                        if(tolMax === " " || tolMax === ""){
                                            tolMax = textContent.items[y+5].str
                                        }
                                    }
                                    
                                    tolMin = Number(tolMin)
                                    tolMax = Number(tolMax)

                                    let minim = (nominal+tolMin).toFixed(3);
                                    let maxim = (nominal+tolMax).toFixed(3);

                                    // console.log(`%c${tolMin} | ${tolMax}`, `color: red`);
                                    // console.log(`%c${minim} | ${maxim}`, `color: blue`);
            
                                    let clasa = (masurat>=minim && masurat<=maxim) ? "ok" : "nok";
            
                                    if(!isNaN(minim) && !isNaN(maxim) ){
                                        contor++;
                                        document.querySelector('#output').innerHTML += `<div class='masuratoare'><span class='nrCrt tooltip' data-tooltip='Nr. crt.'>${contor}</span><input type='text' class='index_spc' placeholder='index spc'/><div class="custom_checkbox" title='De afisat clientului, respectiv de inregistrat in SPC'><input type='checkbox' id="id_${contor}" class='checkbox_client' /><label for="id_${contor}"><i class="fas fa-heart"></i></label></div><div class='custom_checkbox' title='Inregistrare valoare masurata in baza de date'><input type='checkbox' id="idn_${contor}" class='checkbox' checked/><label for="idn_${contor}"><i class="fas fa-check-square"></i></label></div><span class='bulina tooltip' data-tooltip='${info_cav}'></span><div class='info'><span class='nominal tooltip' data-tooltip='Nominal'>${nominal}</span> ( <span class='tolminim tooltip' data-tooltip='Tol -'>${tolMin}</span> / <span class='tolmaxim tooltip' data-tooltip='Tol +'>${tolMax}</span> )</div><div class='calcul'><span class='minim tooltip' data-tooltip='Minim'>${minim}</span><input type='text' class='valMasurata ${clasa}' value='${masurat}'><span class='tooltip maxim' data-tooltip='Maxim'>${maxim}</span></div></div>`;
                                    }
                                    // else{
                                    //     console.log("%cs-au amestecat mere cu pere", 'background: #222; color: #bada55');
                                    // }

                                    // y += isPortrait ? 5 : 5;
                                    y+=10

                                }catch{
                                    // $("#get_id_btn").removeClass("inactiv"); 
                                    // $(".loading").removeClass('visible');
                                    // console.log('hopa')
                                    // return false
                                }
                                    
                                                                    
        
                                
                            }
                        }
                    }
    
                    // FINAL DE PAGINA
                    document.querySelector('#output').innerHTML += `<br>`;

                    // console.log(x, pdfDoc.numPages)
                    
                    if(x<pdfDoc.numPages){                  
                        $("#get_id_btn").addClass("inactiv"); 
                        $("#save").addClass("inactiv"); 
                        $("#update").addClass("inactiv"); 
                        x++;
                        myLoop(x);
                    }else{
                        $("#get_id_btn").removeClass("inactiv"); 
                        $("#save").removeClass("inactiv"); 
                        $(".loading").removeClass('visible');
                        // DEBIFARE / BIFARE CASUTE
                        $.post("php/getIndex.php", {produs}, function(data){   


                            // console.log(data)
                            
                            let ind = JSON.parse(data.ind_spc);

                            let contorSpc = 0;

                            JSON.parse(data.uncheck_reg).forEach( (el, i) =>{
                                // console.log(el+" - "+i);
                                $(".checkbox").eq(el).prop("checked", false);
                            });

                            JSON.parse(data.check_fav).forEach( i =>{
                                $(".checkbox_client").eq(i).prop("checked", true);
                                
                                if(typeof ind[contorSpc] === 'undefined'){

                                }else{
                                    $(".index_spc").eq(i).val(ind[contorSpc]);
                                }

                                contorSpc++;
                            });

                            
                        }, "json");
                    }

                });

                

            }); // END PDF PAGE MANIPULATION
              

    }
    
    myLoop(x);

    
        
};

// Check for pages rendering
const queueRenderPage = num =>{
    if(pageIsRendering){
        pageNumIsPending = num;
    }else{
        renderPage(num);        
    }
}

// Show previous page
const showPrevPage = () =>{

    if(pdfDoc!=null){
        if(pageNum <=1){
            return;
        }
        pageNum--;
        queueRenderPage(pageNum);
    }
    
}
// Show next page
const showNextPage = () =>{

    if(pdfDoc!=null){
        if(pageNum >= pdfDoc.numPages){
            return;
        }
        pageNum++;
        queueRenderPage(pageNum);
    }
}

// Show values
const showValues = () =>{
    if(pdfDoc!=null){

        let sirIndexSpc = [];
        let sirCoteClient = [];
        let sirValoriSPC = [];
        let sirUncheck = [];
        let sirValoriDeInregistrat = [];

        let checkbox = document.querySelectorAll(".checkbox");
        let checkbox_client = document.querySelectorAll(".checkbox_client");
        let index_spc = document.querySelectorAll(".index_spc");
        let valori = document.querySelectorAll(".valMasurata");
        let minim = document.querySelectorAll(".minim");
        let tolminim = document.querySelectorAll(".tolminim");
        let nominal = document.querySelectorAll(".nominal");
        let tolmaxim = document.querySelectorAll(".tolmaxim");
        let maxim = document.querySelectorAll(".maxim");
        let descriere = document.querySelectorAll(".bulina");

        let indFav = [];

        let check_index = true;

        checkbox_client.forEach( (c, i) =>{
            if(c.checked){
                sirValoriSPC.push(valori[i].value);

                let t = `${nominal[i].innerText} +${tolmaxim[i].innerText} / ${tolminim[i].innerText}`;
                sirCoteClient.push([minim[i].innerText, maxim[i].innerText, descriere[i].getAttribute('data-tooltip'), t]);
                indFav.push(i);
                
                if( index_spc[i].value === "" ){
                    check_index = false;
                }else{                    
                    sirIndexSpc.push(index_spc[i].value);
                }
                
            }
        });

        if(!check_index){ alertify.error("Nu ai completat indecsii necesari cotelor relevante SPC"); return false; }

        checkbox.forEach( (c, i) =>{
            if(c.checked===false){
                sirUncheck.push(i)
            }else{
                sirValoriDeInregistrat.push(valori[i].value);
            }
        });

        let ind_prod = `${JSON.stringify(indFav)}|${JSON.stringify(sirUncheck)}|${JSON.stringify(sirIndexSpc)}`; // |
        let valoriMasurate = JSON.stringify(sirValoriDeInregistrat);
        let valoriSPC = JSON.stringify(sirValoriSPC);
        let indSPC = JSON.stringify(sirIndexSpc);
        let toleranteClient = JSON.stringify(sirCoteClient);

        let produs = $("#produs").val();
        let cav = $("#cavitate").val();
        let data = $("#data").val();
        let ora = $("#ora").val();
        let id = $("#id_spc").val();
        let metrolog = $("#metrolog").val().replace(/ /g,"");
        let masina = $("#masina").val();
        let obs = $("#observatii").val();

        if( produs === "" ){
            alertify.error("Nu ati completat produsul!")
            return false
        }
        if( cav === "" ){
            alertify.error("Nu ati completat cavitatea!")
            return false
        }
        if( data === "" ){
            alertify.error("Nu ati completat data!")
            return false
        }
        if( ora === "" ){
            alertify.error("Nu ati completat ora!")
            return false
        }
        if( id === "" ){
            alertify.error("Nu ati completat id-ul!")
            return false
        }
        if( metrolog === "" || metrolog.length < 2){
            alertify.error("Nu ati completat numele metrologului!")
            return false
        }

        $.post("php/register.php", {produs, cav, data, ora, id, ind_prod, valoriMasurate, valoriSPC, indSPC, toleranteClient, metrolog, masina, obs}, function(callback){
            if( callback.status === 200 ){
                alertify.alert(callback.response)
                console.log(callback.response)
            }else{
                if(callback.status === 400){
                    alertify.error(callback.error)
                }else{
                    console.log(callback)
                }
            }
        }, 'json');

    }else{
        alertify.error("Nu exista valori de interpretat!");
    }
};

// Button events
document.querySelector('#prev').addEventListener('click', showPrevPage);
document.querySelector('#next').addEventListener('click', showNextPage);
document.querySelector('#update').addEventListener('click', showValues);


// UPLOAD FILE DRAG & DROP
function onFile() {

        var file = document.getElementById('upload').files[0];	

        var formData = new FormData();
        formData.append('fileToUpload', file);				
            
            var ajax = new XMLHttpRequest();				
            ajax.addEventListener('load', completeHandlerUpdate, false);
            ajax.open('POST', 'php/upload.php');
            ajax.send(formData);
}

// COMPLETE HANDLER
function completeHandlerUpdate(event){	

        $("#upload").val('').clone(true);
        $(".area").removeClass("dragging");

        switch(event.target.responseText){
            case 'a' : alertify.error("Nu ati atasat nici un document!"); break;
            case 'b' : alertify.error("Documentul atasat nu este de tip .pdf!"); break;
            case 'd' : alertify.error("A aparut o eroare, iar fisierul nu a fost uploadat!"); break;
            default : getDocument();
        }
        
}

const reset = () =>{
    $("#produs").val("")
    $("#cavitate").val("")
    $("#data").val("")
    $("#ora").val("")
    $("#id_spc").val("")
    $("#masina").val("")
    $("#get_id_btn").addClass("inactiv")
    $("#update").addClass("inactiv")
};



// GET DOCUMENT
const getDocument = () =>{
    
    $(".loading").addClass('visible');
    reset()
    
    pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
        pdfDoc = pdfDoc_;

        pageNum = 1;

        if(pdfDoc.numPages > 1){
            $("#meniu button").addClass("visible");
        }else{
            $("#meniu button").removeClass("visible");
        }
        
        document.querySelector('#pages').textContent = pdfDoc.numPages;

        $("#meniu p").addClass("visible");

        renderPage(pageNum);

        text();
        
    })
    .catch(err =>{
        // Display error
        // const div = document.createElement('div');
        // div.className = 'error';
        // div.appendChild(document.createTextNode(err.message));
        // document.querySelector('body').insertBefore(div, canvas);

        console.log(err.message);

    });
};

let upload = document.getElementById("upload");

upload.addEventListener('dragenter', function (e) {
    upload.parentNode.className = 'area dragging';
}, false);

upload.addEventListener('dragleave', function (e) {
    upload.parentNode.className = 'area';
}, false);

upload.addEventListener('dragdrop', function (e) {
    onFile();
}, false);

upload.addEventListener('change', function (e) {
    onFile();
}, false);

// Fetch measurement id
$("#get_id_btn").on("click", function(){
    // console.log('click')

    const produs = $("#produs").val()
    const cavitate = $("#cavitate").val()
    const data = $("#data").val()
    const ora = $("#ora").val()
    
    $.post('php/getId.php',{produs, cavitate, data, ora}, function(response){
        if(response.status===200){
            $("#id_spc").val(response.id);            
            $("#update").removeClass("inactiv");            
        }else{            
            console.log(response)
        }
        
    }, "json")
}) 

$("#id_spc").on("keyup", function(){
    if( $(this).val() ){
        $("#update").removeClass("inactiv");                    
    }else{
        $("#update").addClass("inactiv");            
    }
})

// SAVE TEMPLATE
$("#save").on("click", function(){
    // console.log('click')

    let sirIndexSpc = [];
    let indFav = [];
    let sirUncheck = [];

    const produs = $("#produs").val()
    const checkbox = document.querySelectorAll(".checkbox");
    const checkbox_client = document.querySelectorAll(".checkbox_client");
    const index_spc = document.querySelectorAll(".index_spc");

    let check_index = true;

    checkbox_client.forEach( (c, i) =>{
        if(c.checked){
            indFav.push(i);                
            if( index_spc[i].value === "" ){
                check_index = false;
            }else{                    
                sirIndexSpc.push(index_spc[i].value);
            }                
        }
    });

    if(!produs){ alertify.error("Nu ai completat produsul"); return false; }
    if(!check_index){ alertify.error("Nu ai completat indecsii necesari cotelor relevante SPC"); return false; }

    checkbox.forEach( (c, i) =>{
        if(c.checked===false){
            sirUncheck.push(i)
        }
    });


    let ind_prod = `${JSON.stringify(indFav)}|${JSON.stringify(sirUncheck)}|${JSON.stringify(sirIndexSpc)}`;
    
    $.post('php/saveTemplate.php',{produs, ind_prod}, function(response){
        if(response.status===200){
            alertify.success(response.msg)        
        }else{
            alertify.error(response.msg)
        }
    }, "json")
}) 