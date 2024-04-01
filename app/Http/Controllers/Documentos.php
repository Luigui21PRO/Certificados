<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plantilla;
use App\Models\Documento;
use DB;//Conexion a BD 
use Storage;
use ZipArchive;

require "../vendor/autoload.php";
use clsTinyButStrong;//tbs_class
include_once('../vendor/tinybutstrong/opentbs/tbs_plugin_opentbs.php'); 

class Documentos extends Controller
{
    public function __construct(){
        //Valida que haya una session, si no la hay te devuelve al login
        $this->middleware('auth');
    }
    
    public function listar_documentos(Request $request){
        $idPlantillas = $request['idPlantillas'];
        if($idPlantillas){
            $info['Registro']  = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0];
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            $info['html'] = ($info['Registro']->usarhtml=="SI")?view('PlantillasHTML/Plantilla'.$info['Registro']->idPlantillas):"";
            return view('Documentos/listar_Documentos',$info);
        }else{
            $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
            $info['Registro']  = ($info['Plantilla'])?$info['Plantilla'][0]:exit("No hay Plantillas disponibles.<br><a href='listar_Registros'>cree una Plantilla</a>");;
            $info['html'] = ($info['Registro']->usarhtml=="SI")?view('PlantillasHTML/Plantilla'.$info['Registro']->idPlantillas):"";
            return view('Documentos/listar_Documentos',$info);
        }

        /*$idPlantillas = ($request['idPlantillas'])?$request['idPlantillas']:7;
        $info['Registro'] =  ($idPlantillas)?DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0]:0;
        $info['Plantilla'] = DB::connection('mysql')->select("SELECT * FROM `plantilla_documento` WHERE estado=1");
        return view('Documentos/listar_Documentos',$info);*/
    }

    public function tabla_documentos(Request $request){
        $idPlantillas = $request['idPlantillas'];
        $where = ($request['iddoc'])?' and idDocumentos IN('.$request['iddoc'].')':'';
        $plantilla =  explode(",",DB::connection('mysql')->select("SELECT Variable FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0]->Variable);
        $sql = DB::connection('mysql')->select("SELECT *,DATE_FORMAT(Fecha,'%d/%m/%Y') as F_Fecha FROM `documento`where estado=1 And idPlantillas= $idPlantillas $where");
         for ($i=0; $i < count($sql); $i++) {
            $Contenido=json_decode($sql[$i]->Contenido);
            foreach ($plantilla as $key) {
                $sql[$i]->$key = (array_key_exists($key,(Array)$Contenido))?$Contenido->$key:'';
            }
         }
        echo json_encode($sql);
    }

    public function guardar_documentos(Request $request){
        date_default_timezone_set("America/Lima");
        $idDocumentos =$request['idDocumentos'];
        //$ins['Asunto'] = $request['Asunto'];
        $ins['Fecha'] = $request['Fecha'];
        $ins['idPlantillas'] = $request['idPlantillas'];
        $Contenido=$request->all();
        unset($Contenido['_token']);
        unset($Contenido['Plantilla']);
        unset($Contenido['idDocumentos']);
        unset($Contenido['Asunto']);
        $ins['Contenido'] = json_encode($Contenido);
        //print_r($request->all());

        $primeravariable = DB::connection('mysql')->select("SELECT SUBSTRING(Variable,1,POSITION(',' in Variable)-1) as primeravariable FROM `plantilla_documento` WHERE idPlantillas = 16");
        $primeravariable = $primeravariable[0]->primeravariable;
        //print_r($primeravariable);
        $ins['Asunto'] = $request[$primeravariable];

        if($idDocumentos){
            Documento::where('idDocumentos',$idDocumentos)->update($ins);
            $ins['idDocumentos'] = $idDocumentos;
        }else{
            $ins['Fecha'] = date('Y-m-d');
            $ins['idDocumentos'] = Documento::insertGetId($ins);
        }       
        return $ins;
    }

    public function eliminar_documentos(Request $request){
        $idDocumentos=$request['idDocumentos'];
        Documento::where('idDocumentos',$idDocumentos)->update(['estado'=>0]);
        return 1;
    }


    public function word(Request $request){
        $data = $request->all();
        $idPlantillas = $data['idPlantillas'];
        $plantilla_documento =  DB::connection('mysql')->select("SELECT Variable,Plantilla FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0];
        
        $Variable = explode(",",$plantilla_documento->Variable);
        $plantilla = substr($plantilla_documento->Plantilla,1);

      // prevent from a PHP configuration problem when using mktime() and date()
        if (version_compare(PHP_VERSION,'5.1.0')>=0) {
            if (ini_get('date.timezone')=='') {
                date_default_timezone_set('UTC');
            }
        }
        // Initialize the TBS instance
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
        //Parametros
        $nomprofesor = 'Jorge Acosta';
        $fechaprofesor = '24/02/2024';
        $firmadecano = 'storage/Plantilla/firma.png';
        //Cargando template
        $template = $plantilla;
        //$template = 'storage/Plantilla/FORMATO ÚNICO DE TRÁMITE.docx';//Plantilla
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
        //Escribir Nuevos campos

        $TBS->MergeField('asunto',$data['Asunto']);//Variables a remplazar
        $TBS->MergeField('fecha', $data['Fecha']);//Variables a remplazar

        foreach ($Variable as $key) {
            $TBS->MergeField($key, $data[$key]);//Variables a remplazar
        }

        $TBS->VarRef['x'] = $firmadecano;

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        //$save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        //$save_as='Jorge tonto.docx';
        //$output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
        //$save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        //$output_file_name = 'storage/word/'.$save_as;
        $save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        $output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
        $output_file_name= rand().'.docx';
        if ($save_as==='') {
            $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
            exit();
        } else {
            $TBS->Show(OPENTBS_FILE, $output_file_name);
            exit("File [$output_file_name] has been created.");
        }
    }


    public function generar_word(Request $request){
        $idDocumentos = $request['idDocumentos'];
        //$plantilla =  explode(",",DB::connection('mysql')->select("SELECT Variable FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0]->Variable);
        $sql = DB::connection('mysql')->select("SELECT D.*,DATE_FORMAT(D.Fecha,'%d/%m/%Y') as Fecha, P.Variable, P.Plantilla
        FROM documento D 
        INNER JOIN plantilla_documento P ON D.idPlantillas= P.idPlantillas
        where D.estado=1 And D.idDocumentos IN($idDocumentos)");
        $lista=array();
         for ($i=0; $i < count($sql); $i++) {
            //convertido
            $Contenido=json_decode($sql[$i]->Contenido);
            $plantilla=explode(",",$sql[$i]->Variable);
            foreach ($plantilla as $key) {
                $sql[$i]->$key = (array_key_exists($key,(Array)$Contenido))?$Contenido->$key:'';
            }
            //convertido

            //generar word
            $data = (Array)$sql[$i];
            //print_r($data);

            $idPlantillas = $data['idPlantillas'];
            $plantilla_documento =  DB::connection('mysql')->select("SELECT Variable,Plantilla FROM `plantilla_documento` WHERE estado=1 AND idPlantillas=$idPlantillas")[0];
            $Variable = explode(",",$plantilla_documento->Variable);
            $plantilla = substr($plantilla_documento->Plantilla,1);
            if (version_compare(PHP_VERSION,'5.1.0')>=0) {
                if (ini_get('date.timezone')=='') {
                    date_default_timezone_set('UTC');
                }
            }
            
            if (version_compare(PHP_VERSION,'5.1.0')>=0) {
                if (ini_get('date.timezone')=='') {
                    date_default_timezone_set('UTC');
                }
            }
             // Initialize the TBS instance
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
        //Parametros
        $nomprofesor = 'Jorge Acosta';
        $fechaprofesor = '24/02/2024';
        $firmadecano = 'storage/Plantilla/firma.png';
        //Cargando template
        $template = $plantilla;
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
        $TBS->MergeField('asunto',$data['Asunto']);//Variables a remplazar
        $TBS->MergeField('fecha', $data['Fecha']);//Variables a remplazar
        foreach ($Variable as $key) {
            $TBS->MergeField($key, $data[$key]);//Variables a remplazar
        }
        $TBS->VarRef['x'] = $firmadecano;

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        //$save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        $save_as=$data['Asunto'].'_'.rand().'.docx';
        //$output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
        $output_file_name = 'storage/word/'.$save_as;
        if ($save_as==='') {
            $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
            exit();
        } else {
            $TBS->Show(OPENTBS_FILE, $output_file_name);
            echo("<br>File [<a href='$output_file_name'>$output_file_name</a>] has been created.");
            $lista[]=$output_file_name;
        }
    }
            //generar word

            //comprimir
            echo '<br><br>';
            //print_r($lista);
            $zip = new \ZipArchive();
            $fileName = 'storage/word/trabajo'.rand().'.zip';
            if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE){
        foreach ($lista as $key => $value){
            $relativeName = basename($value);
            $zip->addFile($value, $relativeName);
        }
        $zip->close();
        echo "<br><a href='$fileName'>Descargar Todo</a>";
    }
    //return response()->download(public_path($fileName));

            //comprimir

         }
        //echo json_encode($sql);
    }



/*
public function word(){
      // prevent from a PHP configuration problem when using mktime() and date()
        if (version_compare(PHP_VERSION,'5.1.0')>=0) {
            if (ini_get('date.timezone')=='') {
                date_default_timezone_set('UTC');
            }
        }
        // Initialize the TBS instance
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
        //Parametros
        $nomprofesor = 'Jorge Acosta';
        $fechaprofesor = '24/02/2024';
        $firmadecano = 'storage/Plantilla/firma.png';
        //Cargando template
        $template = 'storage/Plantilla/FORMATO ÚNICO DE TRÁMITE.docx';//Plantilla
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
        //Escribir Nuevos campos
        $TBS->MergeField('pro.nomprofesor', $nomprofesor);//Variables a remplazar
        $TBS->MergeField('pro.fechaprofesor', $fechaprofesor);//Variables a remplazar
        $TBS->MergeField('jorgetexto', 'Se entrega por valor');//Variables a remplazar varible jorgetexto
        $TBS->MergeField('Luiguitexto', 'Estudiante');//Variables a remplazar
        //FORMATO ÚNICO DE TRÁMITE
        $TBS->MergeField('solicitud', 'Solicito Matricula');//Variables a remplazar
        $TBS->MergeField('dependencia', 'Ugel01');//Variables a remplazar
        $TBS->MergeField('datos', 'Jorge Acosta');//Variables a remplazar
        $TBS->MergeField('dni', '3245332');//Variables a remplazar
        $TBS->MergeField('telefono', '932971570');//Variables a remplazar
        $TBS->MergeField('correo', 'affernando2002@gmail.com');//Variables a remplazar
        $TBS->MergeField('domicilio', 'AV.Guardia Civil');//Variables a remplazar
        $TBS->MergeField('dnidelniño', '5433232');//Variables a remplazar
        $TBS->MergeField('modulo', 'Matematica');//Variables a remplazar
        $TBS->MergeField('fundamentacion', 'ds adasdsa dsadsadsad adas adsadasda sdiusadhsa udhusadusghwuigdiqwh ds adasdsa dsadsadsad adas adsadasda sdiusadhsa udhusadusghwuigdiqwh');//Variables a remplazar

        //FICHA DE MATRÍCULA
        $TBS->MergeField('nombre', 'Jorge');//Variables a remplazar
        $TBS->MergeField('codigo', '1');//Variables a remplazar

        $TBS->VarRef['x'] = $firmadecano;

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        $save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        $output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
        if ($save_as==='') {
            $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
            exit();
        } else {
            $TBS->Show(OPENTBS_FILE, $output_file_name);
            exit("File [$output_file_name] has been created.");
        }
    }
*/
/*
    public function guardar_documentos(Request $request){
        date_default_timezone_set("America/Lima");
        $idDocumentos =$request['idDocumentos'];
        $ins['Asunto'] = $request['Asunto'];
        $ins['Fecha'] = $request['Fecha'];
        $ins['idPlantillas'] = $request['Plantilla'];
        $Contenido=$request->all();
        unset($Contenido['_token']);
        unset($Contenido['Plantilla']);
        unset($Contenido['idDocumentos']);
        unset($Contenido['Asunto']);
        $ins['Contenido'] = json_encode($Contenido);
        //print_r($request->all());

        if($idDocumentos){
            Documento::where('idDocumentos',$idDocumentos)->update($ins);
            $ins['idDocumentos'] = $idDocumentos;
        }else{
            $ins['Fecha'] = date('Y-m-d');
            $ins['idDocumentos'] = Documento::insertGetId($ins);
        }       
        return $ins;
    }

    public function eliminar_documentos(Request $request){
        $idDocumentos=$request['idDocumentos'];
        Documento::where('idDocumentos',$idDocumentos)->update(['estado'=>0]);
        return 1;
    }

    public function word(){
      // prevent from a PHP configuration problem when using mktime() and date()
        if (version_compare(PHP_VERSION,'5.1.0')>=0) {
            if (ini_get('date.timezone')=='') {
                date_default_timezone_set('UTC');
            }
        }
        // Initialize the TBS instance
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load the OpenTBS plugin
        //Parametros
        $nomprofesor = 'Jorge Acosta';
        $fechaprofesor = '24/02/2024';
        $firmadecano = 'storage/Plantilla/firma.png';
        //Cargando template
        $template = 'storage/Plantilla/Plantilla_Colegiado.docx';
        $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
        //Escribir Nuevos campos
        $TBS->MergeField('pro.nomprofesor', $nomprofesor);
        $TBS->MergeField('pro.fechaprofesor', $fechaprofesor);
        $TBS->VarRef['x'] = $firmadecano;

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        $save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
        $output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
        if ($save_as==='') {
            $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
            exit();
        } else {
            $TBS->Show(OPENTBS_FILE, $output_file_name);
            exit("File [$output_file_name] has been created.");
        }
    }
*/