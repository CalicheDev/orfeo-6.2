<?php


    session_start();
    $dir_raiz = $_SESSION['dir_raiz'];
    include "../../config.php";
    // $dbProcess = new ConnectionHandler("$dir_raiz");
    include_once "$dir_raiz/include/db/ConnectionDB.php";
    $dbProcess = new ConnectionDB("$dir_raiz");
    $dbProcess->conn->SetFetchMode(ADODB_FETCH_ASSOC);

    $dbProcessNew = new ConnectionDB("$dir_raiz", false);
    $dbProcessNew->conn->SetFetchMode(ADODB_FETCH_ASSOC);

    //Incluido el archivo que tiene la clase historico
    include("../../include/tx/Historico.php");
    $historiRecord = new Historico($dbProcess);

    switch($_POST['type']){
       
        # Busqueda de Documentos
        case '1':

            $request = $_POST;
            /* print_r($_POST);
            exit; */

            $queryRadicados = "SELECT radicado.*, sgd_exp_expediente.*, sgd_sexp_secexpedientes.*, sgd_dir_drecciones.* FROM radicado";
            # join a expedientes - radicado 
            $queryRadicados .= " LEFT JOIN sgd_exp_expediente ON radicado.radi_nume_radi = sgd_exp_expediente.radi_nume_radi";
            # join a detalles expedientes 
            $queryRadicados .= " LEFT JOIN sgd_sexp_secexpedientes ON sgd_exp_expediente.sgd_exp_numero = sgd_sexp_secexpedientes.sgd_exp_numero";
            # join a dir direcciones  
            $queryRadicados .= " INNER JOIN sgd_dir_drecciones ON radicado.radi_nume_radi = sgd_dir_drecciones.radi_nume_radi";


            if(isset($request['form']) && !empty($request['form'])){

                $queryRadicados .= " WHERE 1 = 1";
                foreach ($request['form'] as $key => $value) {
                    if(isset($request['form'][$key]) && !empty($request['form'][$key])){
                       
                        $valorBuscado = trim($value);

                        switch ($key) {
                            case 'radi_nume_radi':
                                $queryRadicados .= " and  radicado.radi_nume_radi LIKE '%".$valorBuscado."%'";
                            break;
                            case 'sgd_sexp_parexp1':
                                $queryRadicados .= " and  sgd_sexp_secexpedientes.sgd_sexp_parexp1 LIKE '%".$valorBuscado."%'";
                            break;
                            case 'sgd_exp_numero':
                                $queryRadicados .= " and  sgd_sexp_secexpedientes.sgd_exp_numero LIKE '%".$valorBuscado."%'";
                            break;
                            case 'ra_asun':
                                $queryRadicados .= " and  radicado.ra_asun LIKE '%".$valorBuscado."%'";
                            break;
                            case 'sgd_dir_nomremdes':
                                $queryRadicados .= " AND  sgd_dir_drecciones.sgd_dir_nomremdes LIKE '%".$valorBuscado."%'";
                            break;
                            case 'sgd_dir_doc':
                                $queryRadicados .= " AND  sgd_dir_drecciones.sgd_dir_doc LIKE '%".$valorBuscado."%'";
                            break;
                            case 'radi_depe_radi':
                                $queryRadicados .= " AND  radicado.radi_depe_radi = '".$valorBuscado."'";
                            break;
                            case 'radi_depe_actu':
                                $queryRadicados .= " AND  radicado.radi_depe_actu = '".$valorBuscado."'";
                            break;
                            case 'sgd_trad_codigo':
                                $queryRadicados .= " AND  RIGHT(radicado.radi_nume_radi,1) = '".$valorBuscado."'";
                            break;
                            case 'tdoc_codi':
                                $queryRadicados .= " AND  radicado.tdoc_codi = ".$valorBuscado;
                            break;
                            case 'radi_fech_radi_ini': 
                                $ini = $request['form']['radi_fech_radi_ini'] ?? date('Y-m-d'); 
                                $queryRadicados .=" AND  radicado.radi_fech_radi >= '".trim($ini)." 00:00:00'";
                            break;
                            case 'radi_fech_radi_end': 
                                $end = $request['form']['radi_fech_radi_end'] ?? date('Y-m-d'); 
                                $queryRadicados .=" AND  radicado.radi_fech_radi <= '".trim($end)." 23:59:59'";
                            break;
                        }
                    }
                }
            }

            # niveles de busqueda -> usua_nivel_consulta
            # print_r($_SESSION);die();

            # consultar usuario
            $sql = "SELECT * FROM usuario WHERE usua_doc = '".$_SESSION['usua_doc']."'";
            $usua_session = $dbProcessNew->conn->query($sql); // false: para NO consultar la BD historico (consultar la principal porque son permisos de usuario)

            $queryRadicados .= " ORDER BY radi_fech_radi DESC  LIMIT 1000";
            //print_r($queryRadicados);die(); # SQL

            // print_r($usua_session->fields);die();
            $rsRadicados = $dbProcess->conn->query($queryRadicados);

            $tableRadicados = "";

            if(isset($rsRadicados)){
                while (!$rsRadicados->EOF) {

                    $continuar = TRUE;
                    # usuario
                    $radi_usua_actu = $dbProcess->conn->query("SELECT * FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_actu']." AND depe_codi = '".$rsRadicados->fields["radi_depe_actu"]."'"); 
                    $radi_usua_radi = $dbProcess->conn->query("SELECT usua_nomb FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_radi']." AND depe_codi = '".$rsRadicados->fields["radi_depe_radi"]."'"); 
                    if(!empty($request['form']['radi_usua_actu'])) {
                        $buscaUsuaActu = stripos($radi_usua_actu->fields['usua_nomb'], $request['form']['radi_usua_actu']);
                        if($buscaUsuaActu === FALSE) {
                            $continuar = FALSE;
                        }
                    }
                    if(!empty($request['form']['radi_usua_radi'])) {
                        $buscaUsuaRadi = stripos($radi_usua_radi->fields['usua_nomb'], $request['form']['radi_usua_radi']);
                        if($buscaUsuaRadi === FALSE) {
                            $continuar = FALSE;
                        }
                    }
                    if($continuar) {
                        # dependencias
                        $radi_depe_radi = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_radi']."'"); 
                        $radi_depe_actu  = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_actu']."'"); 
                        # tipo documental
                        $cod_tp_tpdcumento = $dbProcess->query("SELECT sgd_tpr_descrip FROM sgd_tpr_tpdcumento WHERE sgd_tpr_codigo = ".$rsRadicados->fields['tdoc_codi']);

                        # consultar remitente en sgd_ciu_ciudadano
                        $remitente = $dbProcess->conn->query("SELECT sgd_ciu_nombre AS nombre ,sgd_ciu_cedula AS documento  FROM sgd_ciu_ciudadano WHERE sgd_ciu_codigo =".$rsRadicados->fields['sgd_ciu_codigo']); 

                        if(empty($remitente)){
                            # consultar remitente en bodega_empresas
                            $remitente = $dbProcess->conn->query("SELECT nombre_de_la_empresa AS nombre ,nit_de_la_empresa AS documento  FROM bodega_empresas WHERE identificador_empresa =".$rsRadicados->fields['sgd_esp_codi']);
                            if(empty($remitente)){
                                # consultar remitente en sgd_oem_oempresas
                                $remitente = $dbProcess->conn->query("SELECT sgd_oem_oempresa AS nombre ,sgd_oem_nit AS documento  FROM sgd_oem_oempresas WHERE sgd_oem_codigo =".$rsRadicados->fields['sgd_oem_codigo']);
                            }
                        }

                        if(empty($remitente)){
                            $remitenteNombre = 'Sin información';
                            $remitenteDocume = 'Sin información';
                        }else{
                            $remitenteNombre = $remitente->fields['nombre'];
                            $remitenteDocume = $remitente->fields['documento'];
                        }

                        if(isset($rsRadicados->fields['radi_desc_anex'])){
                            $radi_desc_anex = trim($rsRadicados->fields['radi_desc_anex']);
                        }else{
                            $radi_desc_anex = 'Sin especificar';
                        }

                        $result[] = [
                            'radi_nume_radi' => $rsRadicados->fields['radi_nume_radi'],
                            'radi_fech_radi' => date('Y-m-d H:i', strtotime($rsRadicados->fields['radi_fech_radi'])),
                            'ra_asun'        => trim($rsRadicados->fields['ra_asun']) ?? 'Sin especificar asunto',
                            // 'radi_desc_anex' => $radi_desc_anex,
                            'sgd_exp_numero' => $rsRadicados->fields['sgd_exp_numero'] ?? 'Sin Expediente',
                            'sgd_exp_nomb'   => $rsRadicados->fields['sgd_sexp_parexp1'] ?? 'Sin Expediente',
                            'nombre'         => $remitenteNombre,
                            'documento'      => $remitenteDocume,
                            'radi_usua_actu' => $radi_usua_actu->fields['usua_nomb'] ?? 'No encontrado',
                            'radi_usua_radi' => $radi_usua_radi->fields['usua_nomb'] ?? 'No encontrado',
                            'radi_depe_radi' => $radi_depe_radi->fields['depe_nomb'] ?? 'No encontrado',
                            'radi_depe_actu' => $radi_depe_actu->fields['depe_nomb'] ?? 'No encontrado',
                            'sgd_tpr_descri' => $cod_tp_tpdcumento->fields['sgd_tpr_descrip'] ?? 'No encontrado',
                        ];
                        
                    }
                    $rsRadicados->MoveNext();
                }
            }

            foreach($result as $value){

                $tableRadicados .= "<tr>";
                    # datos del radicado
                    $tableRadicados .= "<td>".$value['radi_nume_radi']."</td>";   // <th>Número de Radicado</th>
                    $tableRadicados .= "<td>".$value['radi_fech_radi']."</td>";   // <th>Fecha de Radicación</th>
                    $tableRadicados .= "<td width=200>".$value['ra_asun']."</td>";          // <th>Asunto del Radicado</th>
                    // $tableRadicados .= "<td>".$value['radi_desc_anex']."</td>";          // <th>Asunto del Radicado</th>   
                    # datos del expediente      
                    $tableRadicados .= "<td>".$value['sgd_exp_numero']."</td>";   // <th>Número Expediente</th>
                    $tableRadicados .= "<td>".$value['sgd_exp_nomb']."</td>";     // <th>Nombre expediente</th>
                    # datos del remitente
                    $tableRadicados .= "<td>".$value['nombre']."</td>";      // <th>Remitente/Destinatario</th>
                    $tableRadicados .= "<td>".$value['documento']."</td>";      // <th>Nit o Documento </th>
                    # dependencia
                    $tableRadicados .= "<td>".$value['radi_depe_radi']."</td>";   // <th>Dependencia Radicadora</th>
                    # tramitador
                    $tableRadicados .= "<td>".$value['radi_usua_radi']."</td>";   // <th>Usuario Radicador</th>
                    # Dependencia Actual
                    $tableRadicados .= "<td>".$value['radi_depe_actu']."</td>";   // <th>Dependencia Actual</th>
                    $tableRadicados .= "<td>".$value['radi_usua_actu']."</td>";   // <th>Usuario Actual</th>
                    # tipo documental   
                    $tableRadicados .= "<td>".$value['sgd_tpr_descri']."</td>";   // <th>Tipo Documental</th>
                    # action
                    $tableRadicados .= 
                    
                    "<td class='btn-action-content'>"
                        .'<span style="color: #1c4056;" class="glyphicon glyphicon-paperclip btn-action" data-toggle="tooltip" title="Ver Documentos." aria-hidden="true" onclick="documet(\''.$value['radi_nume_radi'].'\')"></span>'   // Ver Documentos,     
                        .'<span style="color: #8f0000;" class="glyphicon glyphicon-time btn-action"  data-toggle="tooltip" title="Ver Historico."  aria-hidden="true" onclick="history(\''.$value['radi_nume_radi'].'\')"></span>'   // Ver historico</th>
                    ."</td>";  

                $tableRadicados .= "</tr>";

            }

            echo $tableRadicados; 

        break;

        # Historico de Radicados
        case '2':

            $request = $_POST;

            $queryRadicados = "SELECT * FROM radicado WHERE radi_nume_radi = '".$request['radi_nume_radi']."'";
            $rsRadicados = $dbProcess->conn->query($queryRadicados);

            while (!$rsRadicados->EOF) {

                # usuario
                $radi_usua_actu = $dbProcess->conn->query("SELECT * FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_actu']." AND depe_codi = '".$rsRadicados->fields["radi_depe_actu"]."'"); 
                $radi_usua_radi = $dbProcess->conn->query("SELECT usua_nomb FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_radi']." AND depe_codi = '".$rsRadicados->fields["radi_depe_radi"]."'"); 
                # dependencias
                $radi_depe_radi = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_radi']."'"); 
                $radi_depe_actu  = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_actu']."'"); 

                $details = [
                    'radi_usua_actu' => $radi_usua_actu->fields['usua_nomb'] ?? 'x',
                    'radi_usua_radi' => $radi_usua_radi->fields['usua_nomb'] ?? 'x',
                    'radi_depe_radi' => $radi_depe_radi->fields['depe_nomb'] ?? 'x',
                    'radi_depe_actu' => $radi_depe_actu->fields['depe_nomb'] ?? 'x',
                ];
                
                $rsRadicados->MoveNext();
            }

            # historial && eventos
            $hist_eventos = "SELECT * FROM hist_eventos WHERE radi_nume_radi ='".$request['radi_nume_radi']."'";
            $hist_eventos .= " ORDER BY hist_fech DESC";
            $rsHistEvents= $dbProcess->conn->query($hist_eventos);

            while (!$rsHistEvents->EOF) {

                # dependencia
                $depe_codi  = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsHistEvents->fields['depe_codi']."'"); 
                # transaccion
                $sgd_ttr_codigo = $dbProcess->conn->query("SELECT sgd_ttr_descrip FROM sgd_ttr_transaccion WHERE sgd_ttr_codigo = ".$rsHistEvents->fields['sgd_ttr_codigo']); 
                # usuario
                $usua_doc = $dbProcess->conn->query("SELECT usua_nomb FROM usuario WHERE usua_doc = '".$rsHistEvents->fields['usua_doc']."'"); 

                $result[] = [
                    'depe_nomb' => $depe_codi->fields['depe_nomb'],
                    'hist_fech' => date('Y-m-d H:i', strtotime($rsHistEvents->fields['hist_fech'])),
                    'sgd_ttr_codigo' => $sgd_ttr_codigo->fields['sgd_ttr_descrip'],
                    'usua_nomb' => $usua_doc->fields['usua_nomb'],
                    'hist_obse' => $rsHistEvents->fields['hist_obse']
                ];

                $rsHistEvents->MoveNext();
            }

            ob_start();
            include './views/history.php';
            echo ob_get_clean(); 

        break;

        # Documentos del Radicado
        case '3':

            $request = $_POST;

            $queryRadicados = "SELECT * FROM radicado WHERE radi_nume_radi = '".$request['radi_nume_radi']."'";
            $rsRadicados = $dbProcess->conn->query($queryRadicados);

            while (!$rsRadicados->EOF) {

                # usuario
                $radi_usua_actu = $dbProcess->conn->query("SELECT * FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_actu']." AND depe_codi = '".$rsRadicados->fields["radi_depe_actu"]."'"); 
                $radi_usua_radi = $dbProcess->conn->query("SELECT usua_nomb FROM usuario WHERE usua_codi =".$rsRadicados->fields['radi_usua_radi']." AND depe_codi = '".$rsRadicados->fields["radi_depe_radi"]."'"); 
                # dependencias
                $radi_depe_radi = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_radi']."'"); 
                $radi_depe_actu  = $dbProcess->conn->query("SELECT depe_nomb FROM dependencia WHERE depe_codi = '".$rsRadicados->fields['radi_depe_actu']."'"); 

                $details = [
                    'radi_usua_actu' => $radi_usua_actu->fields['usua_nomb'] ?? 'x',
                    'radi_usua_radi' => $radi_usua_radi->fields['usua_nomb'] ?? 'x',
                    'radi_depe_radi' => $radi_depe_radi->fields['depe_nomb'] ?? 'x',
                    'radi_depe_actu' => $radi_depe_actu->fields['depe_nomb'] ?? 'x',
                ];

                /** Procesar imagen principal del radicado */
                if ($rsRadicados->fields['radi_path'] != null && $rsRadicados->fields['radi_path'] != '') {

                    # TRD
                    $trd = TRD_RADICADO($request['radi_nume_radi']);
                    // var_dump($trd); die();
                    # link
                    $link = $rutaBodegaOLD.''.$rsRadicados->fields['radi_path'];
                    $doc2_b64 = base64_encode(file_get_contents($link, FILE_USE_INCLUDE_PATH));

                    $result[] = [
                        'radi_nume_salida' => $rsRadicados->fields['radi_nume_radi'],
                        'anex_codigo' => '',
                        'anex_tipo_desc' => 'Acrobat Reader(pdf)',
                        'trd_desc' => $trd,
                        'usua_nomb' => $details['radi_usua_radi'],
                        'anex_desc' => $rsRadicados->fields['radi_desc_anex'],
                        'anex_radi_fech' => date('Y-m-d H:i', strtotime($rsRadicados->fields['radi_fech_radi'])),
                        'anex_nomb_archivo' => $rsRadicados->fields['radi_nume_radi'],
                        'documento' => 'Imagen Principal',
                        'link' => $link,
                        'link_document' => $doc2_b64
                    ];
                }
                /** Fin Procesar imagen principal del radicado */
                
                $rsRadicados->MoveNext();
            }

            # historial && eventos
            $anexos = "SELECT * FROM anexos WHERE anex_radi_nume ='".$request['radi_nume_radi']."'";
            $anexos .= " ORDER BY anex_radi_fech DESC";
            $rsAnexos= $dbProcess->conn->query($anexos);

            while (!$rsAnexos->EOF) {

                # anex_tipo
                $anex_tipo  = $dbProcess->conn->query("SELECT anex_tipo_desc, anex_tipo_ext FROM anexos_tipo WHERE anex_tipo_codi = ".$rsAnexos->fields['anex_tipo']); 
                # TRD
                $trd = TRD($request['radi_nume_radi'], $rsAnexos->fields['anex_codigo']);
                # link
                $link = $rutaBodegaOLD . substr(trim($rsAnexos->fields['anex_codigo']), 0, 4) . "/" . $rsAnexos->fields['anex_depe_creador'] . "/docs" . "/".trim(strtoupper(stristr($rsAnexos->fields["anex_nomb_archivo"], ".", true))) . trim(stristr($rsAnexos->fields['anex_nomb_archivo'], "."));   
                $doc2_b64 = base64_encode(file_get_contents($link, FILE_USE_INCLUDE_PATH));

                $result[] = [
                    'radi_nume_salida' =>  $rsAnexos->fields['radi_nume_salida'] ??  $rsAnexos->fields['anex_codigo'],
                    'anex_codigo' => $rsAnexos->fields['anex_codigo'],
                    'anex_tipo_desc' => $anex_tipo->fields['anex_tipo_desc'].'('.$anex_tipo->fields['anex_tipo_ext'].')',
                    'trd_desc' => $trd,
                    'usua_nomb' => $rsAnexos->fields['anex_creador'],
                    'anex_desc' => $rsAnexos->fields['anex_desc'],
                    'anex_radi_fech' => date('Y-m-d H:i', strtotime($rsAnexos->fields['anex_radi_fech'])),
                    'anex_nomb_archivo' =>  $rsAnexos->fields['anex_nomb_archivo'],
                    'documento' => 'Anexo',
                    'link' => $link,
                    'link_document' => $doc2_b64
                ];

                $rsAnexos->MoveNext();
            }

            ob_start();
            include './views/documents.php';
            echo ob_get_clean(); 

        break;

        ###
        # Inventario Documental

        # Inventario Documental --- Cargar Documentos ---
        case '4':

            include "$dir_raiz/config.php";
            require_once("../../include/db/ConnectionHandler.php");

            $db = new ConnectionHandler("$dir_raiz");
            $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);
            $request = $_POST;  $files = $_FILES;

            # por defecto ajax sube repetido un archivo -> 
            unset($files['upload']);

            # validacion de archivos
            if(isset($files) && !empty($files) && $files['error'] == 0){

                $formatos_permitidos =  array('pdf');

                foreach($files as $key => $file){

                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

                    if(!in_array($extension, $formatos_permitidos) ) {
                        echo '<div class="alert alert-danger" role="alert"> Error el formato del archivo es invalido </div>'; die();
                    }
                }

            }else{
                echo '<div class="alert alert-danger" role="alert"> El archivo es obligatorio </div>'; die();
            }

            $exceptions = ['upload','type'];
            foreach($request as $key => $value){
                if(!in_array($key,$exceptions)){
                    $campos[] = $key;
                    $values[] = "'".$value."'";
                }
            }

            $sql = "INSERT INTO inventario_documental (".implode(',', $campos).") VALUES (".implode(',',$values).")";
            #print_r($sql);die();       
            $rs = $db->conn->query($sql);

            $id = $db->conn->query("SELECT nextval('inventario_documental_id_inv_documental_seq')");
            $id_inv_documental = $id->fields['nextval'] - 1;

            if ($rs == -1){
                echo '<div class="alert alert-danger" role="alert"> Error al guardar, Intentelo mas tarde  </div>'; die();
            }

            foreach($files as $key => $value){

                $tmp = $value;  $campos_file = [];  $values_file = [];

                /****   
                 * inventario_documentos
                 * 
                 * id_inv_documento
                 * original_inv_doc
                 * nombre_inv_doc
                 * ruta_inv_doc
                 * tipo_inv_doc
                 * tamano_inv_doc
                 * user_inv_doc
                 * creacion_inv_doc
                 */

                $year = date('Y');

                $ruta = "$dir_raiz/bodega/" . $year . "/inventario";

                if(!file_exists($ruta)){
                    mkdir($ruta, 0775);
                }

                //print_r($value);die();

                $id_inv_documento = $db->conn->query("SELECT nextval('inventario_documentos_id_inv_documento_seq')");
                $id_inv_documento = $id_inv_documento->fields['nextval'] + 1;

                $value['type'] = pathinfo($value['name'], PATHINFO_EXTENSION);
                $nombre_inv_doc = $year.str_pad('0',6,'0', STR_PAD_LEFT). $id_inv_documento.'.'.$value['type']; 

                $file_name =  $ruta."/".$nombre_inv_doc;

                $data = [
                    'original_inv_doc' => $value['name'],
                    'nombre_inv_doc' => $nombre_inv_doc,
                    'ruta_inv_doc' => $file_name,
                    'tipo_inv_doc' => $value['type'],
                    'tamano_inv_doc' => $value['size'],
                    'user_inv_doc' => $_SESSION['usua_doc'],
                    'creacion_inv_doc' => date("Y-m-d H:i:s"),
                    'id_inv_documental' => $id_inv_documental
                    
                ]; 

                foreach($data as $key => $value){
                    $campos_file[] = $key;
                    $values_file[] = "'".$value."'";
                }

                $sql = "INSERT INTO inventario_documentos (".implode(',', $campos_file).") VALUES (".implode(',',$values_file).")";
                $rs = $db->conn->query($sql);

                if ($rs == -1){
                    echo '<div class="alert alert-danger" role="alert"> Error al guardar los archivos subidos, Intentelo mas tarde </div>'; die();
                }

                # guardar archivo
                move_uploaded_file($tmp['tmp_name'], $file_name);

            }

            echo ' <div class="alert alert-success" role="alert">Registros Guardados con Exito</div>';
            
        break;

        # Inventario Documental --- Index ---
        case '5':

            include "$dir_raiz/config.php";
            require_once("../../include/db/ConnectionHandler.php");

            $db = new ConnectionHandler("$dir_raiz");
            $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

            $request = $_POST; 

            $queryInventario = "SELECT * FROM inventario_documental";
        
            if(isset($request['form']) && !empty($request['form'])){

                $queryInventario .= " WHERE 1 = 1";

                foreach ($request['form'] as $key => $value) {
                    if(isset($request['form'][$key]) && !empty($request['form'][$key])){
                       

                        switch ($key) {
                            case 'uni_administrativa':
                                $queryInventario .= " AND  uni_administrativa = '".$value."'";
                            break;
                            case 'ofi_productora':
                                $queryInventario .= " AND  ofi_productora = '".$value."'";
                            break;
                            case 'objeto':
                                $queryInventario .= " AND  objeto LIKE '%".$value."%'";
                            break;
                            case 'codigo':
                                $queryInventario .= " AND  codigo LIKE '%".$value."%'";
                            break;
                            case 'nombre':
                                $queryInventario .= " AND  nombre = '".$value."'";
                            break;
                            case 'sub_serie':
                                $queryInventario .= " AND  sub_serie = '".$value."'";
                            break;
                            case 'modulo':
                                $queryInventario .= " AND  modulo::TEXT LIKE '%".$value."%'";
                            break;
                            case 'estante':
                                $queryInventario .= " AND  estante::TEXT  LIKE '%".$value."%'";
                            break;
                            case 'caja':
                                $queryInventario .= " AND  caja::TEXT LIKE '%".$value."%'";
                            break;
                            case 'carpeta':
                                $queryInventario .= " AND  carpeta::TEXT LIKE '%".$value."%'";
                            break;
                            case 'tomo':
                                $queryInventario .= " AND  tomo::TEXT LIKE '%".$value."%'";
                            break;
                            case 'otro':
                                $queryInventario .= " AND  otro LIKE '%".$value."%'";
                            break;
                            case 'soporte':
                                $queryInventario .= " AND  soporte = '".$value."'";
                            break;
                            case 'fr_consulta':
                                $queryInventario .= " AND  fr_consulta = '".$value."'";
                            break;
                            case 'fecha_ext_ini': 
                                $ini = $value ?? date('Y-m-d'); 
                                $queryInventario .=" AND  fecha_ext_ini >= '".trim($ini)." 00:00:00'";
                            break;
                            case 'fecha_ext_end': 
                                $end = $value ?? date('Y-m-d'); 
                                $queryInventario .=" AND  fecha_ext_end <= '".trim($end)." 23:59:59'";
                            break;
                        }
                    }
                }
            }
            
            $queryInventario .= " ORDER BY id_inv_documental DESC  LIMIT 100";
            # print_r($queryInventario);die(); # SQL

            $rsInventario = $db->conn->query($queryInventario);

            # print_r($queryInventario);
            # print_r($rsInventario);die(); # SQL

            $tableInventario = "";

            if(!empty($rsInventario)){
                while (!$rsInventario->EOF) {

                    /**  
                     * 
                     *   Inventario_Documental
                     * 
                     * id_inv_documental
                     * uni_administrativa
                     * ofi_productora
                     * no_orden
                     * objeto
                     * codigo
                     * nombre
                     * sub_serie
                     * fecha_ext_ini
                     * fecha_ext_end
                     * caja
                     * carpeta
                     * tomo
                     * otro
                     * modulo
                     * estante
                     * no_folios
                     * fr_consulta
                     * notas
                     * soporte
                     */

                    if($rsInventario->fields['fr_consulta'] == '1'){
                         $fr_consulta_txt = "Baja";   
                    }

                    if($rsInventario->fields['fr_consulta'] == '2'){
                        $fr_consulta_txt = "Media";   
                    }

                    if($rsInventario->fields['fr_consulta'] == '3'){
                        $fr_consulta_txt = "Alta";   
                    }
                    

                    $result[] = [
                        'id_inv_documental' => $rsInventario->fields['id_inv_documental'],
                        'uni_administrativa' => $rsInventario->fields['uni_administrativa'],
                        'ofi_productora' => $rsInventario->fields['ofi_productora'],
                        'no_orden' => $rsInventario->fields['no_orden'],
                        'objeto' => $rsInventario->fields['objeto'],
                        'codigo' => $rsInventario->fields['codigo'],
                        'nombre' => $rsInventario->fields['nombre'],
                        'sub_serie' => $rsInventario->fields['sub_serie'],
                        'fecha_ext_ini' => date('Y-m-d', strtotime($rsInventario->fields['fecha_ext_ini'])),
                        'fecha_ext_end' => date('Y-m-d', strtotime($rsInventario->fields['fecha_ext_end'])),
                        'modulo' => $rsInventario->fields['modulo'],
                        'estante' => $rsInventario->fields['estante'],
                        'no_folios' => $rsInventario->fields['no_folios'],
                        'soporte' => $rsInventario->fields['soporte'],
                        'fr_consulta' => $fr_consulta_txt,
                        'notas' => $rsInventario->fields['notas'],
                    ];
                    
                    $rsInventario->MoveNext();
                }
            }

            foreach($result as $value){



                /***
                 * 
                 *  <th>Unidad Administradora</th>
                    <th>Oficina Productora</th>

                    <th>Objetivo</th>
                    <th>Codigo</th>
                    <th>Nombre Serie</th>
                    <th>Nombre SubSerie </th>
                    <th>Fechas Extremas </th>

                    <th>Modulo</th>
                    <th>Estante</th>
                    <th>No Folios</th>
                    <th>Soporte</th>
                    <th>Frecuencia</th>

                    <th>Accion</th> <!-- unidad de conservacion / documentos -->
                 * 
                 * 
                 */

                $tableInventario .= "<tr>";
                    # datos del radicado
                    $tableInventario .= "<td>".$value['uni_administrativa']."</td>";  
                    $tableInventario .= "<td>".$value['ofi_productora']."</td>";  
                    $tableInventario .= "<td>".$value['objeto']."</td>";                
                    $tableInventario .= "<td>".$value['codigo']."</td>";  
                    $tableInventario .= "<td>".$value['nombre']."</td>";    
                    $tableInventario .= "<td>".$value['sub_serie']."</td>";   
                    $tableInventario .= "<td>".$value['fecha_ext_ini'].'@'.$value['fecha_ext_end']."</td>";  
                    $tableInventario .= "<td>".$value['no_folios']."</td>"; 
                    $tableInventario .= "<td>".$value['soporte']."</td>";  
                    $tableInventario .= "<td>".$value['fr_consulta']."</td>"; 
                    # action
                    $tableInventario .= 
                    
                    "<td class='btn-action-content'>"
                        .'<span style="color: #7a7a7a;" class="glyphicon glyphicon-folder-open btn-action" data-toggle="tooltip" title="Ver Documentos." aria-hidden="true" onclick="documet(\''.$value['id_inv_documental'].'\')"></span>'   // Ver Documentos,     
                        .'<span style="color: #7a7a7a;" class="glyphicon glyphicon-eye-open btn-action"  data-toggle="tooltip" title="Ver Detalles."  aria-hidden="true" onclick="view(\''.$value['id_inv_documental'].'\')"></span>'   // Ver historico</th>
                    ."</td>";  # glyphicon glyphicon-save-file

                $tableInventario .= "</tr>";

            }

            echo $tableInventario; 

        break;

        # Inventraio Documnetal -- Documentos -- 
        case '6':
            
            include "$dir_raiz/config.php";
            require_once("../../include/db/ConnectionHandler.php");

            $db = new ConnectionHandler("$dir_raiz");
            $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);

            $request = $_POST; 

            $queryInventario = "SELECT * FROM inventario_documentos WHERE id_inv_documental =".$request['id_inv_documental'];
            
            $queryInventario .= " ORDER BY id_inv_documental DESC";

            # print_r($queryInventario);die(); # SQL
            $rsInventario = $db->conn->query($queryInventario);

            if(isset($rsInventario)){
                while (!$rsInventario->EOF) {

                    /****   inventario_documentos
                     * 
                     * id_inv_documento
                     * original_inv_doc
                     * nombre_inv_doc
                     * ruta_inv_doc
                     * tipo_inv_doc
                     * tamano_inv_doc
                     * user_inv_doc
                     * creacion_inv_doc
                     */
    
                    # usuario
                    $usuario = $db->conn->query("SELECT * FROM usuario WHERE usua_doc = '".$rsInventario->fields["user_inv_doc"]."'"); 
       
                    # link
                    $link = $rsInventario->fields['ruta_inv_doc']; # "http://10.11.12.1/orfeo-6.0/bodega/" . 
                    $doc2_b64 = base64_encode(file_get_contents($link));
    
                    $result[] = [
                        'id_inv_documento' => $rsInventario->fields['id_inv_documento'],
                        'original_inv_doc' => $rsInventario->fields['original_inv_doc'],
                        'nombre_inv_doc' => $rsInventario->fields['nombre_inv_doc'],
                        'tipo_inv_doc' => $rsInventario->fields['tipo_inv_doc'],
                        'tamano_inv_doc' => $rsInventario->fields['tamano_inv_doc'],
                        'usua_nomb' => $usuario->fields['usua_nomb'],
                        'creacion_inv_doc' => $rsInventario->fields['creacion_inv_doc'],
                        'link' => $link,
                        'link_document' => $doc2_b64
                    ];
    
                    $rsInventario->MoveNext();
                }
            }

            ob_start();
            include './views/documents-inv.php';
            echo ob_get_clean(); 

        break;

        # Inventario Documental -- Detalles -- 
        case '7':
            
            include "$dir_raiz/config.php";
            require_once("../../include/db/ConnectionHandler.php");

            $db = new ConnectionHandler("$dir_raiz");
            $db->conn->SetFetchMode(ADODB_FETCH_ASSOC);


            $request = $_POST; 

            $queryInventario = "SELECT * FROM inventario_documental WHERE id_inv_documental =".$request['id_inv_documental'];
   
            $queryInventario .= " ORDER BY id_inv_documental DESC  LIMIT 100";
            # print_r($queryInventario);die(); # SQL
            $rsInventario = $db->conn->query($queryInventario);

            $tableInventario = "";

            if(isset($rsInventario)){
                while (!$rsInventario->EOF) {

                    /**  
                     * Inventario_Documental
                     * 
                     * id_inv_documental
                     * uni_administrativa
                     * ofi_productora
                     * no_orden
                     * objeto
                     * codigo
                     * nombre
                     * sub_serie
                     * fecha_ext_ini
                     * fecha_ext_end
                     * caja
                     * carpeta
                     * tomo
                     * otro
                     * modulo
                     * estante
                     * no_folios
                     * fr_consulta
                     * notas
                     * soporte
                     */

                    if($rsInventario->fields['fr_consulta'] == '1'){
                        $fr_consulta_txt = "Baja";   
                    }

                    if($rsInventario->fields['fr_consulta'] == '2'){
                        $fr_consulta_txt = "Media";   
                    }

                    if($rsInventario->fields['fr_consulta'] == '3'){
                        $fr_consulta_txt = "Alta";   
                    }

                    $details = [
                        'id_inv_documental' => $rsInventario->fields['id_inv_documental'],
                        'uni_administrativa' => $rsInventario->fields['uni_administrativa'],
                        'ofi_productora' => $rsInventario->fields['ofi_productora'],
                        'no_orden' => $rsInventario->fields['no_orden'],
                        'objeto' => $rsInventario->fields['objeto'],
                        'codigo' => $rsInventario->fields['codigo'],
                        'nombre' => $rsInventario->fields['nombre'],
                        'sub_serie' => $rsInventario->fields['sub_serie'],
                        'fecha_ext_ini' => date('Y-m-d', strtotime($rsInventario->fields['fecha_ext_ini'])),
                        'fecha_ext_end' => date('Y-m-d', strtotime($rsInventario->fields['fecha_ext_end'])),
                        'caja' => $rsInventario->fields['caja'],
                        'carpeta' => $rsInventario->fields['carpeta'],
                        'tomo' => $rsInventario->fields['tomo'],
                        'otro' => $rsInventario->fields['otro'],
                        'modulo' => $rsInventario->fields['modulo'],
                        'estante' => $rsInventario->fields['estante'],
                        'no_folios' => $rsInventario->fields['no_folios'],
                        'soporte' => $rsInventario->fields['soporte'],
                        'fr_consulta' => $fr_consulta_txt,
                        'notas' => $rsInventario->fields['notas'],
                    ];
                    
                    $rsInventario->MoveNext();
                }
            }

            ob_start();
            include './views/view-inv.php';
            echo ob_get_clean(); 

        break;

    }

    function TRD($cod_radi, $coddocu){

        $dir_raiz = $_SESSION['dir_raiz'];
        include_once "$dir_raiz/include/db/ConnectionDB.php";
        $dbProcess = new ConnectionDB("$dir_raiz");
        $dbProcess->conn->SetFetchMode(ADODB_FETCH_ASSOC);

        // print_r($cod_radi); print_r(" - "); print_r($coddocu); die();
    
        /** Indica si el Radicado Ya tiene asociado algun TRD */
        $isql_TRDA = "SELECT * FROM SGD_RDF_RETDOCF WHERE RADI_NUME_RADI = '$cod_radi'";
        $rs_TRA = $dbProcess->conn->query($isql_TRDA);
        $radiNumero = $assoc == 0 ? $rs_TRA->fields["radi_nume_radi"] : $rs_TRA->fields["RADI_NUME_RADI"];
        $mrdTRD = $assoc == 0 ? $rs_TRA->fields["sgd_mrd_codigo"] : $rs_TRA->fields["SGD_MRD_CODIGO"];

        $selectTRD = "select distinct tp.sgd_tpr_descrip, sr.sgd_srd_descrip, srb.sgd_sbrd_descrip from sgd_mrd_matrird mrd";

        if ($radiNumero == '') {
            /* Para extraer la información que tiene asignado el anexo cargado, serie, subserie, tipodocumental */
            $trdAnex = "select sgd_sbrd_codigo, sgd_srd_codigo, sgd_tpr_codigo from anexos where anex_codigo='$coddocu'";
            $rsselect_TRA_anex = $dbProcess->conn->query($trdAnex);

            $codserieanex = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_srd_codigo"] : $rsselect_TRA_anex->fields["SGD_SRD_CODIGO"];
            $tsubanex = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_sbrd_codigo"] : $rsselect_TRA_anex->fields["SGD_SBRD_CODIGO"];
            $tdocanex = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_tpr_codigo"] : $rsselect_TRA_anex->fields["SGD_TPR_CODIGO"];

            $selectTRD .= " inner join sgd_tpr_tpdcumento tp on mrd.sgd_tpr_codigo=tp.sgd_tpr_codigo
                    inner join sgd_sbrd_subserierd srb on mrd.sgd_sbrd_codigo=srb.sgd_sbrd_codigo 
                    inner join sgd_srd_seriesrd sr on mrd.sgd_srd_codigo=sr.sgd_srd_codigo 
                where srb.sgd_srd_codigo=sr.sgd_srd_codigo and mrd.sgd_tpr_codigo = $tdocanex "
                    . "and mrd.sgd_srd_codigo = $codserieanex and mrd.sgd_sbrd_codigo = $tsubanex";
        } else {
            $selectTRD .= " inner join sgd_rdf_retdocf rdf on mrd.sgd_mrd_codigo=rdf.sgd_mrd_codigo 
                    inner join sgd_tpr_tpdcumento tp on mrd.sgd_tpr_codigo=tp.sgd_tpr_codigo
                    inner join sgd_sbrd_subserierd srb on mrd.sgd_sbrd_codigo=srb.sgd_sbrd_codigo 
                    inner join sgd_srd_seriesrd sr on mrd.sgd_srd_codigo=sr.sgd_srd_codigo 
                where  srb.sgd_srd_codigo=sr.sgd_srd_codigo and mrd.sgd_mrd_codigo = $mrdTRD and rdf.radi_nume_radi='$radiNumero'";
        }
        $rsselect_TRA = $dbProcess->conn->query($selectTRD);
        
        /* Se agrega esta*/
        $datoSgd_tpr_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_tpr_descrip"] : $rsselect_TRA_anex->fields["SGD_TPR_DESCRIP"];
        $datoSgd_srd_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_srd_descrip"] : $rsselect_TRA_anex->fields["SGD_SRD_DESCRIP"];
        $datoSgd_sbrd_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_sbrd_descrip"] : $rsselect_TRA_anex->fields["SGD_SBRD_DESCRIP"];

        if ($datoSgd_tpr_descrip != '') {
            $msg_TRD = $datoSgd_srd_descrip . ' /  <br>' . $datoSgd_sbrd_descrip . ' /  <br>' . $datoSgd_tpr_descrip;
        } else {
            $trdAnex = "select sgd_sbrd_codigo as SGD_SBRD_CODIGO, sgd_srd_codigo, sgd_tpr_codigo from anexos where anex_codigo='$coddocu'";
            $rsselect_TRA_anex = $dbProcess->conn->query($trdAnex);

            if ($rsselect_TRA_anex->fields["SGD_SBRD_CODIGO"] != '') {
                $srserie = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_srd_codigo"] : $rsselect_TRA_anex->fields["SGD_SRD_CODIGO"];
                $srbserie = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_sbrd_codigo"] : $rsselect_TRA_anex->fields["SGD_SBRD_CODIGO"];
                $srtipodoc = $assoc == 0 ? $rsselect_TRA_anex->fields["sgd_tpr_codigo"] : $rsselect_TRA_anex->fields["SGD_TPR_CODIGO"];
                /************************************************************************************** 
                 * Se consulta tabla por tabla la información correspondiente a la trd desde Skinascan *
                 * ************************************************************************************* */
                $infoserie = "select sgd_srd_descrip as SGD_SRD_DESCRIP from sgd_srd_seriesrd where sgd_srd_codigo=" . $srserie;
                $rsinfoserie = $dbProcess->conn->query($infoserie);
                $infosubserie = "select sgd_sbrd_descrip as SGD_SBRD_DESCRIP from sgd_sbrd_subserierd where sgd_srd_codigo=" . $srserie . " and sgd_sbrd_codigo=" . $srbserie;
                $rsinfosubserie = $dbProcess->conn->query($infosubserie);
                $infotipodoc = "select sgd_tpr_descrip as SGD_TPR_DESCRIP from sgd_tpr_tpdcumento where sgd_tpr_codigo=" . $srtipodoc;
                $rsinfotipodoc = $dbProcess->conn->query($infotipodoc);

                $msg_TRD = $rsinfoserie->fields["SGD_SRD_DESCRIP"] . ' / <br> ' . $rsinfosubserie->fields["SGD_SBRD_DESCRIP"] . ' / <br>' . $rsinfotipodoc->fields["SGD_TPR_DESCRIP"];
            } else {
                $msg_TRD = "No";
            }
        }

        return $msg_TRD;
      
    }

    function TRD_RADICADO($cod_radi) {

        $dir_raiz = $_SESSION['dir_raiz'];
        include_once "$dir_raiz/include/db/ConnectionDB.php";
        $dbProcess = new ConnectionDB("$dir_raiz");
        $dbProcess->conn->SetFetchMode(ADODB_FETCH_ASSOC);

        // print_r($cod_radi); die();
    
        /** Indica si el Radicado Ya tiene asociado algun TRD */
        $isql_TRDA = "SELECT * FROM SGD_RDF_RETDOCF WHERE RADI_NUME_RADI = '$cod_radi'";
        $rs_TRA = $dbProcess->conn->query($isql_TRDA);
        $radiNumero = $assoc == 0 ? $rs_TRA->fields["radi_nume_radi"] : $rs_TRA->fields["RADI_NUME_RADI"];
        $mrdTRD = $assoc == 0 ? $rs_TRA->fields["sgd_mrd_codigo"] : $rs_TRA->fields["SGD_MRD_CODIGO"];

        if ($radiNumero != null && $radiNumero != '') {

            $selectTRD = "select distinct tp.sgd_tpr_descrip, sr.sgd_srd_descrip, srb.sgd_sbrd_descrip from sgd_mrd_matrird mrd
                    inner join sgd_rdf_retdocf rdf on mrd.sgd_mrd_codigo=rdf.sgd_mrd_codigo 
                    inner join sgd_tpr_tpdcumento tp on mrd.sgd_tpr_codigo=tp.sgd_tpr_codigo
                    inner join sgd_sbrd_subserierd srb on mrd.sgd_sbrd_codigo=srb.sgd_sbrd_codigo 
                    inner join sgd_srd_seriesrd sr on mrd.sgd_srd_codigo=sr.sgd_srd_codigo 
                where  srb.sgd_srd_codigo=sr.sgd_srd_codigo and mrd.sgd_mrd_codigo = $mrdTRD and rdf.radi_nume_radi='$radiNumero'";
            $rsselect_TRA = $dbProcess->conn->query($selectTRD);

            $datoSgd_tpr_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_tpr_descrip"] : $rsselect_TRA_anex->fields["SGD_TPR_DESCRIP"];
            $datoSgd_srd_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_srd_descrip"] : $rsselect_TRA_anex->fields["SGD_SRD_DESCRIP"];
            $datoSgd_sbrd_descrip = $assoc == 0 ? $rsselect_TRA->fields["sgd_sbrd_descrip"] : $rsselect_TRA_anex->fields["SGD_SBRD_DESCRIP"];

            if ($datoSgd_tpr_descrip != null  && $datoSgd_tpr_descrip != '') {
                $msg_TRD = $datoSgd_srd_descrip . ' /  <br>' . $datoSgd_sbrd_descrip . ' /  <br>' . $datoSgd_tpr_descrip;
            } else {
                $msg_TRD = "No";
            }

        } else {
            $msg_TRD = "No";
        }

        return $msg_TRD;
    }

?>
