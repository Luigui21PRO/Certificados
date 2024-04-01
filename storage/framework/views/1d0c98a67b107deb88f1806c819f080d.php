
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
    <div class="card-body"><h5 class="card-title" style="font-size:20px;"><b>PLANTILLAS</b>
    </h5>
        <div class="position-relative form-group">
          
            
        <form id="formulario" style="max-width:800px;" method="POST" onsubmit="grabar_Registros();return false;" enctype="multipart/form-data">
            <div class="row">
              
            <div class="col-sm-2">idPlantillas :</div>
                <div class="col-sm-10"><input type="text" class="form-control" name="idPlantillas" id="idPlantillas" readonly></div>

                <div class="col-sm-2">Nombre :</div>
                <div class="col-sm-10"><input type="text" class="form-control" name="Nombre" id="Nombre"></div>

                <div class="col-sm-2">Variables:</div>
                <div class="col-sm-10"><textarea class="form-control" name="Variable" id="Variable"></textarea></div>
                
                <div class="col-sm-2">Plantilla:</div>
                <div class="col-sm-10"><input type="file" class="form-control" name="Plantilla" id="Plantilla"></div>
                
                <div class="col-sm-2">Usar Html:</div>
                <div class="col-sm-10">
                  <!--<input type="text" class="form-control" name="tipCnn" id="tipCnn">-->
                  <select class="form-control" name="usarhtml" id="usarhtml">
                  <option value="NO">NO</option>
                  <option value="SI">SI</option>
                  </select>
                  </div>
                    
            </select>

                <div class="col-sm-2"></div>
                <div class="col-sm-10"><br><button class="btn btn-success">GRABAR</button> <input class="btn btn-danger" type="reset" onclick="cancelar()" value="Cancelar"> </div>
            </div>
            <?php echo csrf_field(); ?>
        </form>
            
            <div class="divtabla" id="div_citas">
            <table class="display table table-bordered table-striped table-dark" id="t_programas" style="color:#000;font-size:10px;width:100%;">
                          <thead>
                            <tr style="background-color:rgb(0,117,184);">
                                <td style="width:15px;color:#fff;"><b>N</b></td>
                                <td style="width:55px;color:#fff;"><b>Editar</b></td>
                                <td style="width:35px;color:#fff;"><b>Eliminar</b></td>
                                <td style="width:45px;color:#fff;"><b>Nombre</b></td>
                                <td style="width:45px;color:#fff;"><b>Variable</b></td>
                                <td style="width:55px;color:#fff;"><b>Plantilla</b></td>
                                <td style="width:55px;color:#fff;"><b>Usar Html</b></td>
                                <td style="width:55px;color:#fff;"><b>Fecha</b></td>

                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
            </div>

        </div>
    </div>
</div>


<script type="text/javascript">

function grabar_Registros(id='formulario'){
          if($("#Variable").val().split(' ').length>1){
            alert("El campo Variables no puede tener espacios en blanco");
            return false;
          }
          var formData = new FormData($("#"+id)[0]);
          var message = "";
          $.ajax({
              url: "<?php echo e(route('guardar_Registros')); ?>",  
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
                tabla_Registros();
                alert('Guardado');
              },
              error: function(){
                alert('Se ha producido un error, recargue la página e inténtelo de nuevo.');
              }
            });
        }

function tabla_Registros(){
    ajax_data = {
      "alt"    : Math.random()
    }
    $.ajax({
                    type: "GET",
                    url: "<?php echo e(route('tabla_Registros')); ?>",
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
                            data[i]['t_plantilla'] = (data[i]['Plantilla'])?'<a target="_blank" href=".'+data[i]['Plantilla']+'">Plantilla</a>':'';
                            data[i]['editar']='<span class="btn btn-success" onclick="editar('+i+')">editar</span>';
                            data[i]['eliminar']='<span class="btn btn-danger" onclick="eliminar_Registros('+data[i]['idPlantillas']+')">eliminar</span>';
                          }
                          table4.clear().draw();
                          table4.rows.add(data).draw();
                          g_data=data;
                      }
              });
              
}

function editar(nro){
  var data = g_data[nro];
  $("#idPlantillas").val(data['idPlantillas']);
  $("#Nombre").val(data['Nombre']);
  $("#Variable").val(data['Variable']);
  $("#usarhtml").val(data['usarhtml']);

}

function eliminar_Registros(idPlantillas){
    ajax_data = {
      "idPlantillas"   : idPlantillas,
      "alt"    : Math.random()
    }
    if(confirm('¿Desea eliminar?')){
    $.ajax({
                    type: "GET",
                    url: "<?php echo e(route('eliminar_Registros')); ?>",
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
                      tabla_Registros();
                      }
              });
      }else{

      }

}


tabla_Registros();

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
                            { "data": "Nombre" },
                            { "data": "Variable" },
                            { "data": "t_plantilla" },
                            { "data": "usarhtml" },
                            { "data": "Fecha" },
                            
                            
  
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
                      

<?php echo $__env->make('layout_especialista/cuerpo', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\certificados\resources\views/RegistrodePlantillas/listar_Registros.blade.php ENDPATH**/ ?>