<?php
/**
 * Programa controlador que coordina la combinaci�n de correspondencia definitiva, una vez se ha generado la pruena desde  adjuntar_masiva.php
 * @author      Sixto Angel Pinz�n
 * @version     1.0
 */
session_start();

$tipoOld = $tipo;
if (!$tipo)
    $tipo = $tipoOld;

foreach ($_GET as $key => $valor)
    ${$key} = $valor;
foreach ($_POST as $key => $valor)
    ${$key} = $valor;

$archInsumo = $_POST['archInsumo'];
$arcPDF = $_POST['arcPDF'];
$dependencia = $_SESSION['dependencia'];
$tipoRad = $_POST['tipoRad'];
$usua_doc = $_SESSION['usua_doc'];
$codusuario = $_SESSION['codusuario'];
$codiTRD = $_GET['codiTRD'];
$extension = $_POST['extension'];
$arcPlantilla = $_POST['arcPlantilla'];
$archivo = $_POST['archivo'];
$depe_codi_territorial = $_SESSION["depe_codi_territorial"];
$usua_nomb = $_SESSION["usua_nomb"];

$assoc = $_SESSION['assoc'];
define('ADODB_ASSOC_CASE', $assoc);

$ruta_raiz = "../..";
include_once "$ruta_raiz/include/db/ConnectionHandler.php";
require_once "$ruta_raiz/include/jhrtf/jhrtf.php";
//require_once("$ruta_raiz/class_control/CombinaError.php");

$conexion = new ConnectionHandler($ruta_raiz);
$conexion->conn->StartTrans();
//$conexion->conn->debug = true;
$conexion->conn->SetFetchMode(ADODB_FETCH_ASSOC);
//Si no llega la dependencia recupera la sesi�n
if (!$_SESSION['dependencia'])
    include "$ruta_raiz/rec_session.php";
//variable con elementos de sesion
$phpsession = session_name() . "=" . session_id();
//variable con elementos de sesion
$params = $phpsession . "&krd=$krd&codiTRD=$codiTRD&dependencia=$dependencia&depe_codi_territorial=$depe_codi_territorial&usua_nomb=$usua_nomb&depe_nomb=$depe_nomb&tipo=$tipo";
//print ("luego de sesion");
$debug = true;

//Función que calcula el tiempo transcurrido
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

//Evita caractres especiales en la combinacion 
function caracteresEspeciales($campo) {
    $blacklist = array(':', '*', '#', '@', '(', ')'); //etc etc
    $campo = str_replace($blacklist, " ", $campo);
    return str_replace("&", "y", $campo);
}
?>

<html>
    <head>
        <link href="<?= $ruta_raiz . $ESTILOS_PATH2 ?>bootstrap.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="<?= $ruta_raiz . $_SESSION['ESTILOS_PATH_ORFEO'] ?>">
        <title>Prueba Masiva</title>
        <script>
            function regresar() {
                document.formDefinitivo.action = "menu_masiva.php?" + '<?= $params ?>';
                document.formDefinitivo.submit();
            }
            function abrirArchivoaux(url) {
                nombreventana = 'Documento';
                window.open(url, nombreventana, 'status, width=900,height=500,screenX=100,screenY=75,left=50,top=75');
                return;
            }
        </script>
    </head>
    <body>
        <center>
            <form action="adjuntar_defint.php?<?= $params ?>" method="post" enctype="multipart/form-data" name="formDefinitivo">
            <input type=hidden name=pNodo value='<?= $pNodo ?>'>
            <input type=hidden name=codProceso value='<?= $codProceso ?>'>
            <input type=hidden name=tipoRad value='<?= $tipoRad ?>'>
            <?php
                echo "<center><span class=etextomenu align=left><br>";
                echo '<div id="titulo" style="width: 60%;" align="center">Generaci&oacute;n de radicados definitivos</div>';
                echo "<center><TABLE border=1 width 58% cellpadding='0' cellspacing='5' class='borde_tab'>
                <TR ALIGN=LEFT><TD width=21% class='titulos2' >Dependencia :</td><td class='listado2'> " . $_SESSION['depe_nomb'] . "</TD>	<TR ALIGN=LEFT><TD class='titulos2' >Usuario responsable :</td><td class='listado2'>" . $_SESSION['usua_nomb'] . "</TD>
                <TR ALIGN=LEFT><TD class='titulos2' >Fecha :</td><td class='listado2'>" . date("d-m-Y - h:mi:s") . "</TD></TR></TABLE></center>";
            ?>
            <br>
            <center>
                <span class="info">Se ha realizado la combinaci&oacute;n de correspondencia DEFINITIVA</span><BR>
            </center>
            <br>
            <?php
            $time_start = microtime_float();
            //Verifica que el objeto de combinaci�n masiva se encuentre en la sesi�n, refrenciado como 'masiva'

            $masiva = new jhrtf($archInsumo, $ruta_raiz, $arcPDF, $conexion);
            $masiva->cargar_csv();
            $masiva->validarArchs();

            if (!$masiva) {
                echo("ERROR ! No llega la informacion de radicacion masiva");
            } else {
                $masiva->setConexion($conexion);
                $masiva->setDefinitivo("si");

                error_reporting(7);
                echo "<span class='leidos'>";
                $masiva->codProceso = $codProceso;
                list ($masiva->codFlujo, $masiva->codArista) = preg_split("/[\s-]+/", $pNodo);
                $masiva->tipoDocto = $tipo;
                $masiva->combinar_csv($dependencia, $codusuario, $usua_doc, $usua_nomb, $depe_codi_territorial, $codiTRD, $tipoRad);
//                $this->conexion->debug = true;
                $archInsumo = $masiva->getInsumo();
                error_reporting(7);
                include("$ruta_raiz/config.php");
                //El include del servlet hace que se altere el valor de la variable  $estadoTransaccion como 0 si se pudo procesar el documento,
                // -1 de lo contrario
//                $archivo
                $estadoTransaccion = -1;
                //El archivo que ingresó es odt, luego se utiliza el nuevo combinador
                if ($extension == 'odt') {
                    //Se incluye la clase que maneja la combinación masiva
                    include ( "$ruta_raiz/radsalida/masiva/OpenDocText.class.php" );
                    define('WORKDIR', '../../bodega/tmp/workDir/');
                    define('CACHE', WORKDIR . 'cacheODT/');
                    //Se abre archivo de insumo para lectura de los datos
                    $fp = fopen("$ruta_raiz/bodega/masiva/$archInsumo", 'r');
                    if ($fp) {
                        $contenidoCSV = file("$ruta_raiz/bodega/masiva/$archInsumo");
                        fclose($fp);
                    } else {
                        exit("No hay acceso para crear el archivo $archInsumo");
                    }
                    $accion = false;
                    $odt = new OpenDocText();
                    //Se establede el modo Debug, poner en true para pruebas y muestra mensajes de lo que va ocurriendo
                    $odt->setDebugMode(true);

                    //Se carga el archivo odt Original 
                    $odt->cargarOdt("$ruta_raiz/bodega/masiva/$arcPlantilla", $arcPlantilla);
                    $odt->setWorkDir(WORKDIR);
                    $accion = $odt->abrirOdt();

                    if (!$accion) {
                        die("<CENTER><table class=borde_tab><tr><td class=titulosError>Problemas en el servidor abriendo archivo ODT para combinaci&oacute;n.</td></tr></table>");
                    }

                    $odt->cargarContenido();

                    //Se recorre el archivo de insumo
                    foreach ($contenidoCSV as $line_num => $line) {

                        if ($line_num == 4) { //Esta línea contiene las variables a reemplazar
                            $cadaVariable = explode(';', $line);
                        } else if ($line_num > 4) { //Desde la línea 5 hasta el final del archivo de insumo están los datos de reemplazo
                            $cadaValor = explode(";", $line);

                            $cadaValor[1] = caracteresEspeciales($cadaValor[1]);
                            $cadaValor[2] = caracteresEspeciales($cadaValor[2]);
                            $cadaValor[7] = caracteresEspeciales($cadaValor[7]);
                            $cadaValor[8] = caracteresEspeciales($cadaValor[8]);
                            $cadaValor[9] = caracteresEspeciales($cadaValor[9]);

                            $docx->setVariable($cadaVariable, $cadaValor);
                        }

                        echo "";

                        if (connection_status() != 0) {
                            $objError = new CombinaError(NO_DEFINIDO);
                            echo ($objError->getMessage());
                            die;
                        }
                    }
                    $tipoUnitario = '0';

                    //Se guardan los cambios del archivo temporal para su descarga
                    $archivoF = $odt->salvarCambios(null, $archivo, $tipoUnitario);
                    $intBodega = strpos($archivoF, "/bodega");
                    if ($intBodega === false) {
                        $rutaTMP = $ruta_raiz . '/bodega';
                    } else {
                        $rutaTMP = $ruta_raiz;
                    }
                    //Se limpia el conteido de la carpeta temporal
                    //				$odt->borrar();

                    $estadoTrans = $masiva->confirmarMasiva();
                    if ($estadoTrans) {
                        $_SESSION["masiva"] = $masiva;
                        echo "<BR><span class='info'><a  class='vinculos' href=javascript:abrirArchivoaux('$rutaTMP$archivoF')>Guardar Archivo</a>";
                        echo "</span>";
                        echo "<span class='info'>";
                        echo "<BR><a class='vinculos' href=javascript:abrirArchivoaux('$arcPDF')> Abrir Listado</a>";
                        echo "</span>";
                        //Contabilizamos tiempo final
                        $time_end = microtime_float();
                        $time = $time_end - $time_start;
                        echo "<span class='info'>";
                        echo "<br><b>Se demor&oacute;: $time segundos la Operaci&oacute;n total.</b>";
                        echo "</span>";

                        echo "<input type=hidden name=masiva2 value=$masiva>";
                    } else {
                        echo "<BR><span class='alarmas'>Se gener&oacute; un problema al alctualizar la base de datos, intente mas tarde($estadoTrans)";
                        echo "</span>";
                    }
                } else if ($extension == 'docx' || $extension == 'DOCX') {

                    //Se incluye la clase que maneja la combinación masiva
                    include ( "$ruta_raiz/radsalida/masiva/ooxml_masiva.class.php" );
                    define('WORKDIR', '../../bodega/tmp/workDir/');
                    define('CACHE', WORKDIR . 'cacheODT/');

                    //Se abre archivo de insumo para lectura de los datos
                    $fp = fopen("$ruta_raiz/bodega/masiva/$archInsumo", 'r');
                    if ($fp) {
                        $contenidoCSV = file("$ruta_raiz/bodega/masiva/$archInsumo");
                        fclose($fp);
                    } else {
                        exit("No hay acceso para crear el archivo $archInsumo");
                    }

                    $accion = false;
                    $docx = new Ooxml();
                    //Se establede el modo Debug, poner en true para pruebas y muestra mensajes de lo que va ocurriendo
                    $docx->setDebugMode(false);

                    //Se carga el archivo odt Original 
                    $docx->cargarOdt("$ruta_raiz/bodega/masiva/$arcPlantilla", $arcPlantilla);
                    $docx->setWorkDir(WORKDIR);
                    $accion = $docx->abrirOdt();

                    if (!$accion) {
                        die("<CENTER><table class=borde_tab><tr><td class=titulosError>Problemas en el servidor abriendo archivo ODT para combinaci&oacute;n.</td></tr></table>");
                    }

                    $docx->cargarContenido();

                    //Se recorre el archivo de insumo
                    foreach ($contenidoCSV as $line_num => $line) {

                        if ($line_num == 4) { //Esta línea contiene las variables a reemplazar
                            $cadaVariable = explode(';', $line);
                        } else if ($line_num > 4) { //Desde la línea 5 hasta el final del archivo de insumo están los datos de reemplazo
                            $cadaValor = explode(";", $line);


                            // echo '<pre>';
                            // print_r($cadaValor);
                            // echo '</pre>';
                            // die();

                            $cadaValor[1] = caracteresEspeciales($cadaValor[1]);
                            $cadaValor[2] = caracteresEspeciales($cadaValor[2]);
                            $cadaValor[7] = caracteresEspeciales($cadaValor[7]);
                            $cadaValor[8] = caracteresEspeciales($cadaValor[8]);
                            $cadaValor[9] = caracteresEspeciales($cadaValor[9]);

                            $docx->setVariable($cadaVariable, $cadaValor);
                        }

                        echo "";

                        if (connection_status() != 0) {
                            $objError = new CombinaError(NO_DEFINIDO);
                            echo ($objError->getMessage());
                            die;
                        }
                    }

                    $tipoUnitario = '0';
                    //Se guardan los cambios del archivo temporal para su descarga
                    $archivoF = $docx->salvarCambios(null, $archivo, $tipoUnitario);
//                    error_log('*** '.$archivo.' *** '.$tipoUnitario.' *** '.$archivoF);
                    $intBodega = strpos($archivoF, "/bodega");
                    if ($intBodega === false) {

                        $rutaTMP = $ruta_raiz . '/bodega';
                    } else {

                        $rutaTMP = $ruta_raiz;
                    }

                    //Se limpia el conteido de la carpeta temporal
                    $estadoTrans = $masiva->confirmarMasiva();

                    if ($estadoTrans) {
                        $_SESSION["masiva"] = $masiva;
                        echo "<BR><span class='info'><a  class='vinculos' href=javascript:abrirArchivoaux('$rutaTMP$archivoF')>Guardar Archivo</a>";
                        echo "</span>";
                        echo "<span class='info'>";
                        echo "<BR><a class='vinculos' href=javascript:abrirArchivoaux('$arcPDF')> Abrir Listado</a>";
                        echo "</span>";

                        //Contabilizamos tiempo final
                        $time_end = microtime_float();
                        $time = $time_end - $time_start;
                        echo "<span class='info'>";
                        echo "<br><b>Se demor&oacute;: $time segundos la Operaci&oacute;n total.</b>";
                        echo "</span>";

                        echo "<input type=hidden name=masiva2 value=$masiva>";
                    } else {
                        echo "<BR><span class='alarmas'>Se gener&oacute; un problema al alctualizar la base de datos, intente mas tarde($estadoTrans)";
                        echo "</span>";
                    }
                } else {
                    include ("http://$servProcDocs/docgen/servlet/WorkDistributor?accion=2&ambiente=$ambiente&archinsumo=$archInsumo&definitivo=no");

                    //$estadoTransaccion=-1;
                    if (!file_exists("$ruta_raiz/bodega/masiva/$archInsumo.ok")) {
                        $masiva->deshacerMasiva();
                        $objError = new CombinaError(NO_DEFINIDO);
                        echo ($objError->getMessage());
                        die;
                    } else {
                        $estadoTrans = $masiva->confirmarMasiva();
                        if ($estadoTrans) {
                            $_SESSION["masiva"] = $masiva;
                            echo "<BR><span class='info'><a  class='vinculos' href=javascript:abrirArchivoaux('$ruta_raiz/$archivo?')>Guardar Archivo</a>";
                            echo "</span>";
                            echo "<span class='info'>";
                            echo "<BR><a class='vinculos' href=javascript:abrirArchivoaux('$arcPDF')> Abrir Listado</a>";
                            echo "</span>";
                            //Contabilizamos tiempo final
                            $time_end = microtime_float();
                            $time = $time_end - $time_start;
                            echo "<span class='info'>";
                            echo "<br><b>Se demor&oacute;: $time segundos la Operaci&oacute;n total.</b>";
                            echo "</span>";
                            echo "<input type=hidden name=masiva2 value=$masiva>";
                        } else {
                            echo "<BR><span class='alarmas'>Se gener&oacute; un problema al alctualizar la base de datos, intente mas tarde($estadoTrans)";
                            echo "</span>";
                        }
                    }
                }
            }
            ?>
            </form>
        </center>
    </body>
</html>
