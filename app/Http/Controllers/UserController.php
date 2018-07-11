<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\StartUp;
use App\Mail\CambioContrasena\CambioContrasenaAdministrador;
use Mail;
use DateTime;

class UserController extends Controller
{
    //

    public function listar(){
    $user=User::join('personas as p','users.persona_id','=','p.person_id')->join('roles as rol','users.rol_id','=','rol.idroles')->select('users.email as "Cuenta de usuario"','p.name as Nombre','rol.rolescol as Categoria','p.dob as "Fecha de nacimiento"','p.phone as Telefono','p.genre as Genero','users.activity as Estado','users.user_id')->whereIn('users.activity',[0,1,2])->get();

     for ($i=0; $i < $user->count(); $i++) {
     	 $var = $user[$i]["Fecha de nacimiento"];
          $user[$i]["Fecha de nacimiento"] = date("d/m/Y", strtotime($var));
     }
     return response()->json(['rpta' => $user , 'success' => true], 201);
    }


    public function cambiarPassword($id,Request $request){

      $user = User::where('user_id',$id)->first();
      $user->password = bcrypt($request->input('password'));

     try{
      $user->update();
            if ($user->category_id != null && app('env') == 'prod' ) {
                if($user->category_id == 1){                	
                    Mail::to($user->email)->send(new CambioContrasenaAdministrador($user,$request->input('password'),$request->header('URL')));
                    Mail::to($soporte)->send(new CambioContrasenaAdministrador($user,$request->input('password'),$request->header('URL')));
                } else if($user->category_id == 2 ){
                    Mail::to($user->email)->send(new CambioContrasenaEvaluador($user,$request->input('password'),$request->header('URL')));
                    Mail::to($soporte)->send(new CambioContrasenaEvaluador($user,$request->input('password'),$request->header('URL')));
                } else if($user->category_id > 2) { // $user->category == 3 || $user->category == 4 || $user->category == 5 || $user->category == 6
                    Mail::to($user->email)->send(new CambioContrasenaIncubado($user,$request->input('password'),$request->header('URL')));
                    Mail::to($soporte)->send(new CambioContrasenaIncubado($user,$request->input('password'),$request->header('URL')));
                }
            }
          return response()->json(['msg' => 'Contraseña cambiada' , 'success' => true, 'rpta'=> ''], 201);
    }catch(\Exception $e){
       return response()->json(['msg' => 'Error:'.$e->getMessage() , 'success' => false], 500);
        }
    }
    
    public function cambiarEstado($id,Request $request){
        $user = User::where('user_id', $id)->first();
        $user->activity = $request->input('activity');
        $user->update();
        if($user->start_up_id != null){
            $startUp = StartUp::where('start_up_id',$user->start_up_id)->first();
            $startUp->activity = $request->input('activity');
            $startUp->update();
        }
        switch ($request->input('activity')){
            case '1':
                return response()->json(['msg' => 'Usuario habilitado', 'success' => true ], 201);
                break;
            
            case '2':
                return response()->json(['msg' => 'Usuario deshabilitado', 'success' => true ], 201);
                break;
            
            case '3':
                return response()->json(['msg' => 'Usuario eliminado', 'success' => true ], 201);
                break;
        }
    }

    public function registrar(Request $request){

     	try{        
        $soporte = 'soporte@disnovo.com';
     	$user = new User();
        $user->name = $request->input('name');
        $user->password = bcrypt($request->input('password'));
        $user->email = $request->input('email');
        $user->phone = $user->phone = $request->input('phone');
        $user->genre = $user->genre = $request->input('genre');
        $user->dob = $user->dob = $request->input('dob');


     	if ($request->input('category') == 3){
            if($request->input('sub_category') == null || $request->input('sub_category') == '' || $request->input('sub_category') == -1){
                    $user->category_id = $request->input('category') + 0;
                // return response()->json(['msg' => 'No se pudo crear el usuario incubado falta asignarle su subcategoria','success' => false], 201);
            } else {
                    $user->category_id = $request->input('category') + $request->input('sub_category');

            }                
         $user->fecha_inicio = $request->input('fecha_inicio');//Solo aparecera el campo si en categoria se escoje incubado
                $user->fecha_inicio_historico = $request->input('fecha_inicio');
                $date =new DateTime($user->fecha_inicio);
                if($date != null && $date->format('Y') < 2012){
                    return response()->json(['msg' => 'La fecha de inicio no puede ser menor al 2012','success' => false, 'rpta'=>''], 201);
                }else{
                    if ($user->category != null && app('env') == 'prod') {
                        Mail::to($user->email)->send(new RegistroIncubado($user,$request->input('password'),$request->header('URL')));
                        Mail::to($soporte)->send(new RegistroIncubado($user,$request->input('password'),$request->header('URL')));
                    }
                }
            } else if($request->input('category') == 2) {
                $user->category_id = $request->input('category');
                if ($request->input('especialidades') != null) {
                    $user->especialidades = implode(",",$request->input('especialidades'));
                }
                if ($user->category_id != null && app('env') == 'prod') {
                    Mail::to($user->email)->send(new RegistroEvaluador($user,$request->input('password'),$request->header('URL')));
                    Mail::to($soporte)->send(new RegistroEvaluador($user,$request->input('password'),$request->header('URL')));
                }
            }else {
                $user->category_id = $request->input('category');
                Mail::to($user->email)->send(new RegistroAdministrador($user,$request->input('password'),$request->header('URL')));
                Mail::to($soporte)->send(new RegistroAdministrador($user,$request->input('password'),$request->header('URL')));
            }
            $user->save();
            return response()->json(['msg' => 'Usuario registrado con éxito ', 'rpta' => $user,'success' => true], 201);
    	}catch(\Exception $e){

          echo $e->getMessage();
            if ($e->errorInfo[1] == '1062' ) //Para duplicados y 1048 al haber un error en el nombre de la columna a insertar
            {
                $error = 'Error, la cuenta de usuario ya existe';
                return response()->json(['msg' => $error,'success' => false], 201);
            }
            
            Log::info('Error '.$e->getMessage());
            return response()->json(['msg' => 'No se pudo crear el usuario','success' => false], 201);

    	 } 

    }


    public function actualizar($id,Request $request)
    {

        try{
    		$user = User::where('user_id', $id)->first();
            $user->email = $request->input('email');
            $user->name = $request->input('name');
            $user->phone = $request->input('phone');
            $user->genre = $request->input('genre');
            $user->dob = $request->input('dob');
            $user->category = $request->input('category');
            if ($request->input('fecha_inicio') != null) {
                $fecha_inicio= $request->input('fecha_inicio'); //Solo aparecera el campo si en categoria se escoje incubado
                if( $fecha_inicio != null){
                    $user->fecha_inicio = $fecha_inicio;
                }
            }
            if ($request->input('especialidades') != null) {
                $user->especialidades = implode(",",$request->input('especialidades'));
            }

            $user->update();
            return response()->json(['msg' => 'Usuario actualizado con éxito', 'success' => true, 'rpta'=> ''], 201);

    	
    	 }catch(\Exception $e){
            return response()->json(['msg' => 'Error al actualizar datos del usuario '.$e, 'success' => false], 201);
    	}      

    }
    public function listarIncubados(){

      $user = User::join('personas as p','users.persona_id','=','p.person_id')->join('startup as stup','users.start_up_id','=','stup.id')->join('roles as rol','users.rol_id','=','rol.idroles')->select('stup.name as StartUp ','stup.fecha_inicio as "Fecha de inicio"','users.activity','users.email as "Cuenta de usuario"','p.name as Nombre','p.dob as Nacimiento','p.phone as Telefono','rol.idroles')->whereIn('users.activity',[0,1,2])->whereIn('rol.idroles',[3,4,5,6,7])->get();
        
      for ($i=0; $i < $user->count(); $i++){        

            $var = $user[$i]["Nacimiento"];
            $user[$i]["Nacimiento"] = date("d/m/Y", strtotime($var));
            
        }       
        return response()->json(['rpta'=> $user, 'success' => true],200);        
    }


}
