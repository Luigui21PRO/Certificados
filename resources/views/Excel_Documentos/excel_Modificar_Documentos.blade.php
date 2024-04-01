@extends('layout_especialista/cuerpo')
@section('html')

<script src="https://bossanova.uk/jspreadsheet/v3/jexcel.js"></script>
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v3/jexcel.css" type="text/css" />
<script src="https://jsuites.net/v3/jsuites.js"></script>
<link rel="stylesheet" href="https://jsuites.net/v3/jsuites.css" type="text/css" />

<div class="main-card mb-12 card">
<div class="card-body"><h5 class="card-title" style="font-size:20px;"><b>DOCUMENTOS</b>
</h5>
<div class="position-relative form-group">
          
          <!--return false;  method="POST" action="{{route('word')}}"-->
        <form id="formulario" style="max-width:800px;" onsubmit="grabar_documentos();return false;" enctype="multipart/form-data">
            <div class="row">
            
            <select class="form-control" name="idPlantillas" id="idPlantillas" onchange="Seleccionar_Plantilla()">
                    <option value="">Selecione Plantilla</option>
                    <?php foreach ($Plantilla as $key) {
                    ?>
                    <option value="<?=$key->idPlantillas?>" <?=($key->idPlantillas==$Registro->idPlantillas)?'selected':''?>><?=$key->Nombre?></option>
                    <?php
                    }
                    ?>
            </select>
            
<div class="main-card mb-12 card">
    <div class="card-body"><h5 class="card-title" style="font-size:20px;"></h5>
    <button class="btn btn-success" id="btn_guardar">GRABAR</button>
    @csrf
</br>
</br>
<div class="position-relative form-group">
            
            <div class="row">
            <div id="spreadsheet"></div>


            </div>
        </div>
</div>
<?php
$campos = explode(",",$Registro->Variable);
?>

<script type="text/javascript">
    
function Seleccionar_Plantilla(){
  idPlantillas= $("#idPlantillas").val(); 
  location.href = "{{route('excel_Modificar_Documentos')}}?idPlantillas="+idPlantillas;
}

var g_datos= [];
function grabar_documentos(id='formulario'){
    var data = g_excel.getData();
            if(g_row_modificado.length){
            g_datos = [];
            for (var i = 0; i < data.length; i++) {
                if(g_row_modificado.indexOf(i)>-1){
                    g_datos.push(data[i].join('||'));//.toUpperCase()
                }
            };
                ajax_data = {
                        "idPlantillas"   : $("#idPlantillas").val(),
                        "data"    : g_datos.join('&&'),
                        "campos"  : "<?=$Registro->Variable?>",
                        "_token"  : $("input[name='_token'").val(),
                        "alt"     : Math.random()
                    }
                    $.ajax({
                                    type: "POST",
                                    url: "{{route('excel_Grabar_Documentos')}}",
                                    data: ajax_data,
                                    dataType: "json",
                                    beforeSend: function(){
                                        $("#btn_guardar").prop('disabled',true);
                                    },
                                    error: function(){
                                        alert("error peticion ajax");
                                    },
                                    success: function(data){
                                        alert('Guardado');
                                        $("#btn_guardar").prop('disabled',false);
                                        window.open("generar_word?idDocumentos="+data.toString());
                                        tabla_documentos();
                                    }
                 });
                }else{
                    alert('Ninguna información que guardar');
                }
            }

var g_excel;

function tabla_documentos(){
    ajax_data = {
    "idPlantillas" : $("#idPlantillas").val(),
    "alt"    : Math.random()
    }
    $.ajax({
            type: "GET",
                    url: "{{route('tabla_documentos')}}",
                    data: ajax_data,
                    dataType: "json",
                    beforeSend: function(){
                          //imagen de carga
                          $("#login").html("<p align='center'><img src='http://intranet.ugel01.gob.pe/prestamos/public/images/cargando.gif'/></p>");
                    },
                    error: function(){
                          alert("error peticiÃ³n ajax");
                    },
                    success: function(data){
                        var tabla = [];
                        if(data.length){
                            for (let i = 0; i < data.length; i++) {
                            var key=[];
                            key.push(data[i]['idDocumentos']);
                            key.push(data[i]['Fecha']);
                            <?php
                            foreach ($campos as $key) {
                            ?>
                            key.push(data[i]['<?=$key?>']);
                            <?php
                            }
                            ?>
                            tabla.push(key);
                          }                            
                        }else{
                            tabla.push(['','']);
                        }
                          
                          $("#spreadsheet").html("");
                          g_row_modificado = [];
                          g_excel = jexcel(document.getElementById('spreadsheet'), {
                            data:tabla,
                            onchange:handler,
                            columns: [
                                {
                                    type: 'text',
                                    title:'idDocumentos',
                                    width:0.01
                                },
                                {
                                    type: 'calendar',
                                    title:'Fecha',
                                    width:90
                                },
                                <?php
                                  foreach ($campos as $key) {
                                  ?>
                                {
                                    type: 'text',
                                    title:'<?=$key?>',
                                    width:90
                                },
                                <?php
                                  }
                                  ?>
                            ]
                        });

                      }
              });
              
}

//SE IDENTIFICA LOS REGISTROS MODIFICADOS
var g_row_modificado = [];
handler = function(obj, cell, col, row, val) {
if(g_row_modificado.indexOf(parseInt(row))==-1){ g_row_modificado.push(parseInt(row)); }
}

tabla_documentos();
$(".app-container").removeClass('fixed-header');

</script>

@endsection
