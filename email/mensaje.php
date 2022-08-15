<?php
/**
 * En este frame se van cargado cada una de las funcionalidades del sistema
 *
 * Descripcion Larga
 *
 * @category
 * @package      SGD Orfeo
 * @subpackage   Main
 * @author       Community
 * @author       Skina Technologies SAS (http://www.skinatech.com)
 * @license      GNU/GPL <http://www.gnu.org/licenses/gpl-2.0.html>
 * @link         http://www.orfeolibre.org
 * @version      SVN: $Id$
 * @since
 */

// Ultima Modificacion Kasandra 2012-10    Agregamos templates documentacion
// error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);

session_start();
if (!isset($_SESSION['krd']))
    include "../rec_session.php";

foreach ($_GET as $key => $valor)
    ${$key} = $valor;
foreach ($_POST as $key => $valor)
    ${$key} = $valor;

if (!$ruta_raiz)
    $ruta_raiz = "..";

set_include_path(".:/usr/share/php:/usr/share/pear");

/* * ******************************************************
 *          Encabezados de librerias estandares          *
 * ****************************************************** */

include('../config.php');
include "email.inc.php";

/* * ******************************************************
 *                   Programa Principal                  *
 * ****************************************************** */


function acentos($cadena) {
   $search = explode(",","á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,Ã¡,Ã©,Ã­,Ã³,Ãº,Ã±,ÃÃ¡,ÃÃ©,ÃÃ­,ÃÃ³,ÃÃº,ÃÃ±,Ã“,Ã ,Ã‰,Ã ,Ãš,â€œ,â€ ,Â¿,ü,O?,o?,OÌ,oí");
   $replace = explode(",","á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,Ó,Á,É,Í,Ú,\",\",¿,&uuml;,Ó,ó,Ó,ó");
   $cadena= str_replace($search, $replace, $cadena);

   return $cadena;
}

/** Funcion que permite validar los acentos por medio del regex
 * @param $str [string][Texto a validar]
 */
function properText($str){
    $str = mb_convert_encoding($str, "HTML-ENTITIES", "UTF-8");
    $str = preg_replace('[a-zA-Z áéíóúÁÉÍÓÚñÑ.]',htmlentities('${1}'),$str);
    return($str); 
}

$usuaEmail = $_SESSION['usuaEmail'];
$usuario_mail = $_SESSION['usua_email'];
$dominioEmail = $_SESSION['dominioEmail'];

if (!$_SESSION['passwdEmail']) {
    $passwdEmail = $passwd_mail;
    $_SESSION['passwdEmail'] = $passwd_mail;
} else {
    $passwdEmail = $_SESSION['passwdEmail'];
}
// echo $usuaEmail,"-",$usuario_mail,"-",$dominioEmail,"-",$passwdEmail,"+" ;

if (!$passwdEmail) {
    $splitEmail = explode("@", $usuario_mail);
    $usuaEmail = $splitEmail[0];
    $dominioEmail = $splitEmail[1];
    $_SESSION['usuaEmail'] = $usuaEmail;
    $_SESSION['dominioEmail'] = $dominioEmail;
    $_SESSION['passwdEmail'] = $passwd_mail;
}
if (!$dominioEmail) {
    $splitEmail = explode("@", $usuario_mail);
    $usuaEmail = $splitEmail[0];
    $dominioEmail = $splitEmail[1];
}
include_once "connectIMAP.php";

if ($_GET['inboxEmail']) {
    $buzon_mail = $_GET['inboxEmail'];
} else {
    $buzon_mail = 'INBOX';
}

//////// valores requeridos para el funcionamiento ////////
$msgno = isset($_GET['msgno']) && $_GET['msgno'] != '' ? $_GET['msgno'] : null;
$emailInfo = $msg->getEmailInfo($msgno, $buzon_mail);
$headerInfo = $emailInfo['headerInfo'];
$from = $headerInfo->from[0];
$to = $headerInfo->to[0];
$body = $msg->getBody($msgno, $inbox_email);
$inbox_email = $_GET['inboxEmail'];

///////////////////////////////////////////////////////////
///// valores utilizados como adaptador a lo existente para radicar ////////////
$mid = $msgno;
$eMailMid = $msgno;

/////////////////////////////////////////////////////////
?>
<html>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link href="<?= $ruta_raiz . $ESTILOS_PATH2 ?>bootstrap.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="<?= $ruta_raiz . $_SESSION['ESTILOS_PATH_ORFEO'] ?>">
        <STYLE TYPE="text/css">
            #flotante { position: absolute; top:100; left: 550px; visibility: visible;}
        </STYLE>
    </HEAD>
    <BODY>
        
        <?php
            //var_dump($emailInfo);
        
        //if (isset($_GET['mid']) && isset($_GET['pid'])) {
        if ($msgno) {
            
            //$body = $msg->getBody($_GET['mid'], $_GET['pid']);
            //lectura cabeceras----
            //$msg->getHeaders($_GET['mid']);
            
            // echo '<pre>';
            // print_r($msg->msg[$mid]['at']);

            $eMailRemitente = $_SESSION['eMailRemitente'];
            $eMailNombreRemitente = $_SESSION['eMailNombreRemitente'];
            /*if ($body['ftype'] == "text/html")
                $nl = "</br>";
            else
                $nl = "<br>";
                */
            ?>
            <br>
        <center>
            <div id="titulo" style="width: 95%;" align="center">Correo: <?= imap_utf8($headerInfo->subject) ?></div>
            <table  class="borde_tab" border="1" width="95%" cellspacing="5">
                <tr class="titulos5">
                    <td><b><font size=3><a href='../radicacion/NEW.php?<?= session_name() ?>=<?= session_id() ?>&ent=2&eMailMid=<?= $mid ?>&eMailAmp=<?= $eMailAmp ?>&eMailPid=<?= $eMailPid ?>&fileeMailAtach=<?= $fname ?>&tipoMedio=eMail' target='formulario'>Radicar Este Correo</a></font></b>
                    </td>
                    <td><b><font size=3><a href='deleteMail.php?<?= session_name() ?>=<?= session_id() ?>&ent=2&eMailMid=<?= $mid ?>&eMailAmp=<?= $eMailAmp ?>&eMailPid=<?= $eMailPid ?>&fileeMailAtach=<?= $fname ?>&tipoMedio=eMail' >Eliminar</a></font></b>
                    </td>
                </TR>
            </table>
        </center>
        <br>
        <div class="cuerpoMail">
            <div class="headMail">
                <table border="0" style="width:100%" class="borde_tabReducido">
                    <tr>
                        
                        <td><u><b>De:</b></u></td>
                        <td><?= imap_utf8($from->personal) ?> &lt;<?= imap_utf8($headerInfo->fromaddress) ?>&gt;</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><u><b>Para:</b></u></td>
                        <td><?= imap_utf8($to->mailbox) ?> &lt;<?= imap_utf8($headerInfo->toaddress) ?>&gt;</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><u><b>Asunto:</b></u> </td>
                        <td><?= imap_utf8($headerInfo->subject) ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><u><b>Enviado Desde Serv:</b></u></td>
                        <td><?= imap_utf8($from->host) ?></td>
                        <td><u><b>Fecha:</b></u></td>
                        <td><?= imap_utf8($headerInfo->date) ?></td>
                    </tr>
                </table>
            </div>
            <div class="mensajeMail">
                <div class="cuerpomensaje">
                    <?php if(isset($body['html_message']) && $body['html_message'] != ''): ?>
                        <?= $body['html_message'] ?>
                    <?php elseif(isset($body['plain_message']) && $body['plain_message'] != ''): ?>
                        <?= $body['plain_message'] ?>
                    <?php endif; ?>
                    <?php if(isset($body['attachments']) && $body['attachments'] != ''): ?>
                        <?php $count = 1; ?>
                        <?php foreach($body['attachments'] as $key => $value): ?>
                            <li><a href='attachement.php?msgno=<?= $msgno ?>&numadj=<?= $count++; ?>&folder=<?= $inbox_email ?>' target='_blank'><?= imap_utf8($key) ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>  
                      
                    <?php// $emailBody; ?>     
                </div>
            </div>
        </div>
    </div>

    <table border="1" class="borde_tab" width="100%">
        <tr class="titulos3">
        <br>
        <?php
        //dibujado de los datos del correo con el metodo anterior
        //echo "<br>";
        // echo $headRadicado;
        //echo "<br>";
        // echo str_replace("\n", "<br>", $body['message']);
        //$content = "" . $head . $body['message'];
    }else {
        print("No hay Correo disponible");
    }
    $fn = $body['fname'];
//--Variable con la Cabecera en formato html----------------------------------//
$nl = "<br>";
?>
    </tr>
</table>
</BODY>
</html>
