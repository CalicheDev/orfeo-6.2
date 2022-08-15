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
/* ---------------------------------------------------------+
  |                     INCLUDES                             |
  +--------------------------------------------------------- */

/* ---------------------------------------------------------+
  |                    DEFINICIONES                          |
  +--------------------------------------------------------- */
session_start();
error_reporting(7);
$url_raiz = $_SESSION['url_raiz'];
$dir_raiz = $_SESSION['dir_raiz'];
$ESTILOS_PATH2 = $_SESSION['ESTILOS_PATH2'];
$imagenes = $_SESSION["imagenes"];
$fonodoacumulado = $_SESSION["fonodoacumulado"];
/* ---------------------------------------------------------+
  |                       MAIN                               |
  +--------------------------------------------------------- */

//error_log('entro al menu/primero.php ');
echo 'menu-primero';
?>
<!--hola entro a esta opción-->
<table border="0" cellpadding="0" cellspacing="0" width="160" >
    <tr class="eMenu">
        <th colspan="2"><img name="menu_r1_c1" src="<?= $imagenes ?>/menu_r1_c1.gif" height="25" border="0" alt="ENCABEZADO DE MENU"></th>
    </tr>
    <tr>
        <td valign="top">
            <table width="150"  border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top">
                        <table width="150"  border="0" cellpadding="0" cellspacing="3" class=eMenu>
                            <?php
                            if ($_SESSION["usua_admin_sistema"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                    <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <a  id="adminPerm" href="Administracion/formAdministracion.php?<?= $phpsession ?>&<? echo "fechah=$fechah&usr=".md5($dep)."&primera=1&ent=1"; ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de Administraci&oacute;n del aplicativo" alt="M&oacute;dulo de Administraci&oacute;n del aplicativo" title="M&oacute;dulo de Administraci&oacute;n del aplicativo">Administraci&oacute;n</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ($_SESSION["permReasignaMasiva"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <td width="125">
                                        <span class="Estilo12"><a href="radicacion/reasignacion_masiva/reasignacionMasiva.php?<?= $phpsession ?>&fechah=<?= $fechah ?>&primera=1&ent=2" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo reasignaci&oacute;n masiva de radicados" alt="M&oacute;dulo reasignaci&oacute;n masiva de radicados" title="M&oacute;dulo reasignaci&oacute;n masiva de radicados">Reasignaci&oacute;n Masiva</a></span>
                                    </td>
                                </tr>
                                <?php
                            }           
                            if ($_SESSION["usua_perm_adminflujos"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <a id="flujoPerm" href="Administracion/flujos/texto_version2/mnuFlujosBasico.php?<?= $phpsession ?>&" class="menu_princ menu1" target='mainFrame' aria-label="M&oacute;dulo edici&oacute;n de flujo de procesos" alt="M&oacute;dulo edici&oacute;n de flujo de procesos" title="M&oacute;dulo edici&oacute;n de flujo de procesos">Editor Flujos</a>
                                    </td>
                                </tr>				
                                <?php
                            }
                            if ($_SESSION["usua_perm_envios"] >= 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <a id="envioPerm" href="radicacion/formRadEnvios.php?<?= $phpsession ?>&<? echo "fechah=$fechah&usr=".md5($dep)."&primera=1&ent=1"; ?>" target='mainFrame' class="menu_princ menu1" alt="M&oacute;dulo administraci&oacute;n de env&iacute;os" aria-label="M&oacute;dulo administraci&oacute;n de env&iacute;os" title="M&oacute;dulo administraci&oacute;n de env&iacute;os">Env&iacute;os</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ($_SESSION["usua_perm_modifica"] >= 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                           <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="modificaPerm" href="radicacion/edtradicado.php?<?= $phpsession ?>&fechah=<?= $fechah ?>&primera=1&ent=2" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo Modificaci&oacute;n datos de radicado" alt="M&oacute;dulo Modificaci&oacute;n datos de radicado" title="M&oacute;dulo Modificaci&oacute;n datos de radicado">Modificaci&oacute;n</a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ($_SESSION["usua_perm_firma"] == 1 || $_SESSION["usua_perm_firma"] == 3) {
                                ?>
                                <tr valign="middle">
                                                <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="firmaPerm" href="./certSHT/menu_cert.php?<?= $phpsession ?>&krd=<?= $krd ?>&<?php echo "fechaf=$fechah&carpeta=8&nomcarpeta=Documentos Para Firma Digital&orderTipo=desc&orderNo=3"; ?>" target='mainFrame' class="menu_princ menu1">Certificaciones RH</a></span>
                                    </td>
                                </tr>


                <!--<tr valign="middle">
         By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                        <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                        <!--<td width="125">
                                <span class="Estilo12"><a href="firma/cuerpoPendientesFirma.php?<?= $phpsession ?>&krd=<?= $krd ?>&<?php echo "fechaf=$fechah&carpeta=8&nomcarpeta=Documentos Para Firma Digital&orderTipo=desc&orderNo=3"; ?>" target='mainFrame' class="menu_princ" alt="M&oacute;dulo de firma digital" title="M&oacute;dulo de firma digital" aria-label="M&oacute;dulo de firma digital">Firma Digital</a></span>
                        </td>-->
                                <!--</tr>-->
                                <?php
                            }
                            if ($_SESSION["usua_perm_intergapps"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="appintPerm" href="aplintegra/cuerpoApLIntegradas.php?<?= $phpsession ?>&<?php echo "fechaf=$fechah&carpeta=8&nomcarpeta=Aplicaciones integradas&orderTipo=desc&orderNo=3"; ?>" target='mainFrame' class="menu_princ menu1" aria-label="Menu de aplicaciones integradas">Aplicaciones integradas</a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            /*if ($_SESSION["usua_perm_impresion"] >= 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="impresionPerm"href="envios/cuerpoMarcaEnviar.php?<?= $phpsession ?>&<?php echo "fechaf=$fechah&usua_perm_impresion=$$usua_perm_impresion&carpeta=8&nomcarpeta=Documentos Para Impresion&orderTipo=desc&orderNo=3"; ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo para asignaci&oacute;n de estado de impresi&oacute;n radicados" alt="M&oacute;dulo para asignaci&oacute;n de estado de impresi&oacute;n radicados" title="M&oacute;dulo para asignaci&oacute;n de estado de impresi&oacute;n radicados">Impresi&oacute;n</a></span>
                                    </td>
                                </tr>
                                <?php
                            }*/
                            
                            if ($_SESSION["usua_perm_anu"] == 3 or $_SESSION["usua_perm_anu"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="anulacionesPerm" href="anulacion/cuerpo_anulacion.php?<?= $phpsession ?>&tpAnulacion=1&<? echo "fechah=$fechah"; ?>&dependencia=<?= $_SESSION['dependencia'] ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de solicitud de anulaci&oacute;n de radicados" alt="M&oacute;dulo de solicitud de anulaci&oacute;n de radicados" title="M&oacute;dulo de solicitud de anulaci&oacute;n de radicados">Solicitud anulaci&oacute;n de radicado</a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ($_SESSION["usua_perm_trd"] >= 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12">
                                            <a id="trdPerm" href="trd/menu_trd.php?<?= $phpsession ?>&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="Administraci&oacute;n de tablas de retenci&oacute;n documental" alt="Administraci&oacute;n de tablas de retenci&oacute;n documental" title="Administraci&oacute;n de tablas de retenci&oacute;n documental">Tablas Retenci&oacute;n Documental</a></span>
                                    </td>
                                </tr>
                                <?php
                            }

                            //if($_SESSION["usua_perm_expedientes"] == 1){
                                ?>
                                <!-- <tr valign="middle">
                                    <td width="125">
                                        <span class="Estilo12">
                                            <a id="trdPerm" href="expediente/listado_expedientes.php?<?= $phpsession ?>&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="Administraci&oacute;n de expedientes por dependencia" alt="Administraci&oacute;n de expedientes por dependencia" title="Administraci&oacute;n de expedientes por dependencia">Gestión de Expedientes</a></span>
                                    </td>
                                </tr> -->
                                <?php
                            //}

                            ?>

                            
                            <tr valign="middle">
                                <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                               <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                <td width="125">
                                    <span class="Estilo12">
                                        <a id="consultasPerm" href="busqueda/busquedaPiloto.php?<?= $phpsession ?>&etapa=1&s_Listado=VerListado&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de consulta de radicados" alt="M&oacute;dulo de consulta de radicados" title="M&oacute;dulo de consulta de radicados">* Consultas Generales</a></span>
                                </td>
                            </tr>

                            <?php
                            if($_SESSION["flagConsultaOrfeoOld"] == true){
                                ?>
                                <tr valign="middle">
                                    <td width="125">
                                        <span class="Estilo12">
                                        <a id="consultasPerm" href="busqueda/visorConsulta.php?<?= $phpsession ?>&etapa=1&s_Listado=VerListado&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de consulta de radicados" alt="M&oacute;dulo de consulta de radicados" title="M&oacute;dulo de consulta de documentos">* Consultas Documentos Anteriores</a></span>
                                        <!-- <a id="consultasPerm" href="busqueda/busquedaDocs.php?<?= $phpsession ?>&etapa=1&s_Listado=VerListado&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de consulta de radicados" alt="M&oacute;dulo de consulta de radicados" title="M&oacute;dulo de consulta de documentos">* Consultas Documentos Anteriores</a></span> -->
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>

                            <!-- 
                                Autor: Daniel tautiva 
                                Fecga: 14-12-2020
                                Infor: Se agrega permiso para inventario documental :: flagInventarioDocumental -> config.php
                            -->
                            <?php  if($flagInventarioDocumental){ 
                                        if ($_SESSION["per_consulta_inv_documental"] != 0 || $_SESSION["per_carga_inv_documental"] != 0) { ?>
                            <tr valign="middle">
                                <td width="125">
                                    <span class="Estilo12">  
                                        <a id="consultasPerm" href="busqueda/busquedaInv.php?<?= $phpsession ?>&etapa=1&s_Listado=VerListado&fechah=<?= $fechah ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de inventario documental" alt="M&oacute;dulo de inventario documental" title="M&oacute;dulo de inventario documental"> 
                                            Inventario Documental
                                        </a>
                                    </span>
                                </td>
                            </tr>
                            <?php       
                                        }   
                                    }
                            ?>

<!--                        <tr valign="middle">
                               <td width="125">
                                    <span class="Estilo12">
                                        <a id="consultasPerm" href="fondoAcumulado/menuConfiguracion.php" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de consulta de radicados" alt="M&oacute;dulo de consulta de radicados" title="M&oacute;dulo de consulta de radicados">Configuración</a></span>
                                </td>
                            </tr>-->
                            <?php
                            if($fonodoacumulado == true){
                                ?>
                            <tr valign="middle">
                               <td width="125">
                                    <span class="Estilo12">
                                        <a id="consultasPerm" href="fondoAcumulado/formularioFondoAcumulado.php" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de consulta de fondo acumulado" alt="M&oacute;dulo de consulta de fondo acumulado" title="M&oacute;dulo de consulta de fondo acumulado">Fondo acumulado</a></span>
                                </td>
                            </tr>
                            <?php
                            }

                            /*** Transferencias documentales  ***/
                            if ($_SESSION["per_transferencia_documental"] == 1) {
                            ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                    <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <a  id="transDocuPerm" href="transDocumentales/transfeDocumentales.php?<?= $phpsession ?>&<? echo "fechah=$fechah&usr=".md5($dep)."&primera=1&ent=1"; ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de Transferencias documentales del aplicativo" alt="M&oacute;dulo de Transferencias documentales del aplicativo" title="M&oacute;dulo de Transferencias documentales del aplicativo">Transferencias documentales</a>
                                    </td>
                                </tr>
                            <?php
                            }
                            /**
                             *  $usua_admin_archivo Viene del campo con el mismo nombre en usuario y Establece permiso para ver informaci&oacute;n de
                             *  documentos que tienen que bicarse fisicamente en Archivo
                             *  (Por. Jh 20031101)
                             */
                            if ($_SESSION["usua_admin_archivo"] >= 1) {
                                $isql = "select count(*) as CONTADOR from SGD_EXP_EXPEDIENTE where sgd_exp_estado=0 ";
                                $rs = $db->conn->Execute($isql);
                                $num_exp = $rs->fields["CONTADOR"];
                                ?>
                                <tr>
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td><span class="Estilo12"><a id="archivoPerm" href='archivo/archivo.php?<?= $phpsession ?>&krd=<?= $krd ?>&fechah=<?= $fechah ?>&usr=<?= md5($dep) ?>&primera=1&ent=1' target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de administraci&oacute;n archivo f&iacute;sico" alt="M&oacute;dulo de administraci&oacute;n archivo f&iacute;sico" title="M&oacute;dulo de administraci&oacute;n archivo f&iacute;sico">Archivo (<?= $num_exp ?>)</a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            if ($_SESSION["usua_perm_prestamo"] == 1) {
                                ?>
                                <tr valign="middle">
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <td width="125">
                                        <span class="Estilo12"><a id="prestamoPerm" href="prestamo/menu_prestamo.php?<?= $phpsession ?>&etapa=1&&s_Listado=VerListado&krd=<?= $krd ?>&<? echo "fechah=$fechah"; ?>" target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de administraci&oacute;n de pr&eacute;stamos de documentos" alt="M&oacute;dulo de administraci&oacute;n de pr&eacute;stamos de documentos" title="M&oacute;dulo de administraci&oacute;n de pr&eacute;stamos de documentos">Pr&eacute;stamo</a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            /**
                             *  $usua_perm_dev  Permiso de ver documentos de devolucion de documentos enviados.
                             *  (Por. Jh)
                             */
                            // if ($_SESSION["usua_perm_dev"] == 1) {
                                ?>
                                <!-- <tr> -->
                                    <!--By Skina -Ing Camilo Pintor Nov-12-2013 Se comenta para dar accesibilidad en el movimiento entre aplicaciones con el tab inci -->
                                                   <!--<td width="25"><img src="<?= $imagenes ?>/menu.gif" height="18"></td>-->
                                    <!-- <TD>
                                        <span class="Estilo12">
                                            <a id="devCorreoPerm" href='devolucion/cuerpoDevCorreo.php?<?= $phpsession ?>&krd=<?= $krd ?>&<?php echo "fechaf=$fechah&carpeta=8&devolucion=2&estado_sal=4&nomcarpeta=Documentos Para Impresion&orno=1&adodb_next_page=1"; ?>' target='mainFrame' class="menu_princ menu1" aria-label="M&oacute;dulo de administraci&oacute;n de devoluciones de correo" title="M&oacute;dulo de administraci&oacute;n de devoluciones de correo" alt="M&oacute;dulo de administraci&oacute;n de devoluciones de correo">Dev Correo</span></a>
                                    </td> -->
                                <!-- </tr> -->
                                <?php
                            // }
                            ?>

                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
