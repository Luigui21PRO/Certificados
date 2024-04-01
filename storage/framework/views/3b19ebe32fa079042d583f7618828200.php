
<?php $__env->startSection('html'); ?>

<!-- ***********************LIBRERIAS PARA LA TABLA**************************** -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<!-- ***********************LIBRERIAS PARA LA TABLA**************************** -->

<div class="main-card mb-12 card">
    <div class="card-body"><h5 class="card-title" style="font-size:20px;"><b>DOCUMENTOS</b>
    </h5>
        <div class="position-relative form-group">
          
          <!--return false;-->
        <form id="formulario" style="max-width:800px;" onsubmit="grabar_documentos();" method="POST" action="<?php echo e(route('word')); ?>" enctype="multipart/form-data">
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
            
                <div class="col-sm-2">idDocumentos :</div>
                <div class="col-sm-10"><input type="text" class="form-control" name="idDocumentos" id="idDocumentos" readonly></div>

                <div class="col-sm-2">Fecha:</div>
                <div class="col-sm-10"><input type="date" class="form-control" name="Fecha" id="Fecha"></div>
                
              </div>

              <div class="row">

                <?php
                $campos = explode(",",$Registro->Variable);
                if($Registro->usarhtml=="SI"){
                  echo $html;
                }else{

                  foreach ($campos as $key) {
                  ?>
                  <div class="col-sm-2"><?=str_replace('-',' ',$key)?>:</div>
                  <div class="col-sm-10"><input type="text" class="form-control" name="<?=$key?>" id="<?=$key?>"></div>
                  <?php
                  }
               
                }
                ?>
                
                <div class="col-sm-2"></div>
                <div class="col-sm-10"><br><button class="btn btn-success">GRABAR</button> <input class="btn btn-danger" type="reset" value="Cancelar"> </div>
            </div>
            <?php echo csrf_field(); ?>
        </form>
            <br>
            <div class="divtabla" id="div_citas">
            <table class="display table table-bordered table-striped table-dark" id="t_programas" style="color:#000;font-size:10px;width:100%;">
                          <thead>
                            <tr style="background-color:rgb(0,117,184);">
                                <td style="width:15px;color:#fff;"><b>N</b></td>
                                <td style="width:55px;color:#fff;"><b>Editar</b></td>
                                <td style="width:35px;color:#fff;"><b>Eliminar</b></td>
                                <td style="width:55px;color:#fff;"><b>Fecha</b></td>
                                  <?php
                                  foreach ($campos as $key) {
                                  ?>
                                  <td style="width:55px;color:#fff;"><b><?=$key?></b></td>
                                  <?php
                                  }
                                  ?>
                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">

function Seleccionar_Plantilla(){
  idPlantillas= $("#idPlantillas").val();
  location.href = "<?php echo e(route('listar_documentos')); ?>?idPlantillas="+idPlantillas;
}

function grabar_documentos(id='formulario'){
          var formData = new FormData($("#"+id)[0]);
          var message = "";
          $.ajax({
              url: "<?php echo e(route('guardar_documentos')); ?>",  
              type: 'POST',
              data: formData,
              dataType: "json",
              cache: false,
              contentType: false,
              processData: false,
              beforeSend: function(){
                  
              },
              success: function(data){
                formulario.reset();
                tabla_documentos();
                alert('Guardado');
              },
              error: function(){
                alert('Se ha producido un error, recargue la página e inténtelo de nuevo.');
              }
            });
        }

function tabla_documentos(){
    ajax_data = {
      "idPlantillas" : $("#idPlantillas").val(),
      "alt"    : Math.random()
    }
    $.ajax({
                    type: "GET",
                    url: "<?php echo e(route('tabla_documentos')); ?>",
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
                          for (let i = 0; i < data.length; i++) {
                            data[i]['nro']= i+1;
                            data[i]['editar']='<span class="btn btn-success" onclick="editar('+i+')">editar</span>';
                            data[i]['eliminar']='<span class="btn btn-danger" onclick="eliminar_documentos('+data[i]['idDocumentos']+')">eliminar</span>';
                          }
                          table4.clear().draw();
                          table4.rows.add(data).draw();
                          g_data=data;
                      }
              });
              
}
function editar(nro){
  var data =  g_data[nro];
  $("#idDocumentos").val(data['idDocumentos']);
  $("#Asunto").val(data['Asunto']);
  $("#Fecha").val(data['Fecha']);

  <?php
  foreach ($campos as $key) {
  ?>
  $("#<?=$key?>").val(data['<?=$key?>']);
  <?php
  }
  ?>
}

function eliminar_documentos(idDocumentos){
    ajax_data = {
      "idDocumentos"   : idDocumentos,
      "alt"    : Math.random()
    }
    if(confirm('¿Desea eliminar?')){
    $.ajax({
                    type: "GET",
                    url: "<?php echo e(route('eliminar_documentos')); ?>",
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
                      alert('eliminado');
                      tabla_documentos();
                      }
              });
      }else{

      }

}


tabla_documentos();

var table4 = $("#t_programas").DataTable( {
                        dom: 'Bfrtip',
                        buttons: ['excel'],
                        "iDisplayLength": 35,
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                        },
                        data:[],
                        "columns": [
                            { "data": "nro" },
                            { "data": "editar" },
                            { "data": "eliminar" },
                            { "data": "F_Fecha" },
                            <?php
                                  foreach ($campos as $key) {
                                  ?>
                                  { "data": "<?=$key?>" },

                                  <?php
                                  }
                                  ?>
                            //{ "data": "eliminar" },
                        ],                          
                        rowCallback: function (row, data) {},
                        filter: true,
                        info: true,
                        ordering: true,
                        processing: true,
                        retrieve: true                          
                    });

</script>

<?php $__env->stopSection(); ?>
                      

<?php echo $__env->make('layout_especialista/cuerpo', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\certificados\resources\views/Documentos/listar_Documentos.blade.php ENDPATH**/ ?>