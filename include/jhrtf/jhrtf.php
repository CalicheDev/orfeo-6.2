<?php

/*  CLASS jhrtf
 *  @autor JAIRO LOSADA - SIXTO
 *  @fecha 2003/10/16
 *  @version 0.1
 *  Permite hacer combinaci�n de correspondencia desde php con filas rtf-
 *  @VERSION 0.2
 *  @fecha 2004/01/22
 *  Se a�ade combinaci�n masiva
 *  @fecha 2004/08/30
 *  Se a�aden las funci�nes:
 *  setTipoDocto(),verListado(),setDefinitivo(),mostrarError(),getNumColEnc(),validarEsp(),validarLugar(),validarTipo()
 *  validarRegistrosObligsCsv(),hayError(),cargarOblPlant(),cargarObligCsv(),cargarCampos(),validarArchs()
 *
 */
require_once("$ruta_raiz/include/pdf/class.ezpdf.inc");
require_once("$ruta_raiz/class_control/Esp.php");
require_once("$ruta_raiz/class_control/Dependencia.php");
require_once("$ruta_raiz/include/tx/Historico.php");
require_once("$ruta_raiz/class_control/Radicado.php");
require_once("$ruta_raiz/include/tx/Expediente.php");
require_once("$ruta_raiz/include/tx/Flujo.php");
require "$ruta_raiz/include/jh_class/funciones_sgd.php";
include_once "$ruta_raiz/config.php";

class jhrtf {

    var $archivo_insumo;  // Ubicacion fisica del archivo que indica como habra de realizarce la combinacion
    var $alltext;          // ubicacion fisica del archivo a convertir
    var $texto_masivo;     // utilizado para combinar el vualquier texto
    var $encabezado;
    var $datos;
    var $ruta_raiz;
    var $definitivo;
    var $codigo_envio;
    var $radi_nume_grupo;
    //Contiene los campos obligatorios del archivo CSV
    var $camObligCsv;
    //Contiene los campos obligatorios de la plantilla
    var $camObligPlantilla;
    //Contiene los posibles errores hallados en el encabezado
    var $errorEncab;
    //Contiene los posibles errores hallados en la plantilla
    var $errorPlant;
    //Contiene los posibles errores hallados en los lugares referenciados en el CSV
    var $errorLugar;
    //Contiene los posibles errores hallados en las ESP referenciados en el CSV
    var $errorESP;
    //Contiene los posibles errores de completitud del CSV
    var $errorComplCsv;
    //Contiene los posibles errores del campo tipo de registro del CSV
    var $errorTipo;
    //Contiene los posibles errores del campo direcci�n del CSV
    var $errorDir;
    //Contiene los posibles errores del campo es anexo de un radicado
    var $errorRadAnexo;
    //Contiene los posibles errores del campo nombre del CSV
    var $errorNomb;
    var $arcPDF;
    //Contiene el path del archivo plantilla
    var $arcPlantilla;
    //Contiene el path del archivo CSV
    var $arcCSV;
    //Contiene el path del archivo Final
    var $arcFinal;
    //Contiene el path del archivo Temporal
    
    
    //Contiene los posibles errores hallados en los funcionario referenciados en el CSV
    var $errorFuncionario;
    

    var $arcTmp;
    var $conexion;
    var $pdf;
    var $arregloEsp;
    var $arrCodDepto;
    var $arrCodMuni;
    var $arrCodPais;
    var $arrCodCont;
    var $tipoDocto;
    // Guarda la el objeto CLASS_CONTROL
    var $btt;
    //almacena la conexion que permite efectuar algunas labores de masiva
    var $handle;
    //almacena el resultado obtenido en la combinacion masiva
    var $resulComb;
    var $objExp;
    var $codProceso;
    var $codFlujo;
    var $codArista;

    /**
     * Constructor que carga en la clase los parametros relevantes del proceso de combinaci�n de documentos
     * @param	$archivo_insumo	string	es el path hacia el archivo que contiene los ratos de la combinaci�n
     * @param	$ruta_raiz	string	es el path hacia la raiz del directorio de ORFEO
     * @param	$arcPDF	string	es el path hacia el archivo PDF que habr� de mostrar el resultado de la combinaci�n
     * @param	$db	ConnectionHandler	Manejador de la conexi�n con la base de datos
     */
    function jhrtf($archivo_insumo, $ruta_raiz, $arcPDF, &$db) {
//        error_log('---> ' . $archivo_insumo . '---> ' . $ruta_raiz . '---> ' . $arcPDF . '---> ' . $db);
        $this->archivo_insumo = $archivo_insumo;
        $this->ruta_raiz = $ruta_raiz;
        $this->arcPDF = $arcPDF;
        $this->cargarInsumo();
        $this->conexion = & $db;
//        error_log('---> ' . $this->archivo_insumo . '---> ' . $this->ruta_raiz . '---> ' . $this->arcPDF . '---> ' . $this->conexion,true);
    }

    /**
     * Funcion que carga en la clase el manejador de conexion con la base de datos, en caso de ser necesario
     * @param	$db	ConnectionHandler	Manejador de la conexi�n con la base de datos
     */
    function setConexion($db) {
        $this->conexion = & $db;
    }

    /*
     * Funcion encargada de gestionar en la base de datos la transacci�n que implica la combinaci�n del documento, desde el archivo CSV
     * @param	$dependencia	string	es la dependencia del usuario que realiza la combinaci�n
     * @param	$codusuario	string	es el codigo del usuario que realiza la combinaci�n
     * @param	$usua_doc	string	es numero del documento del usuario que realiza la combinaci�n
     * @param	$usua_nomb	string	es el nombre del usuario que realiza la combinaci�n
     * @param	$depe_codi_territorial	string	es el nombre de la territorial a la que pertenece el usuario usuario que realiza la combinaci�n
     */
    /***
    Autor: Jenny Gamez
    Fecha: 2019-10-02
    Info: En la información de la función   (), la cual se encarga de realizar la comvinación de correspondencia, se adiciona una nueva condición para que evalue la parte de si se radica a los funcionarios para las comunicaciones internas.
    ***/
    function combinar_csv($dependencia, $codusuario, $usua_doc, $usua_nomb, $depe_codi_territorial, $codiTRD, $TipoRad) {
       
        //Var que contiene el arreglo de radicados genrados a partir de la masiva
        $arrRadicados = array();
        //Instancia de la dependencia
        $objDependecia = new Dependencia($this->conexion);
        $objDependecia->Dependencia_codigo($dependencia);
        //Almacena la secuencia de radicacion para esta dependencia
        $secRadicacion = "secr_tp" . $TipoRad . "_" . $objDependecia->getSecRadicTipDepe($dependencia, $TipoRad);
        $driver = $this->conexion->driver;
	    $this->conexion->conn->debug = false;
        $archivo = $this->arcFinal;
        $archivo = trim(substr($archivo, strpos($archivo, "bodega") + 6, strlen($archivo) - strpos($archivo, "bodega") + 6));
        
        // INICIALIZA EL PDF
        $this->pdf = new Cezpdf("LETTER", "landscape");
        $objHist = new Historico($this->conexion);
        $year = date("Y");
        $day = date("d");
        $month = date("m");
        // orientaci�n izquierda
        $orientCentro = array("left" => 0);
        // justificaci�n centrada
        $justCentro = array("justification" => "center");
        $estilo1 = array("justification" => "left", "leading" => 8);
        $estilo2 = array("left" => 0, "leading" => 12);
        $estilo3 = array("left" => 0, "leading" => 15);
        $this->pdf->ezSetCmMargins(1, 1, 3, 2); //top,botton,left,right
        /* Se establece la fuente que se utilizara para el texto. */
        $this->pdf->selectFont($this->ruta_raiz . "/include/pdf/fonts/Times-Roman.afm");
        $this->pdf->ezText("LISTADO DE RADICACION MASIVA\n", 15, $justCentro);
        $this->pdf->ezText("Dependencia: $dependencia \n", 12, $estilo2);
        $this->pdf->ezText("Usuario Responsable: $usua_nomb \n", 12, $estilo2);
        $this->pdf->ezText("Fecha: $day-$month-$day \n", 12, $estilo2);
        $this->pdf->ezText($txtformat, 12, $estilo2);
        $data = array();
        $columna = array();
        $contador = 0;

        $valores = array();

        require_once $this->ruta_raiz . "/class_control/class_control.php";
        $this->btt = new CONTROL_ORFEO($this->conexion);
        echo "<center><table border=1 width 80% cellpadding='0' cellspacing='5' class='borde_tab'>";
        echo "<tr><td class='titulos4'>Registro</td><td class='titulos4'>Radicado</td><td class='titulos4' >Nombre</td><td class='titulos4'>Direccion</td><td class='titulos4'>Depto</td><td class='titulos4'>Municipio</td></tr>";
        //Referencia el archivo a abrir
        $ruta = $this->ruta_raiz ."/bodega/masiva/" . $this->archivo_insumo;
        clearstatcache();
        unlink($ruta);

        /** ------------------------------------------------------------- **/
        setlocale(LC_TIME, 'es_ES.UTF-8');
        $fecha_hoy_corto = strftime("%d %B %Y");
        require_once $this->ruta_raiz .'/include/phpword/PHPWord.php';
    
        $fp = fopen($ruta, 'wb');
        if ($fp) {
            fputs($fp, "plantilla=$this->arcPlantilla" . "\n");
            fputs($fp, "csv=$this->arcCSV" . "\n");
            fputs($fp, "archFinal=$this->arcFinal" . "\n");
            fputs($fp, "archTmp=$this->arcTmp" . "\n");
            
            // Comentariada por HLP. Cambiar , por ;
            fputs($fp, implode(";", $this->encabezado[0]).";*RAD_S*;*F_RAD_S*;*ASUNTO*;*FUNCIONARIO*;*DEPTO_NOMBRE*"."\n");
            $encabezado = implode(";", $this->encabezado[0]).";*RAD_S*;*F_RAD_S*;*ASUNTO*;*FUNCIONARIO*;*DEPTO_NOMBRE*";

            //Recorre el arrego de los datos
            for ($ii = 0; $ii < count($this->datos); $ii++) {
                $i = 0;
                $numeroExpediente = "";
                // Aqui se accede a la clase class_control para actualizar expedientes.
                $ruta_raiz = $this->ruta_raiz;

                // Por cada etiqueta de los campos del encabezado del CSV efect�a un reemplazo
                foreach ($this->encabezado[0] as $campos_d) {
                    
                    if (strlen(trim($this->datos[$ii][$i])) < 1){
                        $this->datos[$ii][$i] = "<ESPACIO>";
                    }
                        
                    $dato_r = iconv("ISO-8859-1", "UTF-8",trim($this->datos[$ii][$i]));
                    $texto_tmp = str_replace($campos_d, $dato_r, $texto_tmp);
                    //unhtmlspecialchars($string)
                    if ($campos_d == "*TIPO*"){ $tip_doc = $dato_r; }
                    if ($campos_d == "*NOMBRE*"){ $nombre = $dato_r; }
                    if ($campos_d == "*CORREO*"){ $mail_us1 = $dato_r; }
                    if ($campos_d == "*DOCUMENTO*"){ $doc_us1 = $dato_r; }
                    if ($campos_d == "*NOMBRE*"){ $nombre_us1 = $dato_r; }
                    if ($campos_d == "*PRIM_APEL*"){ $prim_apell_us1 = $dato_r; }                      
                    if ($campos_d == "*SEG_APEL*"){ $seg_apell_us1 = $dato_r; }
                    if ($campos_d == "*DIGNATARIO*"){ $otro_us1 = $dato_r; }
                    if ($campos_d == "*CARGO*"){ $cargo_us1 = $dato_r; }
                    if ($campos_d == "*DIR*"){ $direccion_us1 = $this->btt->eliminar_tildes($dato_r); }
                    if ($campos_d == "*TELEFONO*"){ $telefono_us1 = $dato_r; }                        
                    if ($campos_d == "*MUNI_NOMBRE*"){ $muni_codi = $dato_r; }  
                    if ($campos_d == "*DEPTO_NOMBRE*"){ $dpto_codi = $dato_r; }                     
                    if ($campos_d == "*ID*"){ $sgd_esp_codigo = $dato_r; }
                    if ($campos_d == "*DESC_ANEXOS*"){ $desc_anexos = $dato_r; }
                    if ($campos_d == "*MUNI_NOMBRE*"){ $muni_nombre = $this->btt->eliminar_tildes($dato_r); }
                    if ($campos_d == "*DEPTO_NOMBRE*"){ $dpto_nombre = $this->btt->eliminar_tildes($dato_r); }
                    if ($campos_d == "*PAIS_NOMBRE*"){ $pais = $dato_r; }
                    if ($campos_d == "*TIPO_DOC*"){ $tdoc = trim($dato_r); }
                    if ($campos_d == "*NUM_EXPEDIENTE*"){ $numeroExpediente = trim($dato_r); }
                    if ($campos_d == "*ESP_CODIGO*") {
                        $codigoESP = $dato_r;
                        if ($codigoESP == "<ESPACIO>") { $codigoESP = null; }
                    }
                    if ($campos_d == "*RAD_ANEXO*") {
                        $radicadopadre = $dato_r;
                        $tipoanexo = 0;

                        if ($radicadopadre == "<ESPACIO>") {
                            $radicadopadre = "";
                            $tipoanexo = "";
                        }
                    } else {
                        $radicadopadre = "";
                    }
                    if ($campos_d == "*ASUNTO*"){ $asunto = $this->btt->eliminar_tildes(trim($dato_r)); }
                    if ($campos_d == "*NUIR*"){ $doc_us1 = trim($dato_r); }
                    if ($campos_d == "*FUNCIONARIO*"){ $usua_nomb = $this->btt->eliminar_tildes($_SESSION["usua_nomb"]); }
                    if ($campos_d == "*DEPTO_NOMBRE*"){ $depe_nomb = $this->btt->eliminar_tildes($_SESSION["depe_nomb"]); }

                    $tipoanexo = "0";
                    $cuentai = "";
                    $documento_us3 = "";
                    $med = "";
                    $fec = "";

                    $ane = "";
                    //$pais="COLOMBIA";
                    $carp_codi = "12";
                    $i++;
                }
                $tip_rem = "1";
                // Si no se especifico el tipo de documento
                IF (!$tdoc)
                    $tdoc = 0;
                $pais_codi = $this->arrCodPais[$pais . $dpto_nombre . $muni_nombre];
                if ($pais_codi == '') {
                    $pais_codi = '170';
                }
                $dpto_codi = $pais_codi . "-" . $this->arrCodDepto[$dpto_nombre];
                $muni_codi = $dpto_codi . "-" . $this->arrCodMuni[$dpto_nombre . $muni_nombre];

                $tmp_objMuni = new Municipio($this->conexion);   //Creamos esto para traer el codigo del continente y transmitirlo
                $tmp_objMuni->municipio_codigo($dpto_codi, $muni_codi); //por las diferentes tablas.
                $cont_codi = $tmp_objMuni->get_cont_codi();
                $muni_codi = $cont_codi . "-" . $muni_codi;

                //Se agregan las dos variables siguientes, para corregir el error que se estaba presentando en la radicación masiva
                $codigo_depto = $this->arrCodDepto[$dpto_nombre];
                $codigo_muni = $this->arrCodMuni[$dpto_nombre . $muni_nombre];
                //Fin Variables agregadas

                $muni_us1 = $muni_codi;
                $codep_us1 = $dpto_codi;                
                $nombre_us = "$nombre_us1 $prim_apell_us1 $seg_apell_us1";

                unset($tmp_objMuni);
                $documento_us3 = $codigoESP;
                if (!$documento_us3)
                    $documento_us3 = null;
                
                $selectDato = 'select * from sgd_mrd_matrird where sgd_mrd_codigo='.$codiTRD;
                $rsDato = $this->conexion->query($selectDato);
                
                $this->tipoDocto = isset($rsDato->fields['sgd_tpr_codigo']) ? $rsDato->fields['sgd_tpr_codigo'] : $rsDato->fields['SGD_TPR_CODIGO'];
                $srdCodigo = isset($rsDato->fields['sgd_srd_codigo']) ? $rsDato->fields['sgd_srd_codigo'] : $rsDato->fields['SGD_SRD_CODIGO'];
                $sbrdCodigo = isset($rsDato->fields['sgd_sbrd_codigo']) ? $rsDato->fields['sgd_sbrd_codigo'] : $rsDato->fields['SGD_SBRD_CODIGO'];
                
                //Si se trata de una combinacion de correspondencia definitiva
                // Segun el tipo de remitente se graba en la tabla respectiva.
                if ($this->definitivo == "si") {     
                    // 0 - ESP 1 - OTRA EMPRESA  2 - PERSONA NATURAL  6 - FUNCIONARIOS
                    $nurad = $this->btt->radicar_salida_masiva($tipoanexo, $cuentai, $documento_us3, $med, $fec, $radicadopadre, $codusuario, $tip_doc, $ane, $pais, $asunto, $dependencia, $tip_rem, $usua_doc, $this->tipoDocto, $muni_codi, $archivo, $usua_doc, $depe_codi_territorial, $secRadicacion, $numeroExpediente,$driver);
                    
                    //by skinatech, grabamos datos de dias de termino
                    $mod = 0;
                    $q = $this->tipoDocto;
                    $isql1 = "SELECT SGD_TPR_TERMINO FROM SGD_TPR_TPDCUMENTO WHERE SGD_TPR_CODIGO='$q'";
                    $rs = $this->conexion->query($isql1);

                    $birds2 = isset($rs->fields['sgd_tpr_termino']) ? $rs->fields['sgd_tpr_termino'] : $rs->fields['SGD_TPR_TERMINO'];
                    if(!isset($birds2) && $birds2 == ''){
                        $birds2 = '5';			
                    }

                    $isql2 = "insert into sgd_dt_radicado values ('$nurad',$birds2)";
                    $rs = $this->conexion->query($isql2);
                    
                    if (strlen($numeroExpediente) >= 10) {
                        $this->objExp = new Expediente($this->conexion);
                        $resultadoExp = $this->objExp->insertar_expediente($numeroExpediente, $nurad, $dependencia, $codusuario, $usua_doc);
                        $observa = "Por Rad. Masiva.";
                        if ($this->codProceso) {
                            $radicados[] = $nurad;
                            $tipoTx = 50;
                            $objFlujo = new Flujo($this->conexion, $this->codProceso, $usua_doc);
                            $expEstadoActual = $objFlujo->actualNodoExpediente($numeroExpediente);
                            $objFlujo->cambioNodoExpediente($numeroExpediente, $nurad, $this->codFlujo, $this->codArista, 1, $observa, $this->codProceso);
                        }
                    }

                    $nombre_us1 = trim($nombre_us1);
                    $direccion_us1 = trim($direccion_us1);
                    if ($tip_doc == 2) {
                        $codigo_us = $this->btt->grabar_usuario($doc_us1, $nombre_us1, $direccion_us1, $prim_apell_us1, $seg_apell_us1, $telefono_us1, $mail_us1, $codigo_depto, $codigo_muni, $tip_doc,$driver);
                        $tipo_emp_us1 = 0;
                        $documento_us1 = $codigo_us;
                    }
                    if ($tip_doc == 1) {
                        $codigo_oem = $this->btt->grabar_oem($doc_us1, $nombre_us1, $direccion_us1, $prim_apell_us1, $seg_apell_us1, $telefono_us1, $mail_us1, $codigo_depto, $codigo_muni,$driver);
                        $tipo_emp_us1 = 2;
                        $documento_us1 = $codigo_oem;
                    }
                    /* By - Skinatech
                     * Fecha - 02 octubre 2019
                     * Se agrega la opción para validar la infrmación de un funcionario, este es el codigo 6 */
                    if ($tip_doc == 6) {
                        
                        $tipo_emp_us1 = 6;
                        $documento_us1 = $doc_us1;
                        $codigo_funcionario = $doc_us1;

                        if($codigo_funcionario){
                            $selectUsuario = "select depe_codi, usua_codi from usuario where usua_doc='$codigo_funcionario'";
                            $rsSelectUsuario = $this->conexion->query($selectUsuario);

                            if (!$rsSelectUsuario->EOF){
                                $Radi_depe_codi = $rsSelectUsuario->fields['DEPE_CODI'];
                                $Radi_usua_codi = $rsSelectUsuario->fields['USUA_CODI'];
                            }

                            $updateRadicado = "update radicado set radi_usua_actu=$Radi_usua_codi, radi_depe_actu='$Radi_depe_codi', carp_codi=0, carp_per=0 where radi_nume_radi='$nurad'";
                            $rsUpdateRadicado = $this->conexion->query($updateRadicado);
                        }
                        $cc_documento_us1 = $codigo_funcionario;
                    }
                    if ($tip_doc == 0) {
                        $sgd_esp_codigo = $this->btt->grabar_bodega_empresas($doc_us1, $nombre_us1, $direccion_us1, $prim_apell_us1, $seg_apell_us1, $telefono_us1, $mail_us1, $codigo_depto, $codigo_muni,$driver);
                        $tipo_emp_us1 = 1;
                        $documento_us1 = $sgd_esp_codigo;
                    }

                    $documento_us2 = "";
                    $documento_us3 = "";
                    $mail_us1;
                    
                    $grbNombresUs1 = trim($nombre_us1) . " " . trim($prim_apel_us1) . " " . trim($seg_apel_us1);

                    $conexion = $this->conexion;
                    include "$ruta_raiz/radicacion/grb_direcciones.php";

                    // En esta parte registra el envio en la tabla SGD_RENV_REGENVIO
                    if (!$this->codigo_envio) {
                        $isql = "select max(SGD_RENV_CODIGO) as MAX FROM SGD_RENV_REGENVIO";
                        $rs = $this->conexion->query($isql);

                        if (!$rs->EOF)
                            $nextval = $rs->fields['MAX'];
                                                
                        $nextval++;
                        $this->codigo_envio = $nextval;
                        $this->radi_nume_grupo = $nurad;
                        $radi_nume_grupo = $this->radi_nume_grupo;
                    }
                    else {
                        $nextval = $this->codigo_envio;
                    }
                    
                    // By Skinatech - 14/08/2018
                    // Para la construcción del número de radicado, esto indica la parte inicial del radicado.
                    if ($estructuraRad == 'ymd'){
                        $num = 8;
                    }elseif ($estructuraRad == 'ym') {
                        $num = 6;
                    }else {
                        $num = 4;
                    }
                        
                    $dep_radicado = substr($verrad_sal, $num, $longitud_codigo_dependencia);
                    $carp_codi = substr($dep_radicado, 0, 2);
                    $dir_tipo = 1;
                    $nombre_us = substr(trim($nombre_us), 0, 29);
                    $direccion_us1 = substr(trim($direccion_us1), 0, 29);
                    if (!$muni_nomb)
                        $muni_nomb = $muni_tmp1;
                    if (!$valor_unit)
                        $valor_unit = 0;
                    if (!$planilla)
                        $planilla = '';
                    //		
                    $isql = "INSERT INTO SGD_RENV_REGENVIO (USUA_DOC, SGD_RENV_CODIGO, SGD_FENV_CODIGO, SGD_RENV_FECH,
						RADI_NUME_SAL, SGD_RENV_DESTINO, SGD_RENV_TELEFONO, SGD_RENV_MAIL, SGD_RENV_PESO, SGD_RENV_VALOR,
						SGD_RENV_CERTIFICADO, SGD_RENV_ESTADO, SGD_RENV_NOMBRE, SGD_DIR_CODIGO, DEPE_CODI, SGD_DIR_TIPO,
						RADI_NUME_GRUPO, SGD_RENV_PLANILLA, SGD_RENV_DIR, SGD_RENV_PAIS, SGD_RENV_DEPTO, SGD_RENV_MPIO,
						SGD_RENV_TIPO, SGD_RENV_OBSERVA,SGD_DEPE_GENERA)
						VALUES
						($usua_doc, $nextval, null, " . $this->btt->sqlFechaHoy . ", '$nurad', '$muni_nomb', '$telefono_us1', '$mail','',
						null, 0, 1, '$nombre_us', NULL, '$dependencia', '$dir_tipo', '" . $this->radi_nume_grupo . "', NULL,
						'$direccion_us1', '$pais','$dpto_nombre', '$muni_nombre', 1, 'Masiva grupo " . $this->radi_nume_grupo . "',
						'$dependencia') ";

                    $rs = $this->conexion->query($isql);
                    if (!$rs) {
                        $this->conexion->conn->RollbackTrans();
                        die("<span class='etextomenu'>No se ha podido isertar la información en SGD_RENV_REGENVIO");
                    }
                    /*
                     * Registro de la clasificacion TRD
                     */
                    $isql = "INSERT INTO SGD_RDF_RETDOCF(USUA_DOC, SGD_MRD_CODIGO, SGD_RDF_FECH, RADI_NUME_RADI, DEPE_CODI, USUA_CODI)
						VALUES($usua_doc, $codiTRD," . $this->btt->sqlFechaHoy . ", '$nurad', '$dependencia', $codusuario )";
                    $rs = $this->conexion->query($isql);

                    if (!$rs) {
                        $this->conexion->conn->RollbackTrans();
                        die("<span class='etextomenu'>No se ha podido isertar la informaci&ocute;n en SGD_RENV_REGENVIO");
                    }
                } else {
                    $sec = $ii;
                    $sec = str_pad($sec, 5, "X", STR_PAD_LEFT);
                    $nurad = date("Y") . $dependencia . $sec . "1X";
                }
                setlocale(LC_TIME, 'es_ES.UTF-8');
                $fecha_hoy_corto = strftime("%d %B %Y");
                // Comentariada por HLP. Cambiar , por ;
                fputs($fp, implode(";", $this->datos[$ii]). ";$nurad;" . $fecha_hoy_corto . "\n");
                $valores[] = implode(";", $this->datos[$ii]). ";$nurad;" . $fecha_hoy_corto;

                $contador = $ii + 1;

                echo "<tr>
                        <td class='listado2'>$contador</td><td class='listado2' >$nurad</td>
                        <td class='listado2'>" . unhtmlspecialchars($nombre_us) . "</td>
                        <td class='listado2'>" . unhtmlspecialchars($direccion_us1) . "</td>
                        <td class='listado2'>$dpto_nombre</td>
                        <td class='listado2'>$muni_nombre</td>
                    </tr>";

                if (connection_status() != 0) {
                    echo "<h1>Error de conexión</h1>";
                    $objError = new CombinaError(NO_DEFINIDO);
                    echo ($objError->getMessage());
                    die;
                }
                $data = array_merge($data, array(array('#' => $contador, 'Radicado' => $nurad, 'Nombre' => $nombre_us, 'Direcci�n' => $direccion_us1, 'Departamento' => $dpto_nombre, 'Municipio' => $muni_nombre)));
                $arrRadicados[] = $nurad;
            }
            fclose($fp);
            echo "</table></center>";
            echo "<span class='info'>Numero de registros $contador</span><br>";
            $this->pdf->ezTable($data);
            $this->pdf->ezText("\n", 15, $justCentro);
            $this->pdf->ezText("Total Registros $contador \n", 15, $justCentro);
            $pdfcode = $this->pdf->ezOutput();
            $fp = fopen($this->arcPDF, 'wb');
            fwrite($fp, $pdfcode);
            fclose($fp);

            if ($this->definitivo == "si")
                $objHist->insertarHistorico($arrRadicados, $dependencia, $codusuario, $dependencia, $codusuario, "Radicado insertado del grupo de masiva $radi_nume_grupo", 30);
            $this->resulComb = $data;

            return $arreglo = ['encabezado' => $encabezado, 'valores' => $valores];
        } else
            exit("No se pudo crear el archivo $this->archivo_insumo");
    }

    function cargar_csv() {
        $h = fopen($this->ruta_raiz . "/bodega/masiva/" . $this->arcCSV, "r") or $flag = 2;
        if (!$h) {
            echo "<BR> No hay un archivo csv llamado *" . $this->ruta_raiz . "/bodega/masiva/" . $this->arcCSV . "*";
        } else {
            $this->alltext_csv = "";
            $this->encabezado = array();
            $this->datos = array();
            $j = -1;
//            while ($line = fgetcsv($h, 10000, ",")) {
            while ($line = fgetcsv($h, 10000, ";")){
                $j++;
                if ($j == 0){
                    $this->encabezado = array_merge($this->encabezado, array($line));
                }else{
                    $this->datos = array_merge($this->datos, array($line));
                }

            } // while get
            $c = 0;
            while ($c < count($this->datos)) {
                $c++;
            }
        }
    }

    /**
     * Gestiona la validaci�n de las archivos que intervienen en el proceso antes de invocar esta funci�n debe haberse invocado 	cargar_csv() y abrir();
     */
    function validarArchs() {
        $this->cargarObligCsv();
        $this->cargarOblPlant();

        //Recorre los campos abligatorios buscando que cada uno de ellos se encuentre en el emcabezado del archivo CSV
        for ($i = 0; $i < count($this->camObligCsv); $i++) {
            $sw = 0;
            foreach ($this->encabezado[0] as $campoEnc) {
                if ("*" . $this->camObligCsv[$i] . "*" == $campoEnc) {
                    $sw = 1;
                }
            }
            if ($sw == 0) {
                $this->errorEncab[] = $this->camObligCsv[$i];
            }
        }
        $this->validarTipo();
        $this->validarRegistrosObligsCsv();
        $this->validarLugar();
        $this->validarEsp();
        $this->validarDireccion();
        $this->validarNombre();
        $this->validarSiAnexo();
    }

    /**
     * Carga los campos obligatorios del tipo de archivo enviado como par�metro y lo hace en el arreglo referenciado en el arreglo definido como par�metro
     * @param $tipo     	es el tipo de archivo de masiva
     * @param $arreglo   es el arreglo donde han de quedar los capos abligatorios
     */
    function cargarCampos($tipo, &$arreglo) {
        $q = "select * from sgd_cob_campobliga where sgd_tidm_codi=$tipo";
        $rs = $this->conexion->query($q);
        while (!$rs->EOF) {
            $arreglo[] = $this->conexion->driver == "mysql" ? $rs->fields['sgd_cob_label'] : $rs->fields['SGD_COB_LABEL'];
            $rs->MoveNext();
        }
//        error_log(' ++ Arreglo ++ '.print_r($arreglo));
    }

    /**
     * Carga los campos obligatorios del tipo de archivo 2 o CSV
     */
    function cargarObligCsv() {
        $this->cargarCampos(2, $this->camObligCsv);
    }

    /**
     * Carga los campos obligatorios del tipo de archivo 1 o Plantilla
     */
    function cargarOblPlant() {
        $this->cargarCampos(1, $this->camObligPlant);
    }

    /**
     * Pregunta si existe alg�ntipo de error, que puede ser de emcabezado, pantilla, lugar, ESP,de completitud del CSV, o del tipo de registro, antes de llamar esta funci�n se debi� validar mediante  validarArchs(). En caso de error retorna true, de lo contrario falso.
     * @return	boolean
     */
    function hayError() {
        if (count($this->errorEncab) > 0 || count($this->errorPlant) > 0 || count($this->errorLugar) > 0 || count($this->errorESP) > 0 ||
                count($this->errorComplCsv) > 0 || count($this->errorTipo) > 0 || count($this->errorDir) > 0 || count($this->errorNomb) > 0 ||
                count($this->errorRadAnexo) > 0)
            return true;
        else
            return false;
    }

    /**
     * Busca si los campos obligatorios est�n completos en todos los registros del archivo CSV
     * Si existe alg�n error lo registra en el arreglo errorComplCsv
     */
    function validarRegistrosObligsCsv() { //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) { //Recorre todos campos obligatorios del CSV y los busca en cada registro
            for ($j = 0; $j < count($this->camObligCsv); $j++) {
                $col = $this->getNumColEnc($this->camObligCsv[$j]);
                $dato = $this->datos[$i][$col];
                //Si no halla alg�n campo obligatorio lo pone en el arreglo de errores
                if (strlen($dato) == 0) {
                    $this->errorComplCsv[] = "REG " . ($i + 1) . ": " . $this->camObligCsv[$j];
                }
            }
        }
    }

    /**
     * Busca si se ha de generar radicados como anexos, validando que el radicado relacionado exista. Para esto debe existir una columna en el archivo csv llamada *RAD_ANEXO*
     * Si existe alg�n error lo registra en el arreglo errorRadAnexo
     */
    function validarSiAnexo() {
        $colRadAnexo = $this->getNumColEnc("RAD_ANEXO");
        if ($colRadAnexo != -1) {
            $objRadicado = new Radicado($this->conexion);
            //Recorre todos los registros del CSV
            for ($i = 0; $i < count($this->datos); $i++) {
                $dato = $this->datos[$i][$colRadAnexo];
                //Si la columna que contiene el campo RAD_ANEXO se refiere a un radicado que no existe
                if (strlen(trim($dato)) > 0 && !$objRadicado->radicado_codigo($dato)) {
                    $this->errorRadAnexo[] = "REG RAD_ANEXO " . ($i + 1) . ": $dato";
                }
            }
        }
    }

    /**
     * Busca si el campo obligatorio TIPO existe correctamente  en todos los registros del archivo CSV
     * Si existe alg�n error lo registra en el arreglo errorTipo
     */
    function validarTipo() {
//        error_log(' ++ $this->datos ++ '.print_r($this->datos));
        $colTipo = $this->getNumColEnc("TIPO");
        //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) {
            $dato = $this->datos[$i][$colTipo];
            //Si la columna que contiene el campo TIPO no es correcta la referencia en en arreglo errorTipo
            if ($dato != "0" && $dato != "1" && $dato != "2" && $dato != "6") {
                $this->errorTipo[] = "REG TIPO " . ($i + 1) . ": ";
            }
        }
    }

    /**
     * Busca si los lugares referenciados en el archivo CSV son v�ildos. Si existe alg�n error lo registra en el arreglo errorLugar
     */
    function validarLugar() {
        $colDepto = $this->getNumColEnc("DEPTO_NOMBRE");
        $colMuni = $this->getNumColEnc("MUNI_NOMBRE");
        $colPais = $this->getNumColEnc("PAIS_NOMBRE");
        //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) {
            $dato_pais = $this->datos[$i][$colPais];
            $dato = $this->datos[$i][$colDepto];
            $dato_muni = $this->datos[$i][$colMuni];
            if ($dato_pais == '') {
                $dato_pais = 'COLOMBIA';
            }
            $q3 = "select * from SGD_DEF_PAISES where NOMBRE_PAIS='$dato_pais'";
            $rs = $this->conexion->query($q3);
            if ($rs) {
                $codigoPais = $this->conexion->driver == "mysql" ? $rs->fields['id_pais'] : $rs->fields['ID_PAIS'];
                $codigoCont = $this->conexion->driver == "mysql" ? $rs->fields['id_cont'] : $rs->fields['ID_CONT'];
                $q = "select * from departamento where dpto_nomb='$dato' and ID_PAIS=$codigoPais";
                $rs = $this->conexion->query($q);
                //Valida si el departamento es v�lido
                if (!$rs->EOF) {
                    $codigoDepto = $this->conexion->driver == "mysql" ? $rs->fields['dpto_codi'] : $rs->fields['DPTO_CODI'];

                    $q2 = "select * from municipio where muni_nomb='$dato_muni' and dpto_codi=$codigoDepto and ID_PAIS=$codigoPais";
                    $rs = $this->conexion->query($q2);

                    //Valida si el municipio es valido
                    if ($rs->EOF)
                        $this->errorLugar[] = "REG LUGAR " . ($i + 1) . ": $dato_muni ";
                    else {
                        $codigoMuni = $this->conexion->driver == "mysql" ? $rs->fields['muni_codi'] : $rs->fields['MUNI_CODI'];
                        $this->arrCodDepto[$dato] = $codigoDepto;
                        $this->arrCodMuni[$dato . $dato_muni] = $codigoMuni;
                        $this->arrCodPais[$dato_pais . $dato . $dato_muni] = $codigoPais;
                        $this->arrCodCont[$codigoCont . $dato_pais . $dato . $dato_muni] = $codigoCont;
                    }
                } else {
                    $this->errorLugar[] = "REG LUGAR " . ($i + 1) . ": $dato ";
                }
            } else {
                $this->errorLugar[] = "REG LUGAR " . ($i + 1) . ": $dato_pais ";
            }
        }
    }

    /**
     * Busca si las ESP referenciadas en el archivo CSV son v�ildas. Si existe alg�n error lo registra en el arreglo errorESP
     */
    function validarEsp() {
        $colESP = $this->getNumColEnc("NOMBRE");
        $colTipo = $this->getNumColEnc("TIPO");
        $esp = new Esp($this->conexion);

        //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) {
            if ($this->datos[$i][$colTipo] == 0) {
                $dato = $this->datos[$i][$colESP];
                //$dato_muni = $this->datos[$i][$colMuni];
                //Valida si la ESP v�lida
                if ($esp->Esp_nombre($dato)) {
                    $this->arregloEsp[$dato] = $esp->getId();
                } else {
                    $this->errorESP[] = "REG " . ($i + 1) . ": $dato ";
                }
            }
        }
    }

    /**
     * Valida que el campo de Nombre, 1er y 2o Apellido existan. Si existe alg�n error lo registra en el arreglo errorDireccion
     */
    function validarNombre() {
        $colNomb = $this->getNumColEnc("NOMBRE");
        $colPrimApe = $this->getNumColEnc("PRIM_APEL");
        $colsegApe = $this->getNumColEnc("SEG_APEL");

        //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) {
            $dato = $this->datos[$i][$colNomb];

            if ($colPrimApe != -1)
                $dato = $dato . " " . $this->datos[$i][$colPrimApe];

            if ($colsegApe != -1)
                $dato = $dato . " " . $this->datos[$i][$colsegApe];

            if (strlen($dato) > 95)
                $this->errorNomb[] = "REG " . ($i + 1) . ": $dato ";
        }
    }

    /**
     * Valida que el campo de direcci�n no exceda el m�ximo permitido. Si existe alg�n error lo registra en el arreglo errorDireccion
     */
    function validarDireccion() {
        $colDir = $this->getNumColEnc("DIR");

        //Recorre todos los registros del CSV
        for ($i = 0; $i < count($this->datos); $i++) {
            $dato = $this->datos[$i][$colDir];
            if (strlen($dato) > 95)
                $this->errorDir[] = "REG " . ($i + 1) . ": $dato ";
        }
    }

    /**
     * Retorna el n�mero de columna en que se encuentra el encabezado que le llegue como par�metro. Si no existe retorna -1
     * @param $nombCol		es el nombre de la columna o encabezado
     * @return   integer
     */
    function getNumColEnc($nombCol) {
        $i = -1;
        $sw = 0;
        
        //Recorre todo el encabezado
        foreach ($this->encabezado[0] as $campoEnc) {
            $i++;
            if ("*" . $nombCol . "*" == $campoEnc) {
                $sw = 1;
                break;
            }
        }
        if ($sw == 1)
            return($i);
        else
            return -1;
    }

    /**
     * Muestra los errores presentados en la validaci�n de los archivos
     */
    function mostrarError() {
        $auxErrrEnca = $this->errorEncab;
        $auxErrPlant = $this->errorPlant;
        $auxErrLugar = $this->errorLugar;
        $auxErrESP = $this->errorESP;
        $auxErrCmpCsv = $this->errorComplCsv;
        $auxErrorTipo = $this->errorTipo;
        $auxErrorDir = $this->errorDir;
        $auxErrorNom = $this->errorNomb;
        $auxErrorAnexo = $this->errorRadAnexo;
        $ruta_raiz = "../..";
        include "$ruta_raiz/radsalida/masiva/error_archivo.php";
    }

    /**
     * Cambia el valor  del atributo que indica si se trata de ina combinaci�n definitiva
     * @param $arg		nuevo valor de la variable, puede ser "si" o "no"
     */
    function setDefinitivo($arg) {
        $this->definitivo = $arg;
    }

    /**
     * Cambia el valor del atributo que indica la caracter�stica de los documentos a combinar
     * @param $tipo		nuevo valor de la variable
     */
    function setTipoDocto($tipo) {
        $this->tipoDocto = $tipo;
    }

    /**
     * Carga los datos del archivo insumo para la generaci�n de masiva
     */
    function cargarInsumo() {
//Referencia el archivo a abrir
//        error_log('----------> ');
        $fp = fopen("$this->ruta_raiz/bodega/masiva/$this->archivo_insumo", 'r');
        $i = 0;
        while (!feof($fp)) {
            $i++;
            $buffer = fgets($fp, 4096);
            if ($i == 1) {
                $this->arcPlantilla = trim(substr($buffer, strpos($buffer, "=") + 1, strlen($buffer) - strpos($buffer, "=")));
            }
            if ($i == 2) {
                $this->arcCSV = trim(substr($buffer, strpos($buffer, "=") + 1, strlen($buffer) - strpos($buffer, "=")));
            }
            if ($i == 3) {
                $this->arcFinal = trim(substr($buffer, strpos($buffer, "=") + 1, strlen($buffer) - strpos($buffer, "=")));
            }
            if ($i == 4) {
                $this->arcTmp = trim(substr($buffer, strpos($buffer, "=") + 1, strlen($buffer) - strpos($buffer, "=")));
            }
        }
        fclose($fp);
    }

    /**
     * Retorna el path del archivo insumo para masiva
     */
    function getInsumo() {
        return($this->archivo_insumo);
    }

    /**
     *
     * Deshace  una la transacci�n de correspondencia masiva en caso de no terminar satisfactoriamente
     */
    function deshacerMasiva() {
        $this->conexion->conn->RollbackTrans();
    }

    /**
     *
     * Confirma  una la transacci�n de correspondencia masiva en caso de terminar satisfactoriamente
     */
    function confirmarMasiva() { //$this->conexion->conn->debug = true;
        return ($this->conexion->conn->CompleteTrans());
    }

}

/**
 * Función que reemplaza caracteres con tíldes por sus contrapartes sin tílde, solo para mostrar por pantalla
 *
 * @param String $string
 * @return String
 */
function unhtmlspecialchars($string) {
    $string = str_replace('á', '&aacute;', $string);
    $string = str_replace('é', '&eacute;', $string);
    $string = str_replace('í', '&iacute;', $string);
    $string = str_replace('ó', '&oacute;', $string);
    $string = str_replace('ú', '&uacute;', $string);
    $string = str_replace('Á', '&Aacute;', $string);
    $string = str_replace('É', '&Eacute;', $string);
    $string = str_replace('Í', '&Iacute;', $string);
    $string = str_replace('Ó', '&Oacute;', $string);
    $string = str_replace('Ú', '&Uacute;', $string);
    $string = str_replace('ñ', '&ntilde;', $string);
    $string = str_replace('Ñ', '&Ntilde;', $string);

    return $string;
}

?>
