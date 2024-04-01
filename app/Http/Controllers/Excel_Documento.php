<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documento;
use DB;//Conexion a BD

class Excel_Documento extends Controller
{
    public function excel_Documentos(Request $request){
        $idPlantillas = $request['idPlantillas'];
        if($idPlantillas){
            $info['Registro']  = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0];
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            return view('Excel_Documentos/excel_Documentos',$info);
        }else{
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            $info['Registro']  = ($info['Plantilla'])?$info['Plantilla'][0]:exit("No hay Plantillas disponibles.<br><a href='listar_Registros'>cree una Plantilla</a>");;
            return view('Excel_Documentos/excel_Documentos',$info);
        }
    }

    public function excel_Modificar_Documentos(Request $request){
        $idPlantillas = $request['idPlantillas'];
        if($idPlantillas){
            $info['Registro']  = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0];
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            return view('Excel_Documentos/excel_Modificar_Documentos',$info);
        }else{
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            $info['Registro']  = ($info['Plantilla'])?$info['Plantilla'][0]:exit("No hay Plantillas disponibles.<br><a href='listar_Registros'>cree una Plantilla</a>");;
            return view('Excel_Documentos/excel_Modificar_Documentos',$info);
        }
    }

    public function excel_Grabar_Documentos(Request $request){
        $ids=array();
        $campos = explode(",",$request['campos']);
        $data = explode('&&',$request['data']);

        if($data){
            foreach ($data as $fila) {
                $col = explode('||',$fila);
                $key = array();
                //print_r($col);
                $idDocumentos                = $col['0'];
                $key['Fecha']                = ($col['1']=='')?NULL: $this->fecha($col['1']);
                $key['idPlantillas']         = $request['idPlantillas'];
                $contenido= array();
                $nro=2;
                foreach ($campos as $tipo) {
                    $contenido[$tipo] = $col[$nro];
                    $nro++;
                }
                $key['contenido']= json_encode($contenido);
                if($idDocumentos){
                    Documento::where('idDocumentos',$idDocumentos)->update($key);
                }else{ 
                    $idDocumentos = Documento::insertGetId($key);
                }
                $ids[]= $idDocumentos;
            }
         return $ids;
        }
    }

    function fecha($fecha){
        $r_fecha = explode("/",$fecha);
        if(count($r_fecha)==3){
        $fecha = $r_fecha[2].'-'.$r_fecha[1].'-'.$r_fecha[0];
        }
        return $fecha;
    }



}


/*
public function excel_Grabar_Documentos(Request $request){

        $campos = explode(",",$request['campos']);
        $data = explode('&&',$request['data']);

        if($data){
            foreach ($data as $fila) {
                $col = explode('||',$fila);
                $key = array();
                //print_r($col);
                $idDocumentos                = $col['0'];
                $key['Asunto']               = $col['1'];
                $key['Fecha']                = ($col['2']=='')?NULL:$col['2'];
                $key['idPlantillas']         = $request['idPlantillas'];
                $contenido= array();
                $nro=3;
                foreach ($campos as $tipo) {
                    $contenido[$tipo] = $col[$nro];
                    $nro++;
                }
                $key['contenido']= json_encode($contenido);
                if($idDocumentos){
                    Documento::where('idDocumentos',$idDocumentos)->update($key);
                }else{ 
                    Documento::insert($key);
                }
            }
        return 1;
        }
    }
    
*/