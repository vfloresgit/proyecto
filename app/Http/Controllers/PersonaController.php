<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PersonaController extends Controller
{
    //
     public function registrar(Request $request){
        $founder = new Founder();
        $founder->name = $request->input('name');
        $founder->genre = $request->input('genre');
        $founder->dob = $request->input('dob');
        $founder->edad = $request->input('edad');
        $founder->phone = $request->input('phone');
        $founder->email = $request->input('email');
        
        
        $startup = new StartUp();
        $startup = StartUp::find($request->header('START-UP-ID'));
        $founder->start_up_id = $startup->start_up_id;

        $user = User::where('user_id',$request->header('USER-ID'))->first();
        $user->paso2 = true;
        $user->pasos = 3;        
        try{
            $user->update();
            $founder->save(); 
            return response()->json(['msg' => 'Fundador registrada con éxito','rpta' => $founder ,'success' => true], 201);
        }catch(\Exception $e){
            return response()->json(['msg' => 'Error al registrar el fundador' ,'success' => true], 201);
        }
        
    }
    public function actualizar($id,Request $request){
        $founder = Founder::where('founder_id', $id)->first();
        $founder->name = $request->input('name');
        $founder->genre = $request->input('genre');
        $founder->dob = $request->input('dob');
        $founder->edad = $request->input('edad');
        $founder->phone = $request->input('phone');
        $founder->email = $request->input('email');
        try{
            $founder->update(); 
            return response()->json(['msg' => 'Fundador actualizado con éxito','rpta' => $founder ,'success' => true], 201);

        } catch(\Exception $e){
            return response()->json(['msg' => 'Error al actualizar el fundador','success' => true], 201);
        }
            }

    public function listarPorStartUp(Request $request){
        $founder = Founder::where('start_up_id',$request->header('START-UP-ID'))->select('founder_id','name','genre','dob','edad','phone','email','start_up_id')->get();
        return response()->json(['msg' => 'Lista de fundadores','rpta' => $founder ,'success' => true], 201);
    }

    public function obtener($id){
        $founder = Founder::where('founder_id',$id)->first();
        $form= Founder::select('name','genre','dob','phone','email','edad')->where('founder_id', $id)->first();        
        return response()->json(['msg' => 'Fundador obtenido con exito','rpta' => $founder,'form'=>$form ,'success' => true], 201);
    }

    public function RegistroPaso2(Request $request) //no se usa
    {
        $arreglo1 = $request->input('fundador');
        $arreglo2 = $request->input('nivel_avance');

        //llena los fundadores
        for($i = 0;$i<count($arreglo1);$i++){
        $founder = new Founder();
        $founder->name = $arreglo1[$i]['name'];
        $founder->genre = $arreglo1[$i]['genre'];
        $founder->dob = $arreglo1[$i]['dob'];
        $founder->phone = $arreglo1[$i]['phone'];
        $founder->email = $arreglo1[$i]['email'];

        $startup = new StartUp();
        $startup = StartUp::find($request->header('START-UP-ID'));
        $founder->start_up_id = $startup->start_up_id;

        $founder->save();
        }

        //llena el nivel de avance
        $nivel = new NivelAvance();
        $nivel->escalamiento=$arreglo2['escalamiento'];
        $nivel->product_min_viable=$arreglo2['product_min_viable'];
        $nivel->product_market_fit=$arreglo2['product_market_fit'];

        $startup = new StartUp();
        $startup = StartUp::find($request->header('START-UP-ID'));
        $nivel->start_up_id = $startup->start_up_id;

        $nivel->save();

        //cambiar paso2 en usuario a TRUE
        
        $user = User::where('user_id',$request->header('USER-ID'))->first();
        $user->paso2 = true;
        $user->pasos = 3;
        $user->update();

        return response()->json(['msg' => 'Paso 2 Completado', 'success' => true], 201);
        
    }
    
}
