<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StartUpController extends Controller
{
    //
    public function finalizoRegistroIndicadores($id, Request $request){
        $startUp = StartUp::where('start_up_id',$id)->first();
        $evaluadores = ComiteStartUp::where('start_up_id',$id)->get();
        $evaluadoresid = $evaluadores->pluck('user_id');
        $evaluadores = User::whereIn('user_id',$evaluadoresid)->get();
        $fecha = Month::where('month_id',$request->header('MONTH-ID'))->first();
        try{
            if (app('env') == 'prod') {
                foreach ($evaluadores as $evaluador) {
                    Mail::to($evaluador->email)->send(
                        new FinalizarMes(
                        $evaluador,
                        $startUp,
                        'Indicadores',
                        $fecha->month_name.' de '.$fecha->year,
                        $request->header('URL')
                        )
                    );
                }
            }
            return response()->json(['msg' => 'Registro de Indicadores Finalizado', 'success' => true, 'rpta'=>''], 201);
        } catch(\Exception $e){
            return response()->json(['msg' => 'Error al finalizar registro de indicadores '.$e, 'success' => false], 201);
        }
    }

    public function finalizoRegistroEvaluaciones($id, Request $request){
        $startUp = StartUp::where('start_up_id',$id)->first();
        $evaluadores = ComiteStartUp::where('start_up_id',$id)->get();
        $evaluadoresid = $evaluadores->pluck('user_id');
        $evaluadores = User::whereIn('user_id',$evaluadoresid)->get();
        $fecha = Month::where('month_id',$request->header('MONTH-ID'))->first();
        
        try{
            if (app('env') == 'prod') {
                foreach ($evaluadores as $evaluador) {
                    Mail::to($evaluador->email)->send(
                        new FinalizarMes(
                    $evaluador,
                        $startUp,
                        'Evaluaciones',
                        $fecha->month_name.' de '.$fecha->year,
                        $request->header('URL')));
                }
            }
            return response()->json(['msg' => 'Registro de Evaluaciones Finalizado', 'success' => true, 'rpta'=>''], 201);
        } catch(\Exception $e){
            return response()->json(['msg' => 'Error al finalizar registro de evaluaciones', 'success' => false], 201);
        }

    }

    public function registrar(Request $request)
    {
        try{
            $startUp = new StartUp();
            $startUp->name = $request->input('name');
            $startUp->foundation_year = $request->input('foundation_year');
            $startUp->email = $request->input('email');
            $startUp->phone = $request->input('phone');
            $startUp->web_page = $request->input('web_page');
            $startUp->industry_sector = $request->input('industry_sector');
            $startUp->especificar = $request->input('especificar');
            $startUp->product_type = $request->input('product_type');
            $startUp->product_details = $request->input('product_details');
            $startUp->region = $request->input('region');
            $startUp->province = $request->input('province');
            $startUp->district = $request->input('district');
        /*$startUp->region_name = $request->input('region.name');
        $startUp->province_name = $request->input('province.name');
        $startUp->district_name = $request->input('district.name');*/

        $startUp->save();
        
        $user = new User();
        $user = User::find($request->header('USER-ID'));
        $user->pasos = 2;
        $user->paso1 = true;
        $user->start_up_id = $startUp->start_up_id;
        $user->save();

        return response()->json(['msg' => 'Start Up registrada con éxito', 'success' => true, 'rpta' => $startUp], 201);
    } catch(\Exception $e){
        return response()->json(['msg' => 'Error al registrar la Start Up', 'success' => false], 201);
    }

}

public function actualizar($id,Request $request)
{
    $startUp = StartUp::where('start_up_id', $id)->first();
    $startUp->name = $request->input('name');
    $startUp->foundation_year = $request->input('foundation_year');
    $startUp->email = $request->input('email');
    $startUp->phone = $request->input('phone');
    $startUp->web_page = $request->input('web_page');
    $startUp->industry_sector = $request->input('industry_sector');
    $startUp->especificar = $request->input('especificar');
    $startUp->product_type = $request->input('product_type');
    $startUp->product_details = $request->input('product_details');
    $startUp->region = $request->input('region');
    $startUp->province = $request->input('province');
    $startUp->district = $request->input('district');
        /*$startUp->region_name = $request->input('region.name');
        $startUp->province_name = $request->input('province.name');
        $startUp->district_name = $request->input('district.name');*/
        $startUp->category = $request->input('category');

        try{
            $startUp->update();
            return response()->json(['msg' => 'Start Up actualizado con éxito' ,'success' => true, 'rpta' => $startUp ], 201);
        }catch(\Exception $e){
            return response()->json(['msg' => 'Error al actualizar datos de la Start Up' ,'success' => false], 201);
        }
        
    }

    public function llenarStartUp($startUp){
        if ($startUp != null) {
            return 
            array(
                    array('CODIGO DE REGISTRO DE LA STARTUP','UP'.$startUp['start_up_id']),
                    array('',''),//Salto de linea
                    array('Nombre',$startUp['name']),
                    array('Fase', $startUp['Fase']),
                    array('Año de Fundación',$startUp['Año de Fundación']),
                    array('Correo Electrónico',$startUp['Correo Electrónico']),
                    array('Teléfono',$startUp['Teléfono']),
                    array('Página Web',$startUp['Página Web']),
                    array('Industria/Sector',$startUp['Sector Industrial']),
                    array('Producto/Tipo',$startUp['Tipo de Producto']),
                    array('Detalles del producto:',$startUp['Detalles del producto']),
                    array('','')
            ); 
        } else {
            return null;
        }
    }

    public function llenarNivelAvance($registroInicial,$startUp){
        $nivelAvance = NivelAvance::where('start_up_id',$startUp->start_up_id)->where('fase',$startUp['Fase'])->first();
        if ($nivelAvance != null) {
            $registroInicial[] = array('Desarrollo del Negocio: Nivel de Avance');
            $registroInicial[] = array('MVP(Producto Mínimo Viable)','Product/market','Escalamiento');
            if ($nivelAvance->escalamiento == 1) {
                // $registroInicial = array_add($registroInicial,sizeof($registroInicial),array('Nivel de Avance:','Escalamiento'));
                // $registroInicial[] = array('Prueba Escalamiento:','Pudo concatenar');
                $registroInicial[] = array('','','X');
            } elseif ($nivelAvance->product_min_viable == 1) {
                // $registroInicial = array_add($registroInicial,sizeof($registroInicial),array('Nivel de Avance:','Producto Mínimo Viable'));
                // $registroInicial[] = array('Prueba Product:','Pudo concatenar');
                $registroInicial[] = array('X','','');
            } else {
                // $registroInicial = array_add($registroInicial,sizeof($registroInicial),array('Nivel de Avance:','Product Market Fit'));
                $registroInicial[] = array('','X','');
            }
        } else {
            $registroInicial[] = array('Nivel de Avance:','','Sin Registrar');
        }
        $registroInicial[] = array('','');
        return $registroInicial;
    }

    public function llenarFundadores($registroInicial,$startUp){
        $founders = Founder::select(
            'name as Nombre',
            'genre as Género',
            'email as Correo Electrónico',
            'phone as Teléfono',
            'dob as Fecha de nacimiento'
        )->where('start_up_id',$startUp['start_up_id'])->get();
        if (sizeof($founders) > 0) {
            // echo 'Fundadores:'.sizeof($founders);
            $registroInicial[] = array('Fundadores','',sizeof($founders));
            $nombresFundadores = array('Nombre');
            $generosFundadores = array('Género');
            $emailsFundadores = array('Correo Electrónico');
            $phoneFundadores = array('Teléfono');
            $dobFundadores = [];
            $dobFundadores[] = 'Fecha de nacimiento';
            foreach ($founders as $founder) {
                $nombresFundadores[] = $founder['Nombre'];
                $generosFundadores[] = $founder['Género'];
                $emailsFundadores[] = $founder['Correo Electrónico'];
                $phoneFundadores[] = $founder['Teléfono'];
                $dobFundadores[] = $founder['Fecha de nacimiento'];
            }
            $registroInicial[] = $nombresFundadores;
            $registroInicial[] = $generosFundadores;
            $registroInicial[] = $emailsFundadores;
            $registroInicial[] = $phoneFundadores;
            $registroInicial[] = $dobFundadores;
        } else {
            $registroInicial[] = array('Fundadores','','Fundadores no registrados');
        }
        $registroInicial[] = array('','');
        $cantidad = sizeof($founders);
        return [$registroInicial,$cantidad];
    }

    public function llenarRendimientoEconomico($registroInicial,$startUp){
        $rendimientoInicial = RendimientoEconomicoMonto::where('start_up_id',$startUp->start_up_id)->where('fase',1)->first();
        if ($rendimientoInicial != null) {
            $registroInicial[] = array('Rendimiento Económico');
            $registroInicial[] = array('Facturación últimos 6 meses sin IGV(Soles)');
            $registroInicial[] = array('Mes','Facturación');
            $registroInicial[] = array(
                $rendimientoInicial->month.'/'.$rendimientoInicial->year, $rendimientoInicial->facturacion);
            $registroInicial[] = array(
                $rendimientoInicial->month2.'/'.$rendimientoInicial->year2,$rendimientoInicial->facturacion2);
            $registroInicial[] = array(
                $rendimientoInicial->month3.'/'.$rendimientoInicial->year3,$rendimientoInicial->facturacion3);
            $registroInicial[] = array(
                $rendimientoInicial->month4.'/'.$rendimientoInicial->year4,$rendimientoInicial->facturacion4);
            $registroInicial[] = array(
                $rendimientoInicial->month5.'/'.$rendimientoInicial->year5,$rendimientoInicial->facturacion5);
            $registroInicial[] = array(
                $rendimientoInicial->month6.'/'.$rendimientoInicial->year6,$rendimientoInicial->facturacion6);
            
            $registroInicial[] = array('Ha logrado su punto de equilibrio?');
            if ($rendimientoInicial->punto_equilibrio == 0) {
                $registroInicial[] = array('NO');
            } else {
                $registroInicial[] = array('SI');
            }
            $registroInicial[] = array('Fondos %');
            $registroInicial[] = array('Propios',$rendimientoInicial->fondos_propios.'%');
            $registroInicial[] = array('Inversionista(s)',$rendimientoInicial->fondos_inversionistas.'%');
            $registroInicial[] = array('No reembolsables (premios)',$rendimientoInicial->fondos_inversionistas.'%');
            $registroInicial[] = array('Créditos',$rendimientoInicial->fondos_inversionistas.'%');
            $registroInicial[] = array('Inversiones Recibidas');
            $registroInicial[] = array('Inversiones privada (Soles)', $rendimientoInicial->inversion_privada);
            $registroInicial[] = array('Fondos concursables (Soles)', $rendimientoInicial->inversion_fondos_concursables);
            $registroInicial[] = array('Otros (Soles)', $rendimientoInicial->inversion_otros);
        } else {
            $registroInicial[] = array('Rendimiento Económico:','Sin registrar');
        }
        $registroInicial[] = array('','');
        return $registroInicial;
    }

    public function llenarEmpleados($registroInicial,$startUp){
        $empleados = EmpleoCreadoEmpleado::where('start_up_id',$startUp->start_up_id)->where('fase',1)->first();
                    $fundadores = EmpleoCreadoFundador::where('start_up_id',$startUp->start_up_id)->where('fase',1)->first();
                    $freelancers = EmpleoCreadoFreelancer::where('start_up_id',$startUp->start_up_id)->where('fase',1)->first();
                    $registroInicial[] = array('Empleos Creados');
                    $registroInicial[] = array('Fundadores','','Empleados','','Freelancers','');
                    if ( $fundadores != null && $empleados != null && $freelancers != null) {
                        
                        $registroInicial[] = array('Hombres tiempo completo (planilla)',$fundadores->hombres_tiempo_completo_planilla,'Hombres tiempo completo (planilla)',$empleados->hombres_tiempo_completo_planilla, 'Hombres tiempo parcial',$freelancers->hombres_tiempo_parcial);
                        $registroInicial[] = array('Mujeres tiempo completo (planilla)',$fundadores->mujeres_tiempo_completo,'Mujeres tiempo completo (planilla)',$empleados->mujeres_tiempo_completo, 'Porcentaje de tiempo que dedican en promedio los hombres que estan a tiempo parcial',$freelancers->porcentaje_hombres_tiempo_parcial);
                        $registroInicial[] = array('Hombres tiempo completo (recibo)',$fundadores->hombres_tiempo_completo_recibo,'Hombres tiempo completo (recibo)',$empleados->hombres_tiempo_completo_recibo, 'Hombres tiempo equivalente (FTE)',$freelancers->hombres_tiempo_completo_equivalente);
                        $registroInicial[] = array('Mujeres tiempo completo (recibo)',$fundadores->mujeres_tiempo_completo_recibo,'Mujeres tiempo completo (recibo)',$empleados->mujeres_tiempo_completo_recibo, 'Mujeres tiempo parcial',$freelancers->mujeres_tiempo_parcial);
                        $registroInicial[] = array('Hombres tiempo parcial ',$fundadores->hombres_tiempo_parcial,'Hombres tiempo parcial ',$empleados->hombres_tiempo_parcial, 'Porcentaje de tiempo que dedican en promedio las mujeres que estan a tiempo parcial',$freelancers->porcentaje_mujeres_tiempo_parcial);
                        $registroInicial[] = array('Porcentaje de tiempo que dedican en promedio los hombres que estan a tiempo parcial',$fundadores->porcentaje_hombres_tiempo_parcial,'Porcentaje de tiempo que dedican en promedio los hombres que estan a tiempo parcial',$empleados->porcentaje_hombres_tiempo_parcial, 'Mujeres tiempo equivalente (FTE)',$freelancers->mujeres_tiempo_completo_equivalente);
                        $registroInicial[] = array('Hombres tiempo equivalente (FTE)',$fundadores->hombres_tiempo_completo_equivalente,'Hombres tiempo equivalente (FTE)',$empleados->hombres_tiempo_completo_equivalente);
                        $registroInicial[] = array('Mujeres tiempo parcial',$fundadores->mujeres_tiempo_parcial,'Mujeres tiempo parcial',$empleados->mujeres_tiempo_parcial);
                        $registroInicial[] = array('Porcentaje de tiempo que dedican en promedio las mujeres que estan a tiempo parcial',$fundadores->porcentaje_mujeres_tiempo_parcial,'Porcentaje de tiempo que dedican en promedio las mujeres que estan a tiempo parcial',$empleados->porcentaje_mujeres_tiempo_parcial);
                        $registroInicial[] = array('Mujeres tiempo equivalente (FTE)',$fundadores->mujeres_tiempo_completo_equivalente,'Mujeres tiempo equivalente (FTE)',$empleados->mujeres_tiempo_completo_equivalente);
                    } else {
                        $empleosCreados = [];
                        if ($empleados == null) {
                            $empleosCreados[] = 'Sin registros';
                        }
                        if ($fundadores == null) {
                            $empleosCreados[] = 'Sin registros';
                        }
                        if ($freelancers == null) {
                            $empleosCreados[] = 'Sin registros';
                        }
                        $registroInicial[] = $empleosCreados;
                    }
                    $registroInicial[] = array('');
        return $registroInicial;
    }

    public function llenarVision($registroInicial,$startUp){
        $vision = Vision::where('start_up_id',$startUp->start_up_id)->where('fase',1)->first();
                    if ($vision != null) {
                        $registroInicial[] = array('Vision','',$vision->vision);
                    } else {
                        $registroInicial[] = array('Vision','','Sin registrar');
                    }
                    $registroInicial[] = array('');
        return $registroInicial;
    }

    public function llenarObjetivos($registroInicial,$startUp){
        $objetivos = Objetivo::where('start_up_id',$startUp->start_up_id)->where('fase',1)->get();
                    if ($objetivos != null) {
                        $objetivos_detalle = [];
                        $objetivos_detalle[] = 'Objetivos';
                        $objetivos_detalle[] = '';
                        $objetivos_n = $objetivos->pluck('objetivo');
                        foreach ($objetivos_n as $objetivo) {
                            $objetivos_detalle[] = $objetivo;
                        }
                        $registroInicial[] = $objetivos_detalle;
                    } else {
                        $registroInicial[] = array('Objetivos aún sin registrar');
                    }
                    $registroInicial[] = array('');
        return [$registroInicial, sizeof($objetivos)];
    }

    public function llenarRetos($registroInicial,$startUp){
        $retos = Reto::where('start_up_id',$startUp->start_up_id)->where('fase',1)->get();
                    if ($retos != null) {
                        $retos_detalle = [];
                        $retos_detalle[] = 'Retos';
                        $retos_detalle[] = '';
                        $retos_n = $retos->pluck('reto');
                        foreach ($retos_n as $reto) {
                            $retos_detalle[] = $reto;
                        }
                        $registroInicial[] = $retos_detalle;
                    } else {
                        $registroInicial[] = array('Retos aún sin registrar');
                    }
                    $registroInicial[] = array('');
        return [$registroInicial, sizeof($retos)];
    }

    public function exportAll(Request $request){
        $startUp = StartUp::select(
            'name',
            'foundation_year as Año de Fundación',
            'email as Correo Electrónico',
            'phone as Teléfono',
            'web_page as Página Web',
            'industry_sector as Sector Industrial',
            'especificar',
            'product_type as Tipo de Producto',
            'product_details as Detalles del producto',
            'region as Región',
            'province as Provincia',
            'district as Distrito',
            'tiempo as Meses a Evaluar',
            'fase as Fase'
        )->where('Meses a Evaluar','>',0)->get();

        $startUp = StartUp::select(
            'name',
            'foundation_year as Año de Fundación',
            'email as Correo Electrónico',
            'phone as Teléfono',
            'web_page as Página Web',
            'industry_sector as Sector Industrial',
            'especificar',
            'product_type as Tipo de Producto',
            'product_details as Detalles del producto',
            'region as Región',
            'province as Provincia',
            'district as Distrito',
            'tiempo as Meses a Evaluar',
            'fase as Fase'
        )->where('start_up_id',$id)->first();
       
        $hoy = getDate();
        Excel::create('Startup '.$startUp['name'].'-'.$hoy[0], function($excel) use ($startUp){
            // $startUp = StartUp::where('start_up_id',$id)->first();
            // Set the title
            $excel->setTitle('Our new awesome title');
        
            // Chain the setters
            $excel->setCreator('Maatwebsite')
                  ->setCompany('Maatwebsite');
        
            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');

            $excel->sheet('StarUp '.$startUp['name'], function($sheet)  use ($startUp) {
                // $startUp = $startUp->;
                $distrito = Distrito::where('id',$startUp['Distrito'])->first();
                $provincia = Provincia::where('id',$startUp['Provincia'])->first();
                $departamento = Departamento::where('id',$startUp['Región'])->first();
                $startUp['Distrito'] = $distrito['nombre'];
                $startUp['Provincia'] = $provincia['nombre'];
                $startUp['Región'] = $departamento['nombre'];
                if($startUp['Sector Industrial'] == 'Otros: especificar'){
                    $startUp['Sector Industrial'] = $startUp['especificar'];
                }
                if($startUp['Meses a Evaluar'] == '' ||  is_null($startUp['Meses a Evaluar'])){
                    $startUp['Meses a Evaluar'] = 0;
                }
                $data = array(
                    array('Startup:',$startUp['name']),
                    array('Año de Fundación:',$startUp['Año de Fundación']),
                    array('Correo Electrónico:',$startUp['Correo Electrónico']),
                    array('Teléfono:',$startUp['Teléfono']),
                    array('Página Web:',$startUp['Página Web']),
                    array('Sector Industrial:',$startUp['Sector Industrial']),
                    array('Tipo de Producto:',$startUp['Tipo de Producto']),
                    array('Detalles del producto:',$startUp['Detalles del producto']),
                    array('Región:',$startUp['Región']),
                    array('Provincia:',$startUp['Provincia']),
                    array('Distrito:',$startUp['Distrito']),
                    array('Meses a Evaluar:',$startUp['Meses a Evaluar']),
                    array('Fase:',$startUp['Fase'])
                );
                $headersStartUp = array(
                    array('Startup:',$startUp['name'])
                );
                // Sheet manipulation
                // $sheet->fromArray($headersStartUp,'','A1');
                $sheet->fromArray($data, '', 'A2');
            });
        
        })->store('xls');

        Excel::create('Startup 2 '.$startUp['name'].'-'.$hoy[0], function($excel) use ($startUp){
            // $startUp = StartUp::where('start_up_id',$id)->first();
            // Set the title
            $excel->setTitle('Our new awesome title');
        
            // Chain the setters
            $excel->setCreator('Maatwebsite')
                  ->setCompany('Maatwebsite');
        
            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');

            $excel->sheet('StarUp '.$startUp['name'], function($sheet)  use ($startUp) {
                // $startUp = $startUp->;
                $distrito = Distrito::where('id',$startUp['Distrito'])->first();
                $provincia = Provincia::where('id',$startUp['Provincia'])->first();
                $departamento = Departamento::where('id',$startUp['Región'])->first();
                $startUp['Distrito'] = $distrito['nombre'];
                $startUp['Provincia'] = $provincia['nombre'];
                $startUp['Región'] = $departamento['nombre'];
                if($startUp['Sector Industrial'] == 'Otros: especificar'){
                    $startUp['Sector Industrial'] = $startUp['especificar'];
                }
                if($startUp['Meses a Evaluar'] == '' ||  is_null($startUp['Meses a Evaluar'])){
                    $startUp['Meses a Evaluar'] = 0;
                }
                $data = array(
                    array('Startup:',$startUp['name']),
                    array('Año de Fundación:',$startUp['Año de Fundación']),
                    array('Correo Electrónico:',$startUp['Correo Electrónico']),
                    array('Teléfono:',$startUp['Teléfono']),
                    array('Página Web:',$startUp['Página Web']),
                    array('Sector Industrial:',$startUp['Sector Industrial']),
                    array('Tipo de Producto:',$startUp['Tipo de Producto']),
                    array('Detalles del producto:',$startUp['Detalles del producto']),
                    array('Región:',$startUp['Región']),
                    array('Provincia:',$startUp['Provincia']),
                    array('Distrito:',$startUp['Distrito']),
                    array('Meses a Evaluar:',$startUp['Meses a Evaluar']),
                    array('Fase:',$startUp['Fase'])
                );
                $headersStartUp = array(
                    array('Startup:',$startUp['name'])
                );
                // Sheet manipulation
                // $sheet->fromArray($headersStartUp,'','A1');
                $sheet->fromArray($data, '', 'A2');
            });
        
        })->store('xls');
        unlink(public_path('test.zip'));
        $zipper = new Zipper();
        $zipper->make('test.zip');
        $files = glob(storage_path('exports')); 
        $zipper->add($files);
        $zipper->close();
        $ficherosEliminados = 0;
        $carpeta = $files[0].'\\';
        // echo 'Files:'.$files[0];
        $files = scandir($carpeta);
        foreach ($files as $file) {
            // echo 'File:'.$file;
            if(is_file($carpeta.$file)) // si se trata de un archivo
             {
                 // echo 'Es Archivo';
                if (unlink($carpeta.$file)){
                    // echo 'Fue eliminado el archivo';
                    $ficherosEliminados++;
                  }
             }   
        }
        // return response()->json(['msg' => 'Start Up actualizado con éxito' ,'success' => true, 'rpta' => $startUp ], 201);
        return response()->download(public_path('test.zip'));
        // $zip = Zipper::make('test.zip')->add($files)->close();
        // return dd($zipper);
        /* \Zipper::make(public_path('test.zip'))->add($files)->close();
        return response()->download(public_path('test.zip'));*/
    }

    public function import(Request $request)
    {
        /*if($request->file('imported-file'))
        {
            $path = $request->file('imported-file')->getRealPath();
            $data = Excel::load($path, function($reader) {})->get();
              if(!empty($data) && $data->count()){
                $data = $data->toArray();
                for($i=0;$i<count($data);$i++){
                    $dataImported[] = $data[$i];
                }
              }
            // Item::insert($dataImported);

        }
        return back();*/
    }

    public function exportById($id){
        $startUp = StartUp::select(
            'start_up_id',
            'name',
            'foundation_year as Año de Fundación',
            'email as Correo Electrónico',
            'phone as Teléfono',
            'web_page as Página Web',
            'industry_sector as Sector Industrial',
            'especificar',
            'product_type as Tipo de Producto',
            'product_details as Detalles del producto',
            'region as Región',
            'province as Provincia',
            'district as Distrito',
            'tiempo as Meses a Evaluar',
            'fase as Fase'
        )->where('start_up_id',$id)->first();
        // echo $startUp;
        $incubado = User::where('start_up_id',$id)->first();
        $hoy = getDate();
        if ($incubado['pasos'] == 6) {
            Excel::create('Startup '.$startUp['name'].'-'.$hoy[0], function($excel) use ($startUp){
                $excel->setTitle('Our new awesome title');
            
                // Chain the setters
                $excel->setCreator('Maatwebsite')
                      ->setCompany('Maatwebsite');
            
                // Call them separately
                $excel->setDescription('A demonstration to change the file properties');
                $excel->sheet('Registro Inicial', function($sheet)  use ($startUp) {
                    if($startUp['Sector Industrial'] == 'Otros: especificar'){
                        $startUp['Sector Industrial'] = $startUp['especificar'];
                    }
                    if($startUp['Meses a Evaluar'] == '' ||  is_null($startUp['Meses a Evaluar'])){
                        $startUp['Meses a Evaluar'] = 0;
                    }
                    $posicionInicio = 2;
                    $posicionInicioFin = 3;
                    $registroInicial = $this->llenarStartUp($startUp);
                    if ($registroInicial != null) {
                        // echo 'Debe Registrar primero startup';
                        $posicionStartUp = $posicionInicioFin + 1;
                        $posicionStartUpFin = $posicionStartUp + 9;
                        $posicionNivelAvance = $posicionStartUpFin + 1;
                        $posicionNivelAvanceFin = $posicionNivelAvance + 1;
                        $registroInicial = $this->llenarNivelAvance($registroInicial,$startUp);
                        if ($registroInicial[sizeof($registroInicial) - 2][2] != 'Sin Registrar') {
                            $posicionFundadores = $posicionNivelAvanceFin + 3;
                        } else {
                            $posicionFundadores = $posicionNivelAvanceFin + 1;
                        }
                        $posicionFundadoresFin = $posicionFundadores + 1;
                        $posicionFundadoresContenido = $posicionFundadoresFin;
                        $fundadores = $this->llenarFundadores($registroInicial,$startUp);
                        $registroInicial = $fundadores[0];
                        $cantFund = $fundadores[1];
                        if ($registroInicial[sizeof($registroInicial) - 2][2] != 'Fundadores no registrados') {
                            $posicionFundadoresContenidoFin = $posicionFundadoresContenido + 5;
                        } else {
                            $posicionFundadoresContenido = $posicionFundadores;
                            $posicionFundadoresContenidoFin = $posicionFundadoresFin;
                        }
                        $posicionRendimientoEconomico = $posicionFundadoresContenidoFin + 1;
                        $registroInicial = $this->llenarRendimientoEconomico($registroInicial,$startUp);
                        if ($registroInicial[sizeof($registroInicial) - 2][1] != 'Sin registrar') {
                            $posicionRendimientoEconomicoFin = $posicionRendimientoEconomico + 2;
                            $posicionPuntoEquilibrio = $posicionRendimientoEconomicoFin + 7;
                            $posicionPuntoEquilibrioFin = $posicionPuntoEquilibrio + 1; 
                            $posicionFondos = $posicionPuntoEquilibrioFin + 1;
                            $posicionFondosFin = $posicionFondos + 9;
                        } else {
                            $posicionRendimientoEconomicoFin = $posicionRendimientoEconomico + 1;
                            $posicionPuntoEquilibrio = $posicionRendimientoEconomico;
                            $posicionPuntoEquilibrioFin = $posicionRendimientoEconomicoFin;
                            $posicionFondos = $posicionRendimientoEconomico;
                            $posicionFondosFin = $posicionRendimientoEconomicoFin;
                        }
                        $posicionEmpleadosTitulo = $posicionFondosFin + 1;
                        $posicionEmpleadosTituloFin = $posicionEmpleadosTitulo + 1;
                        $posicionEmpleadosCategorias = $posicionEmpleadosTituloFin;
                        $posicionEmpleadosCategoriasFin = $posicionEmpleadosCategorias + 1;
                        $registroInicial = $this->llenarEmpleados($registroInicial,$startUp);
                        if ($registroInicial[sizeof($registroInicial) - 2][0] != 'Sin registros') {
                            $posicionEmpleadosContenido = $posicionEmpleadosCategoriasFin; 
                            $posicionEmpleadosContenidoFin = $posicionEmpleadosContenido + 10;
                        } else {
                            $posicionEmpleadosContenido = $posicionEmpleadosCategorias;
                            $posicionEmpleadosContenidoFin = $posicionEmpleadosCategoriasFin;
                        }
                        $posicionVision = $posicionEmpleadosContenidoFin + 1;
                        $registroInicial = $this->llenarVision($registroInicial,$startUp);
                        $posicionVisionFin = $posicionVision + 1;
                        $posicionObjetivos = $posicionVisionFin + 1;
                        $objetivos = $this->llenarObjetivos($registroInicial,$startUp);
                        $registroInicial = $objetivos[0];
                        $cantObj = $objetivos[1];
                        $posicionObjetivosFin = $posicionObjetivos + 1;
                        $posicionRetos = $posicionObjetivosFin + 1;
                        $retos = $this->llenarRetos($registroInicial,$startUp);
                        $registroInicial = $retos[0];
                        $cantRetos = $retos[1];
                        $posicionRetosFin = $posicionRetos + 1;
                        $sheet->fromArray($registroInicial,'0','A1');
    
                    $sheet->cell('C2', function($cell) use ($startUp){
                        // manipulate the cell
                        $cell->setValue('UP'.$startUp['start_up_id']);
                        $cell->setBorder('thin','thin','thin','thin');
                    });
                    $celdasMergeadas = array(
                        $posicionInicio,
                        $posicionNivelAvance,
                        $posicionFundadores,
                        $posicionRendimientoEconomico,
                        $posicionRendimientoEconomico + 1,
                        $posicionPuntoEquilibrio,
                        $posicionEmpleadosTitulo,
                        $posicionEmpleadosCategorias,
                        $posicionVision,
                        $posicionObjetivos,
                        $posicionRetos);
                    foreach ($celdasMergeadas as $celda) {
                        if($celda != $posicionFundadores){
                            $sheet->mergeCells('A'.$celda.':B'.$celda);
                        }
                        $sheet->mergeCells('A'.$celda.':B'.$celda);
                        $sheet->cell('A'.$celda, function($cell) use ($celda,$posicionEmpleadosCategorias) {
                            // manipulate the cell
                            if($celda == $posicionEmpleadosCategorias){
                                $cell->setAlignment('center');
                            }
                            $cell->setFont(array(
                                'family'     => 'Calibri',
                                'size'       => '16'
                            ));
                        });
                        if($celda == $posicionEmpleadosCategorias){
                            $sheet->mergeCells('C'.$celda.':D'.$celda);
                            $sheet->cell('C'.$celda, function($cell) {
                            // manipulate the cell
                            $cell->setAlignment('center');
                                $cell->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '16'
                                ));
                            });
                            $sheet->mergeCells('E'.$celda.':F'.$celda);
                            $sheet->cell('E'.$celda, function($cell) {
                            // manipulate the cell
                            $cell->setAlignment('center');
                                $cell->setFont(array(
                                    'family'     => 'Calibri',
                                    'size'       => '16'
                                ));
                            });
                        }
                    }
    
                    $rangosParaColorear = array(
                        array($posicionInicio, $posicionInicioFin), // inicio $posicionInicio, $posicionInicioFin
                        array($posicionStartUp, $posicionStartUpFin), // start up $posicionStartUp, $posicionStartUpFin
                        array($posicionNivelAvance, $posicionNivelAvanceFin), // nivel de avance $posicionNivelAvance, $posicionNivelAvanceFin
                        array($posicionNivelAvanceFin, $posicionNivelAvanceFin + 1),
                        array($posicionNivelAvanceFin + 1, $posicionNivelAvanceFin + 2),
                        array($posicionFundadores,$posicionFundadoresFin), // fundadores titulo $posicionFundadores,$posicionFundadoresFin
                        array($posicionFundadoresContenido, $posicionFundadoresContenidoFin), // fundadores contenido $posicionFundadoresContenido, $posicionFundadoresContenidoFin
                        array($posicionRendimientoEconomico, $posicionRendimientoEconomicoFin),
                        array($posicionRendimientoEconomicoFin, $posicionRendimientoEconomicoFin + 1),
                        array($posicionRendimientoEconomicoFin + 1, $posicionRendimientoEconomicoFin + 7), // rendimiento economico $posicionRendimientoEconomico, $posicionRendimientoEconomicoFin
                        array($posicionPuntoEquilibrio, $posicionPuntoEquilibrioFin), // punto de equilibrio $posicionPuntoEquilibrio, $posicionPuntoEquilibrioFin
                        array($posicionFondos, $posicionFondosFin),  // fondos $posicionFondos, $posicionFondosFin
                        array($posicionEmpleadosTitulo, $posicionEmpleadosTituloFin), // Empleados titulo $posicionEmpleadosTitulo, $posicionEmpleadosTituloFin
                        array($posicionEmpleadosCategorias, $posicionEmpleadosCategoriasFin),   // Categorias $posicionEmpleadosCategorias, $posicionEmpleadosCategoriasFin
                        array($posicionEmpleadosContenido, $posicionEmpleadosContenidoFin), // contenido Empleados $posicionEmpleadosContenido, $posicionEmpleadosContenidoFin
                        array($posicionVision,$posicionVisionFin),   // vision $posicionVision
                        array($posicionObjetivos,$posicionObjetivosFin),   // objetivos $posicionObjetivos
                        array($posicionRetos,$posicionRetosFin)    // retos    $posicionRetos
                    );
    
                    foreach ($rangosParaColorear as $rangoParaColorear) {
                        for ($i=$rangoParaColorear[0]; $i < $rangoParaColorear[1]; $i++) { 
                            if(
                                $rangoParaColorear[0] != $posicionNivelAvanceFin + 1 &&
                                $rangoParaColorear[0] != $posicionNivelAvanceFin &&
                                $rangoParaColorear[0] != $posicionRendimientoEconomicoFin &&
                                $rangoParaColorear[0] != $posicionRendimientoEconomicoFin + 1
                                ){
                                $sheet->cell('A'.$i, function($color){
                                    $color->setBackground('#BDD6EE');
                                    $color->setAlignment('top');
                                    $color->setBorder('thin','thin','thin','thin');
                                  });
                                if ($rangoParaColorear[0] == $posicionEmpleadosContenido) {
                                    $sheet->cell('C'.$i, function($color){
                                        $color->setBackground('#BDD6EE');
                                        $color->setAlignment('top');
                                        $color->setBorder('thin','thin','thin','thin');
                                      });
                                      if ($i < 54) {
                                        $sheet->cell('E'.$i, function($color){
                                            $color->setBackground('#BDD6EE');
                                            $color->setAlignment('top');
                                            $color->setBorder('thin','thin','thin','thin');
                                          });
                                      }
                                      
                                }
                            } else {
                                $letra = 65; // A
                                $cantSubrayados = 0;
                                if (
                                    $rangoParaColorear[0] == $posicionRendimientoEconomicoFin ||
                                    $rangoParaColorear[0] == $posicionRendimientoEconomicoFin + 1
                                    ) {
                                    $cantSubrayados = 2;
                                } else{
                                    $cantSubrayados = 3;
                                }
                                for ($j=0; $j < $cantSubrayados; $j++) {
                                    $sheet->cell(chr($letra++).''.$i, function($color) use ($rangoParaColorear,$posicionNivelAvanceFin,$posicionRendimientoEconomicoFin){
                                        if ($rangoParaColorear[0] == $posicionNivelAvanceFin || $rangoParaColorear[0] == $posicionRendimientoEconomicoFin) {
                                            $color->setBackground('#BDD6EE');
                                        }
                                        $color->setBorder('thin','thin','thin','thin');
                                    });
                                }
                            }
                            
                            if ($rangoParaColorear[0] == $posicionEmpleadosCategorias) {
                                $sheet->cell('C'.$i, function($color){
                                    $color->setBackground('#BDD6EE');
                                    $color->setAlignment('top');
                                    $color->setBorder('thin','thin','thin','thin');
                                  });
                                $sheet->cell('E'.$i, function($color){
                                    $color->setBackground('#BDD6EE');
                                    $color->setAlignment('top');
                                    $color->setBorder('thin','thin','thin','thin');
                                });
                            }
                            if($rangoParaColorear[0] != $posicionFundadoresContenido){
                                $sheet->cell('B'.$i, function($color){
                                    $color->setBorder('thin','thin','thin','thin');
                                });
                            } else {
                                $letra = 66; // B
                                for ($j=0; $j < $cantFund; $j++) {
                                    $sheet->cell(chr($letra++).''.$i, function($color){
                                        $color->setBorder('thin','thin','thin','thin');
                                    });
                                }
                            }
                            if ($rangoParaColorear[0] == $posicionEmpleadosContenido) {
                                $letra = 66; // B
                                for ($j=0; $j < 5; $j++) {
                                    if ($i < 54) {
                                        $sheet->cell(chr($letra++).''.$i, function($color){
                                            $color->setBorder('thin','thin','thin','thin');
                                        });
                                    } else {
                                        if ($letra < 69) {
                                            $sheet->cell(chr($letra++).''.$i, function($color){
                                                $color->setBorder('thin','thin','thin','thin');
                                            });
                                        }
                                    }
                                    
                                }
                            }
                            if ($rangoParaColorear[0] == $posicionVision) {
                                $sheet->cell('C'.$i, function($color){
                                    $color->setBorder('thin','thin','thin','thin');
                                });
                            }
                            if ($rangoParaColorear[0] == $posicionVision) {
                                $sheet->cell('C'.$i, function($color){
                                    $color->setBorder('thin','thin','thin','thin');
                                });
                            }
                            if ($rangoParaColorear[0] == $posicionObjetivos) {
                                $letra = 67; // B
                                for ($j=0; $j < $cantObj; $j++) {
                                    $sheet->cell(chr($letra++).''.$i, function($color){
                                        $color->setBorder('thin','thin','thin','thin');
                                    });
                                }
                            }
                            if ($rangoParaColorear[0] == $posicionRetos) {
                                $letra = 67; // B
                                for ($j=0; $j < $cantRetos; $j++) {
                                    $sheet->cell(chr($letra++).''.$i, function($color){
                                        $color->setBorder('thin','thin','thin','thin');
                                    });
                                }
                            }
                            if ($rangoParaColorear[0] == $posicionNivelAvance) {
                                # code...
                            }
                            
                        }
                    }
                    }
                    $sheet->setWidth(array(
                        'A'     =>  40,
                        'B'     =>  40,
                        'C'     =>  40,
                        'D'     =>  40,
                        'E'     =>  40,
                        'F'     =>  40,
                        'G'     =>  40,
                    ));
                    $sheet->getStyle('B4:B'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('C4:C'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('D4:D'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('E4:E'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('F4:F'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('G4:G'.$sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    
                });
                /*if (
                    $incubado['fecha_inicio'] != null && 
                    $incubado['fecha_inicio'] != 'NULL'
                    ){
                        $inicio = $incubado['fecha_inicio'];
                        $meses = Month::where('month_id','>','')->limit($startUp['Meses a Evaluar'])->get();
                        for ($i=0; $i < $startUp['Meses a Evaluar']; $i++) {
                            $excel->sheet('Indicadores', function($sheet) use ($startUp){

                            });
                            $excel->sheet('Evaluaciones', function($sheet) use ($startUp){

                            });
                        }
                    }*/
                
                


            })->export('xls');
        } else {
            return response()->json(['msg' => 'No tiene registrado su registro inicial' ,'success' => false], 201);
        }
        

    }


    public function editar($id,Request $request)
    {
        $startUp = StartUp::where('start_up_id', $id)->first();
        $startUp->name = $request->input('name');
        $startUp->foundation_year = $request->input('foundation_year');
        $startUp->email = $request->input('email');
        $startUp->phone = $request->input('phone');
        $startUp->web_page = $request->input('web_page');
        $startUp->industry_sector = $request->input('industry_sector');
        $startUp->especificar = $request->input('especificar');
        $startUp->product_type = $request->input('product_type');
        $startUp->product_details = $request->input('product_details');
        $startUp->region = $request->input('region');
        $startUp->province = $request->input('province');
        $startUp->district = $request->input('district');
        /*$startUp->region_name = $request->input('region.name');
        $startUp->province_name = $request->input('province.name');
        $startUp->district_name = $request->input('district.name');
        $startUp->category = $request->input('category');*/
        
        try{
            $startUp->update();
            return response()->json(['msg' => 'Start Up actualizado con éxito' ,'success' => true, 'rpta' => $startUp], 201);
        }catch(\Exception $e){
            return response()->json(['msg' => 'Error al actualizar datos de la Start Up' ,'success' => false], 201);
        }
    }

    public function cambiarEstado($id,Request $request)
    {
        $startUp = StartUp::where('start_up_id', $id)->first();
        $startUp->activity = $request->input('activity');
        $startUp->update();

        switch ($startUp->activity) {
            case '1':
            return response()->json(['msg' => 'StartUp habilitado', 'success' => true ], 201);
            break;
            
            case '2':
            return response()->json(['msg' => 'StartUp deshabilitado', 'success' => true ], 201);
            break;
            
            case '3':
            return response()->json(['msg' => 'StartUp eliminado', 'success' => true ], 201);
            break;
        }
    }

    public function cambiarCategoria($id,Request $request)
    {
        $startUp = StartUp::where('start_up_id', $id)->first();
        $startUp->category = $request->input('category');
        $startUp->update();

        switch ($startUp->category) {
            case '1':
            return response()->json(['msg' => 'El Start Up ahora es incubada', 'success' => true, 'rpta'=>'' ], 201);
            break;
            
            case '2':
            return response()->json(['msg' => 'El Start Up pasó a ser co-incubada', 'success' => true, 'rpta'=>'' ], 201);
            break;
        }
    }

    public function listar(){
        return response()->json(['msg' => StartUp::select('start_up_id','name as StartUp','foundation_year as Año de fundacion','email as Cuenta de usuario','phone as Telefono','web_page as Pagina web','industry_sector as Sector de industria','product_type as Tipo de producto','product_details as Detalles de producto','region as Region','Province as Provincia','tiempo as Tiempo')->where('activity',1)->get() , 'success' => true], 201);
    }


    public function mostrarEvaluacionesStartUp(){
        //Muestra todas las evaluaciones de todas las start up's
        $startup = StartUp::select('start_up_id','name')->where('activity',1)->get();
        $formEvaluacion = FormularioEvaluacion::select('evaluacion_id','estado_activo','month_month_id','start_up_start_up_id')->where('estado_activo',1)->get();
        $formIndicador = FormularioIndicador::select('indicadores_id','estado_activo','month_month_id','start_up_start_up_id')->where('estado_activo',1)->get();
        
        return response()->json(['msg' => 'Todas las evaluaciones','StartUps' => $startup->groupBy('start_up_id') ,'Formulario Evaluaciones' => $formEvaluacion->groupBy('start_up_start_up_id'),'Formulario Indicadores' => $formIndicador->groupBy('start_up_start_up_id'),'success' => true ], 201);
    }

    public function listaEvaluacionesObjetivoPorStartUp(){
        $resultado = new \Illuminate\Database\Eloquent\Collection;
        $evaluacion = new \Illuminate\Database\Eloquent\Collection;
        $objetivo = new \Illuminate\Database\Eloquent\Collection;

        $startup = StartUp::where('activity',1)->get();
        
        $objetivo =  Objetivo::all();
        $evaluacion = EvaluacionObjetivo::all();
        $resultado = collect($objetivo)->merge($evaluacion);
        return response()->json(['msg' => 'Todas las evaluaciones','StartUps' => $startup->groupBy('start_up_id') ,'Objetivos y sus notas' => $resultado->groupBy('objetivo_id'),'success' => true ], 201);
    }

    /*public function mostrarEvaluacionPorStartUp(Request $request){
        $var = new \Illuminate\Database\Eloquent\Collection;
        $resultado = new \Illuminate\Database\Eloquent\Collection;
        $form = FormularioEvaluacion::where('start_up_start_up_id', $request->header('START-UP-ID'))->get();
        $id = $form->pluck('evaluacion_id');
        foreach ($id as $ids) {
            $evaluacionObjetivo = EvaluacionObjetivo::where('form_evaluacion_id',$ids)->get();
            $evaluacionVision = EvaluacionVision::where('form_evaluacion_id',$ids)->get();
            $var = collect($evaluacionVision)->merge($evaluacionObjetivo);
            $resultado = collect($resultado)->merge($var);

        }
        
        return response()->json(['msg' => 'Evaluaciones de Start Up','rpta' => $resultado->groupBy('form_evaluacion_id'),'success' => true ], 201);

    }*/

    public function obtener($id)
    {
        $startup = StartUp::select(
            'start_up_id',
            'name',
            'foundation_year',
            'email',
            'phone',
            'web_page',
            'industry_sector',
            'product_type',
            'product_details',
            'region',
            'province',
            'district',
            'tiempo',
            'fase')->find($id);
        $msg = 'Startup no encontrada';
        $success = false;
        if ($startup != null) {
            $success = true;
            $msg = 'Startup obtenida con exito';
        }
        return response()->json([
            'success' => $success,
            'rpta' => $startup,
            'msg' => $msg
         ], 201);
    }

    public function obtenerStartUpDeUsuario($id)
    {
        $user = User::where('user_id', $id)->first();
        $startUp = StartUp::where('start_up_id', $user->start_up_id)->first();
        $form= StartUp::select('name','foundation_year','email','phone','web_page','industry_sector','especificar','product_type','product_details','region','province','district')->where('start_up_id', $user->start_up_id)->first();
        return response()->json(['rpta' => $startUp,'form' => $form ,'success' => true ], 201);
    }

    public function obtenerEvaluadoresStartUp(Request $request){
        $comite = ComiteStartUp::select('user_id')->where('estado',1)->where('start_up_id',$request->header('START-UP-ID'))->get();
        $users = User::select('name')->whereIn('user_id',$comite->pluck('user_id'))->get();
        return response()->json(['rpta' => $users,'success' => true ], 200);
    }

    public function obtenerStartUpsYEvaluadores(){
        $startUp = DB::table('start_ups')
        ->join('users',function($join){
            $join->on('users.start_up_id','=','start_ups.start_up_id')
            ->where('users.start_up_id','<>', NULL)
            ->where('users.pasos','=', 6);
        })
        ->select('start_ups.start_up_id as start_up_id','start_ups.name as StartUp','start_ups.tiempo as tiempo')
        ->distinct()
        ->get();
        $array = json_decode($startUp,true);
        //StartUp::select('start_up_id','name as StartUp','tiempo')->where('activity',1)->get();
        
        for ($int=0; $int < sizeof($array); $int++) { 
            $b = $array[$int]['start_up_id'];
            $asignado = ComiteStartUp::where('estado',1)->where('start_up_id',$b)->get();
            $c = $asignado->pluck('user_id');
            
            $collect = User::select('name')->whereIn('user_id',$c)->get();
            $array[$int]['Comite']= $collect->implode('name', ',');
        }
        /*foreach ($a as $b) {
            $asignado = ComiteStartUp::where('estado',1)->where('start_up_id',$b)->get();
            $c = $asignado->pluck('user_id');
            
            $collect = User::select('name')->whereIn('user_id',$c)->get();
            $startUp[$int]['Comite']= $collect->implode('name', ',');
            
            
        }*/

        return response()->json(['msg' => 'Start Up y Evaluadores','rpta' => $array,'success' => true], 201);
        
    }

    public function listarParaComite(Request $request){
        $comite = ComiteStartUp::where('user_id',$request->header('USER-ID'))->get();

        $startUp = StartUp::select('start_up_id','name as StartUp','foundation_year as Año de fundacion','Email as Cuenta de usuario','phone as Teléfono','web_page as Página Web','Activity as Estado')->whereIn('start_up_id',$comite->pluck('start_up_id'))->get();

        for ($i=0; $i < $startUp->count(); $i++) { 
            switch ($startUp[$i]["Estado"] ) {
                case 1:
                $startUp[$i]["Estado"] = "Activo";
                break;
                case 2:
                $startUp[$i]["Estado"] = "Inactivo";
                break;
                case 3:
                $startUp[$i]["Estado"] = "Eliminado";
                break;
            }

        }
        return response()->json(['msg' => 'Lista de Start Ups','rpta' => $startUp,'success' => true], 201);        
    }

    public function listarParaComiteActivos(Request $request){
    //$comite = ComiteStartUp::where('user_id',$request->header('USER-ID'))->get();
        $comite = ComiteStartUp::where('user_id',$request->header('USER-ID'))->where('estado',1)->get();
    //$startUp = StartUp::select('start_up_id','name as StartUp','foundation_year as Año de fundacion','Email as Cuenta de usuario','phone as Teléfono','web_page as Página Web','Activity as Estado')->where('Activity',1)->whereIn('start_up_id',$comite->pluck('start_up_id'))->get();
        $startUp = StartUp::select('start_up_id','name as StartUp','foundation_year as Año de fundacion','region as Cuenta de usuario','email as Correo Electrónico','phone as Teléfono','web_page as Página Web','Activity as Estado','fase')->where('Activity',1)->whereIn('start_up_id',$comite->pluck('start_up_id'))->get();
    /*for ($i=0; $i < $startUp->count(); $i++) { 
        switch ($startUp[$i]["Estado"] ) {
            case 1:
                $startUp[$i]["Estado"] = "Activo";
                break;
            case 2:
                $startUp[$i]["Estado"] = "Inactivo";
                break;
            case 3:
                $startUp[$i]["Estado"] = "Eliminado";
                break;
        }
    } */

    for ($i=0; $i < $startUp->count(); $i++) { 
        $startUp[$i]["Cuenta de usuario"] = User::select('email')->where('start_up_id',  $startUp[$i]["start_up_id"])->first()["email"];
    }

    return response()->json(['msg' => 'Lista de Start Ups activas','rpta' => $startUp,'success' => true], 201);        
}

public function listarParaComiteInactivos(Request $request){
    $comite = ComiteStartUp::where('user_id',$request->header('USER-ID'))->where('estado',1)->get();
    $startUp = StartUp::select('start_up_id','name as StartUp','foundation_year as Año de fundacion','region as Cuenta de usuario','email as Correo Electrónico','phone as Teléfono','web_page as Página Web','Activity as Estado')->where('Activity',2)->whereIn('start_up_id',$comite->pluck('start_up_id'))->get();

    for ($i=0; $i < $startUp->count(); $i++) { 
        /*switch ($startUp[$i]["Estado"] ) {
            case 1:
                $startUp[$i]["Estado"] = "Activo";
                break;
            case 2:
                $startUp[$i]["Estado"] = "Inactivo";
                break;
            case 3:
                $startUp[$i]["Estado"] = "Eliminado";
                break;
            }*/
            $startUp[$i]["Cuenta de usuario"] = User::select('email')->where('start_up_id',  $startUp[$i]["start_up_id"])->first()["email"];

        }
        return response()->json(['msg' => 'Lista de Start Ups Inactivas','rpta' => $startUp,'success' => true], 201);        
    }



    public function tablaEvaluacionesPorStartUp($id){
        $startUp = StartUp::where('start_up_id', $id)->first();
        $objetivo = Objetivo::select('objetivo_id','objetivo')->where('start_up_id',$id)->where('fase',$startUp->fase)->get();
        $listaObjetivo = $objetivo->pluck('objetivo_id');

        $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->whereIn('objetivo_id',$listaObjetivo)->get();
        
        $listaEvaluadores = $evaluacion->pluck('user_int');
        
        //$startUp = StartUp::where('start_up_id',$id)->first(); ya está arriba
        $user = User::where('start_up_id',$id)->first();
        $var = $user->fecha_inicio;
        $date =new DateTime($var);
        if ($date->format('d') <= 15) {
            $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
        } else {
            if($date->format('m') == 12){
                $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
            } else {
                $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
            }
            
        }  
        $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
        $i = 0;
        $j = 0;

        foreach ($listaObjetivo as $a) {

            $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$listaEvaluadores->first())->get();
            foreach ($evaluacion->pluck('month_month_id') as $y) {
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('month_month_id',$y)->first();       
                $x = Month::where('month_id',$y)->first();
                $objetivo[$i][$x->month_name.' '.$x->year]= $evaluacion1->porcentaje;

            }

            foreach ($listaEvaluadores as $b) {
                $user = User::where('user_id',$b)->first();
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$b)->get();
                $objetivo[$i][$user->name] = $evaluacion1->pluck('calificacion')->avg();
            }
            
            $i++;
        }

        return response()->json(['rpta'=>$objetivo ,'success'=>true ],201);
        
    }

    public function tablaUltimasEvaluacionesPorStartUp($id){
        $startUp = StartUp::where('start_up_id', $id)->first();
        $objetivo = Objetivo::select('objetivo_id','objetivo')->where('start_up_id',$id)->where('fase',$startUp->fase)->get();
        $listaObjetivo = $objetivo->pluck('objetivo_id');

        $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->whereIn('objetivo_id',$listaObjetivo)->get();
        
        $listaEvaluadores = $evaluacion->pluck('user_int');
        
        //$startUp = StartUp::where('start_up_id',$id)->first(); ya está arriba
        $user = User::where('start_up_id',$id)->first();
        $var = $user->fecha_inicio;
        $date =new DateTime($var);
        if ($date->format('d') <= 15) {
            $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
        } else {
            if($date->format('m') == 12){
                $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
            } else {
                $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
            }
            
        }  
        $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
        $i = 0;
        $j = 0;

        foreach ($listaObjetivo as $a) {

            $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$listaEvaluadores->first())->get();
            foreach ($evaluacion->pluck('month_month_id') as $y) {
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('month_month_id',$y)->first();       
                $x = Month::where('month_id',$y)->first();
                $objetivo[$i][$x->month_name.' '.$x->year]= $evaluacion1->porcentaje;

            }

            foreach ($listaEvaluadores as $b) {
                $user = User::where('user_id',$b)->first();
                    
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$b)->get();
                // echo $evaluacion1;
                $ultimo = $evaluacion1->where('calificacion','<>',null)->last();
                // echo 'ULTIMO';
                // echo $ultimo;
                if ($ultimo != null) {
                    $objetivo[$i][$user->name] = $ultimo->calificacion;
                } else {
                    // echo  $objetivo[$i];
                    if ($user != null) {
                        $objetivo[$i][$user->name] = 0;
                    }
                    
                }
            }
            
            $i++;
        }

        return response()->json(['rpta'=>$objetivo ,'success'=>true ],201);
        
    }

    public function tablaEvaluacionesComentarioPorStartUp($id,Request $request){
        $objetivo = null;

        $comite = ComiteStartUp::where('start_up_id',$request->header('START-UP-ID'))->where('estado',1)->get();
        $listaEvaluadores = $comite->pluck('user_id');

        $evaluacion = EvaluacionObjetivo::select('comentario','user_int','porcentaje','month_month_id')->where('objetivo_id',$id)->get();
        
        $startUp = StartUp::select('tiempo','created_at')->where('start_up_id',$request->header('START-UP-ID'))->first();
        $user = User::where('start_up_id',$request->header('START-UP-ID'))->first();
        $var = $user->fecha_inicio;
        $date =new DateTime($var);
        if ($date->format('d') <= 15) {
            $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
        } else {
            if($date->format('m') == 12){
                $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
            } else {
                $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
            }
            
        }    
        $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
        $i = 0;

        foreach ($listaEvaluadores as $b) {
            $user = User::where('user_id',$b)->first();
            $objetivo[$i]['Evaluador'] = $user->name;


            $evaluacion1 = EvaluacionObjetivo::where('objetivo_id',$id)->where('month_month_id',$request->header('MONTH-ID'))->where('user_int',$b)->first();       
            $x = Month::where('month_id',$request->header('MONTH-ID'))->first();
            $objetivo[$i][$x->month_name.' '.$x->year]= $evaluacion1->comentario;


            $i++;
        }

        return response()->json(['rpta'=>$objetivo ,'success'=>true ],201);
        
    }

    public function tablaAsignarMeses(){
        
        $rpta = 
        DB::table('start_ups')
        ->join('users',function($join){
            $join->on('users.start_up_id','=','start_ups.start_up_id')
            ->where('users.start_up_id','<>', NULL)
            ->where('users.pasos','=', 6);
        })
        ->join('comite_start_up','comite_start_up.start_up_id','=','start_ups.start_up_id')->where('comite_start_up.estado','=',1)
        ->select('start_ups.start_up_id as start_up_id','start_ups.name as StartUp','start_ups.tiempo as tiempo', 'users.fecha_inicio as Fecha de inicio')
        ->distinct()
        ->get();
        $array = json_decode($rpta,true);
        // $rpta = (array) json_decode($rpta);
        $arregloMeses = Month::select('month_id','month_number','month_name','year')->get();
        // $arregloUsuarios = User::select('user_id','name','start_up_id')->where('category',2)->get();
        $activado = collect([]);
        $meses = collect([]);
        $comite = collect([]);
        $resultado = collect([]);
        // $arregloMeses = Month::select('month_id','month_name','month_number','year')->get();
        //$arregloMeses = 
        $i = 0;

        for ($i=0; $i < sizeof($array); $i++) { 
            $id = $array[$i]['start_up_id'];
            $startUp = StartUp::select('fase','tiempo')->where('start_up_id',$id)->first();
            $nivel = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase)->get();
            // $user_fecha_inicio = $arregloUsuarios->where('start_up_id',$id)->first();//cambiar
            // if ($startUp->tiempo != 0) {
                if($startUp->fase == 1){
                        $nivel->shift();
                }
                /*$activo = $nivel->where('estado_activo',1);
                foreach($activo->pluck('month_month_id') as $a) {
                    $mes = $arregloMeses->where('month_id',$a)->first();
                    $activado->push($mes->month_name.' '.$mes->year);
                }
                $array[$i]["Meses activados"] = $activado->implode(',');*/
                foreach($nivel->pluck('month_month_id') as $a){
                    $mes = $arregloMeses->where('month_id',$a)->first();
                    $meses->push($mes->month_name.' '.$mes->year);
                }

                // $date =new DateTime($array[$i]["Fecha de inicio"]);
                $array[$i]["Fecha de inicio"] = date("d/m/Y", strtotime($array[$i]["Fecha de inicio"]));
                // $meses->shift();
                if(sizeof($meses)>0){
                    $array[$i]["Meses"] = $meses->implode(',');
                } else {
                    $array[$i]["Meses"] = "No hay meses asignados";
                }
                
                /*$com = ComiteStartUp::where('start_up_id',$id)->where('estado',1)->get();
                if($com != null) {
                    foreach($com->pluck('user_id') as $c){
                        $user = $arregloUsuarios->where('user_id',$c)->first();
                        $comite->push($user->name);
                    }
                    $array[$i]["Comite"] = $comite->implode(',');
                } else {
                    $array[$i]["Comite"] = 'No tiene evaluadores asignados';
                }*/
                $resultado->push($array[$i]);
                $comite = collect([]);
                $activado = collect([]);
                $meses = collect([]);
            /*} else{
                $array[$i]["Meses activados"] = "No hay meses asignados";
                $array[$i]["Meses"] = "No hay meses asignados";
            }*/
        }
        return response()->json(['rpta'=>$resultado ,'success'=>true ],201);
    }

    public function obtenerStartUpsYMeses(){ //ya no se usa
        $startUp = StartUp::select('start_up_id','name as StartUp')->where('activity',1)->get();
        $a = $startUp->pluck('start_up_id');//lista de start ups
        $arreglo = collect([]);
        $arregloActivado = collect([]);
        $int = 0;
        foreach ($a as $b) {
        $asignado = ComiteStartUp::where('estado',1)->where('start_up_id',$b)->get(); // evaluador
        $c = $asignado->pluck('user_id'); 

        $collect = User::select('name')->whereIn('user_id',$c)->get(); // evaluador
        if($collect != null){
            if( sizeof($collect) > 0){
                $startUp[$int]['Comite']= $collect->implode('name', ',');
                $startup = StartUp::select('tiempo','created_at')->where('start_up_id',$b)->first();
                $user = User::select('fecha_inicio')->where('start_up_id',$b)->first();
                if($user != null){
                    if( sizeof($user) > 0){
                        $var = $user->fecha_inicio;
                        $date =new DateTime($var);
                        if ($date->format('d') <= 15) {
                            $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
                        } else {
                            if($date->format('m') == 12){
                                $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
                            } else {
                                $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
                            }
                            
                        }       
                        $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startup->tiempo)->get();
                        
                        foreach ($listaMes->pluck('month_id') as $c) {
                            $x = Month::where('month_id',$c)->first();
                            $arreglo->push($x->month_name.' '.$x->year);
                            $sp = StartUp::where('start_up_id',$b)->first();
                            $ment = Mentoria::where('month_month_id',$c)->where('start_up_id',$b)->where('fase',$sp->fase)->first();
                            if($ment != null){
                                if($ment->estado_activo == 1){
                                    $startUp[$int]["month_id"] = $c;
                                    $ment = Month::where('month_id',$a)->first();
                        //$startUp[$int]["Mes activado"] = $x->month_name.' '.$x->year;
                                    $arregloActivado->push($x->month_name.' '.$x->year);
                                }
                            }
                        }

                        $startUp[$int]["Meses activados"] = $arregloActivado->implode(',');
                        $startUp[$int]["Meses"]= $arreglo->implode(',');
                        $var = $user->fecha_inicio;
                        $startUp[$int]["Fecha de inicio"]= date("d/m/Y", strtotime($var));
                        $arreglo = collect([]);
                        $arregloActivado = collect([]);
                        //$int++;
                    }
                }

            }
        }
        $int++;
    }
    if( $int === 0){
        return response()->json(['msg' => 'Start Up y Evaluadores','rpta' => [],'success' => false], 201);
    } else {
        return response()->json(['msg' => 'Start Up y Evaluadores','rpta' => $startUp,'success' => true], 201);
    }

}

public function tablaActivarMeses(){

    $rpta = DB::table('start_ups')
    ->join('users',function($join){
        $join->on('users.start_up_id','=','start_ups.start_up_id')
        ->where('users.start_up_id','<>', NULL)
        ->where('users.pasos','=', 6);
    })
    ->join('comite_start_up','comite_start_up.start_up_id','=','start_ups.start_up_id')->where('comite_start_up.estado','=',1)
    ->select('start_ups.start_up_id as start_up_id','start_ups.name as StartUp','start_ups.tiempo as tiempo', 'users.fecha_inicio as Fecha de inicio')
    ->distinct()
    ->get();
    $array = json_decode($rpta,true);
    $arregloMeses = Month::select('month_id','month_number','month_name','year')->get();
    $arregloUsuarios = User::select('user_id','name','start_up_id')->where('category',2)->get();
    $activado = collect([]);
    $meses = collect([]);
    $comite = collect([]);
    $resultado = collect([]);
        //$startUp = StartUp::select('name','fase','tiempo','cambio_fase')->where('start_up_id',$id)->first();
    for ($i=0; $i < sizeof($array); $i++) { 
        $id = $array[$i]['start_up_id'];
        $startUp = StartUp::select('fase','tiempo')->where('start_up_id',$id)->first();
        $nivel = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase)->limit($startUp->tiempo)->get();
        if ($startUp->tiempo != 0) {
            if($startUp->fase == 1){
                $nivel->shift();
            }
            $activo = $nivel->where('estado_activo',1);
            foreach($activo->pluck('month_month_id') as $a) {
                $mes = $arregloMeses->where('month_id',$a)->first();
                $activado->push($mes->month_name.' '.$mes->year);
            }
            if(sizeof($activado)>0){
                $array[$i]["Meses activados"] = $activado->implode(',');
            } else {
                $array[$i]["Meses activados"] = "No hay meses activados";
            }
            // $array[$i]["Meses activados"] = $activado->implode(',');
            foreach($nivel->pluck('month_month_id') as $a){
                $mes = $arregloMeses->where('month_id',$a)->first();
                $meses->push($mes->month_name.' '.$mes->year);
            }
            if(sizeof($nivel)>0){
                $array[$i]["Meses"] = $meses->implode(',');
            } else {
                $array[$i]["Meses"] = "No hay meses asignados";
            }
            $com = ComiteStartUp::where('start_up_id',$id)->where('estado',1)->get();
            if($com != null) {
                foreach($com->pluck('user_id') as $c){
                    $user = $arregloUsuarios->where('user_id',$c)->first();
                    $comite->push($user->name);
                }
                $array[$i]["Comite"] = $comite->implode(',');
            } else {
                $array[$i]["Comite"] = 'No tiene evaluadores asignados';
            }
            $resultado->push($array[$i]);
            $comite = collect([]);
            $activado = collect([]);
            $meses = collect([]);
        } else{
            $array[$i]["Meses activados"] = "No hay meses asignados";
            $array[$i]["Meses"] = "No hay meses asignados";
        }
    }
        /*foreach($rpta->pluck('start_up_id') as $id){
        $startUp = StartUp::select('fase','tiempo')->where('start_up_id',$id)->first();
        $nivel = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase)->get();
        if ($startUp->tiempo != 0)
        {
        if($startUp->fase == 1)
        {$nivel->shift();}

        $activo = $nivel->where('estado_activo',1);
        
            foreach($activo->pluck('month_month_id') as $a){
                $mes = $arregloMeses->where('month_id',$a)->first();
                $activado->push($mes->month_name.' '.$mes->year);
                
            }
            $rpta[$i]["Meses activados"] = $activado->implode(',');
            foreach($nivel->pluck('month_month_id') as $a){
                $mes = $arregloMeses->where('month_id',$a)->first();
                $meses->push($mes->month_name.' '.$mes->year);
                
            }
            $rpta[$i]["Meses"] = $meses->implode(',');
        } else

        {
            $rpta[$i]["Meses activados"] = "No hay meses asignados";
            $rpta[$i]["Meses"] = "No hay meses asignados";
            
        }

        $com = ComiteStartUp::where('start_up_id',$id)->where('estado',1)->get();
        if($com != null)
        {
            foreach($com->pluck('user_id') as $c)
        {
            $user = $arregloUsuarios->where('user_id',$c)->first();
            $comite->push($user->name);
        }
        $rpta[$i]["Comite"] = $comite->implode(',');
        } else {
            $rpta[$i]["Comite"] = 'No tiene evaluadores asignados';
        }
        
        
        $comite = collect([]);
        $activado = collect([]);
        $meses = collect([]);
        $i++;
    }*/

    return response()->json(['msg' => 'Start Up y Evaluadores','rpta' => $resultado,'success' => true], 201);

}

public function detalleActivacionPorStartUp($id){
        //devuelve
    $startUp = StartUp::select('start_up_id','name','created_at','tiempo')->where('start_up_id',$id)->first();
    $rpta = StartUp::select('start_up_id','name')->where('start_up_id',$id)->get();
    $arregloMeses = Month::select('month_id','month_number','month_name','year')->get();
    $user = User::where('start_up_id',$id)->first();
    $var = $user->fecha_inicio;
    $date =new DateTime($var);
    if ($date->format('d') <= 15) {
        $mes = $arregloMeses->where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
    } else {
        if($date->format('m') == 12){
            $mes = $arregloMeses->where('month_number',1)->where('year',$date->format('Y')+1)->first();
        } else {
            $mes = $arregloMeses->where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
        }

    }         
    $meses = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
    $listaMes = $meses->pluck('month_id');
    $arreglo = collect([]);

    foreach ($listaMes as $a) {
        $ment = Mentoria::where('month_month_id',$a)->where('start_up_id',$id)->where('fase',$startUp->fase)->first();
        if($ment->estado_activo == 1){

            $rpta[0]["month_id"] = $a;
            $x = $arregloMeses->where('month_id',$a)->first();
            $rpta[0]["Mes activado"] = $x->month_name.' '.$x->year;

            foreach ($listaMes as $c) {
                $x = $arregloMeses->where('month_id',$c)->first();
                $arreglo->push($x->month_name.' '.$x->year);
            }

            $rpta[0]["Meses"]= $arreglo->implode(',');
        }
    }
    return response()->json(['msg' => 'Start Up y Mes Activo','rpta' => $rpta,'success' => true], 201);    
}

public function obtenerStartUpConMesActivado($id){
    $startUp = StartUp::select('start_up_id','name','tiempo','fase','created_at')->where('start_up_id',$id)->first();  
    $user = User::where('start_up_id',$id)->first();
    $var = $user->fecha_inicio;
    $date =new DateTime($var);
    if ($date->format('d') <= 15) {
        $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
    } else {
        if($date->format('m') == 12){
            $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
        } else {
            $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
        }

    }  
    $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
    $i = 0;
    foreach($listaMes->pluck('month_id') as $a){

        $m = NivelAvance::where('start_up_id',$id)->where('month_month_id',$a)->where('fase',$startUp->fase)->first();
        $x = Month::where('month_id',$a)->first();
        $listaMes[$i]['titulo'] = $x->month_name.' '.$x->year;
        if ($m != null){
            $listaMes[$i]['estado_activo'] = $m->estado_activo;
        }

        $i++;
    }
    return response()->json(['msg' => 'Lista de meses','rpta' => $listaMes ,'success' => true], 201);
}

public function listaMesesActivosInactivos($id)
{
    $activado = collect([]);
    $desactivado = collect([]);

    $startUp = StartUp::select('name','fase','tiempo','cambio_fase')->where('start_up_id',$id)->first();

    $nivel = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase)->get();
    if($startUp->fase == 1)
        {$nivel->shift();}

    $activo = $nivel->where('estado_activo',1);
    $inactivo = $nivel->where('estado_activo',0);

    foreach($activo->pluck('month_month_id') as $a){
        $mes = Month::where('month_id',$a)->first();
        $activado->push($mes->month_name.' '.$mes->year);
    }

    foreach($inactivo->pluck('month_month_id') as $a){
        $mes = Month::where('month_id',$a)->first();
        $desactivado->push($mes->month_name.' '.$mes->year);
    }



    return response()->json([
        'rpta' => array('Meses activos' => $activado ,'Meses desactivados' => $desactivado),
        'success' => true],
        201);
}

public function cerrarPeriodo($id,Request $request){
    $startUp = StartUp::where('start_up_id',$id)->first();
        /*Validar con 3 requisitos
        *Evaluadores asignados
        *Meses asignados
        *Meses activados
        */
        $evaluador = ComiteStartUp::where('start_up_id',$id)->where('estado',1)->first();
        $mesAsignado = $startUp->tiempo;
        
        $mesActivado = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase)->where('estado_activo',1)->get();
        /*if($mesActivado != null && $startUp->fase == 1 )
            {$mesActivado->shift();}*/
        
        /*if($evaluador != null && $mesAsignado != 0 && $mesActivado->count() != 0)
        {
            return 'true';
        } else {
            return 'false';
        }*/
        
        //----------------------------------------------------------------//
        if($evaluador != null && $mesAsignado != 0 && $mesActivado->count() != 0)
        {
            $var = getdate();
            $d = $var['mday'];
            $m = $var['mon'];
            $y = $var['year'];
            $user = User::where('start_up_id',$id)->first();
            //$startUp = StartUp::where('start_up_id',$id)->first();
            $startUp->fase = $startUp->fase + 1;
            $startUp->cambio_fase = 1;
            $startUp->tiempo = 0;
            $user->fecha_inicio = $y."-".$m."-".$d;
            $user->update();
            $startUp->update();

            //Eliminar Meses Inactivos
            $nivel = NivelAvance::where('start_up_id',$id)->where('fase',$startUp->fase - 1)->get();
            if($startUp->fase == 1)
                {$nivel->shift();}
            $inactivo = $nivel->where('estado_activo',0);
            
            //Eliminando Evaluacion de Objetivos y Vision
            $objetivos = Objetivo::select('objetivo_id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->get();
            $vision = Vision::select('vision_id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->get();
            
            DB::table('evaluacion_objetivos')->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->whereIn('objetivo_id',$objetivos->pluck('objetivo_id'))->delete();
            DB::table('evaluaciones_vision')->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->whereIn('vision_id',$vision->pluck('vision_id'))->delete();            
            /*$ev_objetivos = EvaluacionObjetivo::select('id')->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->whereIn('objetivo_id',$objetivos->pluck('objetivo_id'))->get();
            foreach($ev_objetivos->pluck('id') as $a){
                $objeto = EvaluacionObjetivo::findOrFail($a);
                $objeto->delete();
            }*/
            /*$ev_vision = EvaluacionVision::select('id')->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->whereIn('vision_id',$vision->pluck('vision_id'))->get();            
            foreach($ev_vision->pluck('id') as $a){
                $objeto = EvaluacionVision::findOrFail($a);
                $objeto->delete();
            }*/

            //---------------Eliminando Indicadores---------------//
            DB::table('mentorias')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('rendimientos_economico_montos')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('apoyo_asesorias_otros_servicios')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('empleos_creados_freelancers')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('empleos_creados_fundadores')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('empleos_creado_empleados')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('empleos')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            DB::table('nivel_avance')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->delete();
            /*$mentoria = Mentoria::select('mentor_id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($mentoria->pluck('mentor_id') as $a){
                $objeto = Mentoria::findOrFail($a);
                $objeto->delete();
            }
            $rendimiento = RendimientoEconomicoMonto::select('id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($rendimiento->pluck('id') as $a){
                $objeto = RendimientoEconomicoMonto::findOrFail($a);
                $objeto->delete();
            }
            $asesoria = ApoyoAsesoriaOtroServicio::select('asesoria_id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($asesoria->pluck('asesoria_id') as $a){
                $objeto = ApoyoAsesoriaOtroServicio::findOrFail($a);
                $objeto->delete();
            }
            $freelancer = EmpleoCreadoFreelancer::select('id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($freelancer->pluck('id') as $a){
                $objeto = EmpleoCreadoFreelancer::findOrFail($a);
                $objeto->delete();
            }
            $fundador = EmpleoCreadoFundador::select('id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($fundador->pluck('id') as $a){
                $objeto = EmpleoCreadoFundador::findOrFail($a);
                $objeto->delete();
            }
            $empleado = EmpleoCreadoEmpleado::select('id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($empleado->pluck('id') as $a){
                $objeto = EmpleoCreadoEmpleado::findOrFail($a);
                $objeto->delete();
            }
            $avance = NivelAvance::select('nivel_avance_id')->where('start_up_id',$id)->where('fase',$startUp->fase -1)->whereIn('month_month_id',$inactivo->pluck('month_month_id'))->get();
            foreach($avance->pluck('nivel_avance_id') as $a){
                $objeto = NivelAvance::findOrFail($a);
                $objeto->delete();
            }*/

            return response()->json(['msg' => 'La Start Up '.$startUp->name.' acaba de pasar a periodo '.$startUp->fase,'success' => true], 201);
        }
        else {
            return response()->json(['msg' => 'La Start Up '.$startUp->name.' tiene todos los meses inactivos o no se le han asignado evaluadores','success' => true], 201);
        }
    }

    public function revertirCambioFase($id,Request $request){
        $startUp = StartUp::where('start_up_id',$id)->first();
        $startUp->cambio_fase = 0;
        $startUp->update();

        return response()->json(['msg' => '','success' => true], 201);

    }

    public function obtenerFases($id){
        $vision = Vision::select('fase as fase_id')->where('start_up_id',$id)->get();
        $k =1;
        for ($i=0; $i < $vision->count() ; $i++) { 

            $vision[$i]['titulo'] ='Fase '.$k; 
            $k++;
        }

        return response()->json(['msg' => '','rpta' => $vision,'success' => true], 201);
    }

    public function obtenerFasesAnteriores($id){
        $rpta = collect([]);
        $j = 0;
        $startUp = StartUp::select('start_up_id','fase')->where('start_up_id',$id)->first();
        for ($i=1; $i <= $startUp->fase ; $i++) {

            $vision = Vision::where('start_up_id',$id)->where('fase',$i)->first();

            if ($vision == null){
                return response()->json(['rpta'=> [] ,'success'=>true ],201);
            }
            $ev = EvaluacionVision::where('vision_id',$vision->vision_id)->get();
            $listaMes = Month::select('month_id')->whereIn('month_id',$ev->pluck('month_month_id')->unique()->values())->get();

            foreach($listaMes->pluck('month_id') as $m){
                $listaMes[$j]['fase_id'] = $i;
                $ment = Mentoria::where('start_up_id',$id)->where('month_month_id',$m)->where('fase',$i)->first();
                $x = Month::where('month_id',$m)->first();
                $listaMes[$j]['titulo'] = $x->month_name.' '.$x->year;
                if ($ment != null){
                    $listaMes[$j]['estado_activo'] = $ment->estado_activo;
                }
                $j++;
            }
            $j = 0;
            $rpta->push($listaMes);
        //$fase = collect([]);

        }

        return response()->json(['rpta'=>$rpta ,'success'=>true ],201);

    }

    //POR CHEQUEAR
    //Se envia el numero de fase en la url
    public function tablaEvaluacionesPorStartUpPorFase($id,Request $request){
        //$request->header('START-UP-ID')
        $startUp = StartUp::where('start_up_id',$request->header('START-UP-ID'))->first();
        
        /*$vision = Vision::where('start_up_id',$request->header('START-UP-ID'))->where('fase',$id)->first();
        $evaluacionVision = EvaluacionVision::where('vision_id',$vision->vision_id)->get();
*/
        $objetivo = Objetivo::select('objetivo_id','objetivo')->where('start_up_id',$request->header('START-UP-ID'))->where('fase',$id)->get();
        $listaObjetivo = $objetivo->pluck('objetivo_id');

        $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->whereIn('objetivo_id',$listaObjetivo)->get();
        
        $listaEvaluadores = $evaluacion->pluck('user_int');
        
        //$startUp = StartUp::where('start_up_id',$id)->first(); ya está arriba
        /*$user = User::where('start_up_id',$request->header('START-UP-ID'))->first();
        $var = $user->fecha_inicio;
        $date =new DateTime($var);
        if ($date->format('d') <= 15) {
                            $mes = Month::where('month_number',$date->format('m'))->where('year',$date->format('Y'))->first();
                        } else {
                            if($date->format('m') == 12){
                                $mes = Month::where('month_number',1)->where('year',$date->format('Y')+1)->first();
                            } else {
                                $mes = Month::where('month_number',$date->format('m') + 1)->where('year',$date->format('Y'))->first();
                            }
                            
                        }  
        $listaMes = Month::select('month_id')->where('month_id','>=',$mes->month_id)->limit($startUp->tiempo)->get();
        */

        $i = 0;
        $j = 0;

        foreach ($listaObjetivo as $a) {

            $evaluacion = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$listaEvaluadores->first())->get();
            foreach ($evaluacion->pluck('month_month_id') as $y) {
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('month_month_id',$y)->first();       
                $x = Month::where('month_id',$y)->first();
                $objetivo[$i][$x->month_name.' '.$x->year]= $evaluacion1->porcentaje;
            }

            foreach ($listaEvaluadores as $b) {
                $user = User::where('user_id',$b)->first();
                $evaluacion1 = EvaluacionObjetivo::select('calificacion','user_int','porcentaje','month_month_id')->where('objetivo_id',$a)->where('user_int',$b)->get();
                $objetivo[$i][$user->name] = $evaluacion1->pluck('calificacion')->avg();
            }
            
            $i++;
        }

        return response()->json(['rpta'=>$objetivo ,'success'=>true ],201);
        
    }


    public function verificarDataLlenadaIncubadoPorMes($id, Request $request){
        $arreglo = $request->input('month_id');
        $startUp = StartUp::where('start_up_id',$id)->first();
        // echo 'START UP:';
        // echo $startUp->name;
        // echo 'Fase:'.$startUp->fase;
        $rendimientos = RendimientoEconomicoMonto::where('start_up_id',$startUp->start_up_id)
        ->where('fase',$startUp->fase)->whereIn('month_month_id', $arreglo)->get()->toArray();
        $indice = 0;
        if ($startUp->fase > 1) {
            $indice = 1; // El primer rendimiento economico para la primera fase es el registro inicial
        }
        for ($i=$indice; $i < sizeof($rendimientos); $i++) { // El primer ren
            if ($rendimientos[$i]['facturacion'] != null) {
                return response()->json(['success'=> true, 'msg' => 'El Incubado tiene información procesada en Rendimiento Económico desea desactivar estos meses?'], 200);
            }
        }
        $evaluadores = ComiteStartUp::where('start_up_id',$startUp->start_up_id)->where('estado',1)->get();
        $evaluacionVision = EvaluacionVision::whereIn('user_int',$evaluadores->pluck('user_id'))->where('month_month_id',$id)->get()->toArray();
        for ($i=0; $i < sizeof($evaluacionVision); $i++) { 
            if($evaluacionVision[$i]['etapa'] != null || $evaluacionVision[$i]['etapa'] != ''){
                return response()->json(['success'=> true, 'msg' => 'El Incubado tiene registrada su etapa desea desactivar estos meses?'], 200);
            }
        }
        return response()->json(['success'=>false], 201);
    }
}
