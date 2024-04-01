<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantilla;
use DB;//Conexion a BD 
use Storage;

class Registros extends Controller
{

    public function __construct(){
        //Valida que haya una session, si no la hay te devuelve al login
        $this->middleware('auth');
    }

    public function listar_Registros(){
         $info['data'] = array();
        return view('RegistrodePlantillas/listar_Registros',$info);
    }
    public function tabla_Registros(){
        $sql = DB::connection('mysql')->select("SELECT *, DATE_FORMAT(Fecha,'%d/%m/%Y') AS Fecha FROM `plantilla_documento` where   estado=1");
        echo json_encode($sql);
    }
    public function guardar_Registros(Request $request){
        date_default_timezone_set("America/Lima");
        $idPlantillas = $request['idPlantillas'];
        $ins['Nombre'] = $request['Nombre'];
        $ins['Variable'] = $request['Variable'];
        $ins['usarhtml'] = $request['usarhtml'];

        //Cargar Platilla
        if($request->hasfile('Plantilla')){
            $archivo = $request->file('Plantilla')->store('public/Plantilla');
            $ins['Plantilla']= Storage::url($archivo);
        }

        //Cargar Html
        if($request->hasfile('Html')){
            $archivo = $request->file('Html')->store('public/Html');
            $ins['Html']= Storage::url($archivo);
        }


        if($idPlantillas){
            Plantilla::where('idPlantillas',$idPlantillas)->update($ins);
            $ins['idPlantillas'] = $idPlantillas;
        }else{
            $ins['Fecha'] = date('Y-m-d');
            $ins['idPlantillas'] = Plantilla::insertGetId($ins);
        }       
        return $ins;
    }

    public function eliminar_Registros(Request $request){
        $idPlantillas=$request['idPlantillas'];
        Plantilla::where('idPlantillas',$idPlantillas)->update(['estado'=>0]);
        return 1;
    }
}

