<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Especialidad;
use App\User;
use App\ComiteStartUp;
use DB;

class EspecialidadController extends Controller
{
    //
    public function listarEspecialidades(){

        $especialidades = Especialidad::select(
            'idespecialidad as value',
            'nombre as viewValue'
            )->where('active',1)->get();

   return response()->json(['rpta'=> $especialidades,'success'=>true,'msg' => 'Especialidades obtenidas con exito',
        ],201);
    }


   public function listarEspecialidadesPorEvaluador($id){
        $evaluador = User::where('user_id',$id)->first();
        if ($evaluador !== null){
       
        $verificar_datos=DB::table('users_especialidades')->where('user_id','=',$id)->get();

        if (count($verificar_datos)!==0){
         $user=DB::table('users_especialidades')->join('users','users_especialidades.user_id','=','users.user_id')->join('especialidad','users_especialidades.idespecialidad','=','especialidad.idespecialidad')->select('especialidad.idespecialidad as Value','especialidad.nombre as viewValue')->where('users.user_id','=',$id)->where('especialidad.active','=','1')->get();
            return response()->json(['msg' => 'Especialidades obtenidas con exito',
                    'rpta'=> $user,
                    'success'=>true
            ],200);
         }else{
                return response()->json([
                    'msg' => 'No tiene especialidades registradas',
                    'rpta'=> [],
                    'success'=>false
                ],500);
          }
        }else{
            return response()->json([
                'msg' => 'No tiene evaluador para el id '.$id,
                'rpta'=> [],
                'success'=>false
            ],500);
        }
    } 

  public function tablaEspecialidadesEvaluadores(Request $request){
        /*
        Input EvaluadoresId  244 , 923 , 934
        Output
        [
            (Giancarlo Lopez) , (Estrategico, Marketing, Comercial)
            (Luis Sanchez) , (Digital Finanzas Fintech),
            (Crhistian Manrique) , (Marketing, Finanzas, Fintech)
        ] 
        */        
        // $sin_corchetes =  substr($request->header('EVALUADORES'),1,-1);
        
        $id_evaluadores = explode(',',$request->header('EVALUADORES'));
        // echo sizeof($id_evaluadores);
        // echo $id_evaluadores;
        // echo $id_evaluadores[0];
        $evaluadores = User::whereIn('user_id',$id_evaluadores)->get();
        // echo $evaluadores;
        
        $tabla = null;
        $especialidades = Especialidad::where('active',1)->where('nombre','<>',NULL)->get();
        foreach ($evaluadores as $evaluador){
           $especialidades_evaluador_ids = explode(',',$evaluador->especialidades);
           $especialidades_evaluador = '';
           $especialidades_evaluador_names = $especialidades->whereIn('idespecialidad',$especialidades_evaluador_ids);
           foreach ($especialidades_evaluador_names as $especialidad){
              $especialidades_evaluador = $especialidades_evaluador.$especialidad['nombre'].', ';
              // $especialidades_evaluador=$especialidades_evaluador.' , ';
           }
           // $row = json_encode(array('Nombre'=> $evaluador->name, 'Especialidades' => $especialidades_evaluador));
           $row['Nombre'] = $evaluador->name;
           $row['Especialidades'] = substr($especialidades_evaluador,0, -2);
           // echo $row;
           // echo $row["Nombre"];
           $tabla[] = $row;
        }
        $tabla = $this->stripslashes_deep($tabla);
        return response()->json(['rpta' => $tabla, 'success'=> true], 201);
    }




}
