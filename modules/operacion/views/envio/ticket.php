<?php

use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysSetting;
use app\models\producto\Producto;
use app\models\promocion\PromocionComplemento;

$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";


$qrCode = (new QrCode($model->folio))
    ->setSize(100)
    ->setMargin(2)
    ->setErrorCorrectionLevel('medium');
$code = [];

$code['qrBase64'] =  $qrCode->writeDataUri();


$array_cliente_id   =  [];
$suma_asegurada     = 0;
$total_pieza        = 0;
$pesoPAQUETE        = 0;
?>

<body>
    <table style="font-size: 12px">
        <tr>
            <td colspan="2" align="center">
                <?php if (Yii::$app->user->identity->id == 28) : ?>
                    <?= Html::img('@web/img/logo-servientregas.png', ["height" => "150px"]) ?>
                <?php else : ?>
                    <?php if (Yii::$app->user->identity->id == 26) : ?>
                        <?= Html::img('@web/img/logo-movired.png', ["height" => "150px"]) ?>
                    <?php else : ?>
                        <?= Html::img('@web/img/sea-black.png', ["height" => "150px"]) ?>
                    <?php endif ?>
                <?php endif ?>
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 16px; padding:  10px;">
                <p><strong style="font-weight: bold;">REMITENTE: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;">
                <p style="color: #000; font-size: 16px; font-weight: bold;"><?= isset($model->clienteEmisor->nombreCompleto) ?  $model->clienteEmisor->nombreCompleto : ''  ?></p>
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  10px; font-size: 14px;">
                <p><strong style="font-weight: bold;">SUCURSAL E.: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 14px;">
                <p style="color: #000; font-size: 16px;font-weight: bold;"><?= $model->sucursalEmisor->nombre ?> (<?= isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : ''  ?>)</p>
            </td>
        </tr>
        <tr>
            <td class="text-center" colspan="2" style="background-color:<?php echo $colorGris; ?>;  color: black; padding:  10px; font-size: 14px;">
                <p><strong style="font-weight: bold;"><?= $model->is_zona_riesgo == 1 ? "Lugar en zona roja" : "" ?></strong></p>
            </td>

        </tr>



        <?php foreach ($model->envioDetalles as $key => $item) : ?>
            <?php if ($item->status != EnvioDetalle::STATUS_CANCELADO) : ?>

                <?php if (isset($is_comprimido) && $is_comprimido) : ?>
                    <?php if ($item->status !=  EnvioDetalle::STATUS_CANCELADO) : ?>
                        <tr style="padding: 0">
                            <td colspan="2" style="padding: 0">
                                <hr style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                            </td>
                        </tr>

                        <tr>
                            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                                <p><strong style="font-weight: bold;">SUCURSAL(<?= $key + 1 ?>): </strong></p>
                            </td>
                            <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                                <p>
                                    <?= isset($item->sucursalReceptor->nombre) ?  $item->sucursalReceptor->nombre : ''  ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                                <p><strong style="font-weight: bold;">ENTREGA(<?= $key + 1 ?>): </strong></p>
                            </td>
                            <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                                <p>
                                    <?= isset($item->is_reenvio) && $item->is_reenvio == EnvioDetalle::REENVIO_ON ?  'SI' : 'NO'  ?>
                                    /<?php if (isset($item->direccion)) : ?>
                                    <?= isset($item->direccion->estado->singular) ? $item->direccion->estado->singular : 'N/A' ?>,
                                    <?= isset($item->direccion->municipio->singular)  ? $item->direccion->municipio->singular : 'N/A' ?>,
                                    <?= isset($item->direccion->esysDireccionCodigoPostal->colonia)  ? $item->direccion->esysDireccionCodigoPostal->colonia : 'N/A' ?>

                                <?php endif ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  5px; font-size: 12px;">
                                <p><strong style="font-weight: bold;">DESTINARIO(<?= $key + 1 ?>) : </strong></p>
                            </td>
                            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 12px;">
                                <p><?= isset($item->clienteReceptor->nombreCompleto) ?  $item->clienteReceptor->nombreCompleto : ''  ?></p>
                            </td>
                        </tr>
                        <?php $suma_asegurada = $suma_asegurada + ($item->status !=  EnvioDetalle::STATUS_CANCELADO ? $item->valor_declarado : 0) ?>
                        <?php $pesoPAQUETE = $pesoPAQUETE + ($item->status !=  EnvioDetalle::STATUS_CANCELADO ? $item->peso : 0) ?>
                        <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO) : ?>
                            <tr>
                                <td align='center' colspan="2" style="background-color:#d32f2f; color: white; font-size: 16px; padding: 5px">
                                    <strong style="font-size: 16px;font-weight: bold;">==== PAQUETE CANCELADO ===</strong>
                                </td>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold;font-size: 16px;">PAQ # <?= $item->tracked ?> <small style="font-size: 10px;"> <?= $item->cantidad ?> PZAS</small></strong></td>
                        </tr>
                        <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO) : ?>
                            <tr>
                                <td align='center' colspan="2" style="background-color:#d32f2f; color: white;">
                                    <strong>=============================</strong>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endif ?>
                <?php else : ?>
                    <tr style="padding: 0">
                        <td colspan="2" style="padding: 0">
                            <hr style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 14px; padding:  5px;">
                            <p><strong style="font-weight: bold;">SUCURSAL(<?= $key + 1 ?>): </strong></p>
                        </td>
                        <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 14px;">
                            <p style="font-weight:bold;color: #000; font-size: 16px;">
                                <?= isset($item->sucursalReceptor->nombre) ?  $item->sucursalReceptor->nombre : ''  ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 14px; padding:  5px;">
                            <p><strong style="font-weight: bold;">ENTREGA(<?= $key + 1 ?>): </strong></p>
                        </td>
                        <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 14px;">
                            <p style="font-weight: bold;color: #000; font-size: 16px;   ">
                                <?= isset($item->is_reenvio) && $item->is_reenvio == EnvioDetalle::REENVIO_ON ?  'SI' : 'NO'  ?>
                                /<?php if (isset($item->direccion)) : ?>
                                <?= isset($item->direccion->estado->singular) ? $item->direccion->estado->singular : $item->clienteReceptor->estadoOutMX ?>,
                                <?= isset($item->direccion->municipio->singular)  ? $item->direccion->municipio->singular : $item->clienteReceptor->municipioOutMX  ?>,
                                <?= isset($item->direccion->esysDireccionCodigoPostal->colonia)  ? $item->direccion->esysDireccionCodigoPostal->colonia : $item->clienteReceptor->coloniaOutMX  ?>,
                                <?= isset($item->direccion->esysDireccionCodigoPostal->colonia)  ? 'MÉXICO' : $item->pais->nombre  ?>

                            <?php endif ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:<?php echo $color; ?>;  color: white; padding:  5px; font-size: 14px;">
                            <p><strong style="font-weight: bold;">DESTINARIO(<?= $key + 1 ?>) : </strong></p>
                        </td>
                        <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 14px;">
                            <p style="font-weight:bold; color: #000; font-size: 16px;"><?= isset($item->clienteReceptor->nombreCompleto) ?  $item->clienteReceptor->nombreCompleto : ''  ?></p>
                        </td>
                    </tr>

                    <?php $suma_asegurada = $suma_asegurada + ($item->status !=  EnvioDetalle::STATUS_CANCELADO ? $item->valor_declarado : 0) ?>
                    <?php $pesoPAQUETE = $pesoPAQUETE + ($item->status !=  EnvioDetalle::STATUS_CANCELADO ? $item->peso : 0) ?>

                    <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO) : ?>
                        <tr>
                            <td align='center' colspan="2" style="background-color:#d32f2f; color: white; font-size: 16px; padding: 5px">
                                <strong style="font-size: 16px;font-weight: bold;">==== PAQUETE CANCELADO ===</strong>
                            </td>
                        </tr>
                    <?php endif ?>
                    <tr>
                        <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold;font-size: 24px;">PAQ # <?= $item->tracked ?> - <small style="font-size: 14px;"> <?= $item->cantidad ?> PZAS</small></strong></td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <table style="width: 100%; border-width: 0px;">
                                <tbody>
                                    <?php if ($item->status != EnvioDetalle::STATUS_CANCELADO) {
                                        $total_pieza = $total_pieza + $item->cantidad;
                                        $productos = $item->detalleProducto ? json_decode($item->detalleProducto->detalle_json, true) : null;
                                    ?>
                                        <?php for ($i = 0; $i < $item->cantidad; $i++) {
                                            $pieza_count = $i + 1; ?>
                                            <tr>
                                                
                                                <td style=" text-align: center; font-size: 12px; color: #000; font-family: Georgia, serif; line-height: 15px; font-weight:bold;">PAQ #<?= $item->tracked  ?>/<?= $pieza_count ?> </td>
                                                <td style=" text-align: center; font-size: 12px; color: #000; font-family: Georgia, serif; line-height: 15px; font-weight: bold;"><?= $productos ? $productos[$i]['nombre'] : $item->producto->nombre ?></td>
                                                <td style="text-align: center;font-size: 18px;color: #000;font-family: Georgia, serif;line-height: 15px;font-weight: bold;">
                                                    <?= isset($productos[$i]) && $productos[$i]['tipo_producto'] == Producto::TIPO_CAJA_SIN_LIMITE
                                                        ? ""
                                                        : (isset($productos[$i]['peso_max']) ? $productos[$i]['peso_max'] . " LB" : "")
                                                    ?>
                                            </tr>
                                        <?php } ?>


                                        <tr>
                                            <?php if ($item->observaciones) : ?>
                                                <td style=" text-align: center; font-size: 14px; color: #000;font-family: Georgia, serif; line-height: 15px; font-weight: bold;"><?= $item->observaciones  ?> </td>
                                            <?php endif ?>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO) : ?>
                        <tr>
                            <td align='center' colspan="2" style="background-color:#d32f2f; color: white;">
                                <strong>=============================</strong>
                            </td>
                        </tr>
                    <?php endif ?>

                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>



        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 18px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>
        <tr>
            <td width="50%" style="  border: none; ">
                <table>
                    <tr>
                        <th><strong style="font-size: 14px;color:#000">Cantidad</strong></th>
                        <th><strong style="font-size: 14px;color:#000">Producto</strong></th>
                        <th><strong style="font-size: 14px;color:#000">Costo </strong></th>
                        q
                    </tr>
                    <?php foreach ($model->envioDetalles as $key => $item) : ?>
                        <?php if ($item->status != EnvioDetalle::STATUS_CANCELADO) : ?>
                            <tr>

                                <td>
                                    <p style=" font-size: 14px; font-weight: bold;"><?= $item->cantidad ?></p>
                                </td>
                                <td>
                                    <p style=" font-size: 14px; font-weight: bold;"><?= $item->producto->nombreTipo ?></p>
                                </td>
                                <td>
                                    <p style=" font-size: 14px; font-weight: bold;">$<?= number_format($model->total - $model->seguro_total, 2) ?></p>
                                </td>
                            </tr>
                            <!-- Mostrar información sobre el detalle de envío -->

                        <?php endif; ?>
                    <?php endforeach; ?>


                    <!-- <tr>
                        <td><strong style="font-size: 14px;color:#000">TOTAL [PZ] </strong></td>
                        <td>
                            <p style=" font-size: 14px; font-weight: bold;"><?= print_r($model->pienzasTotal); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td><strong style="font-size: 14px;color:#000">PESO [LBS]</strong></td>
                        <td>
                            <p style=" font-size: 14px; font-weight: bold;"><?= number_format($pesoPAQUETE, 2) ?> lbs</p>
                        </td>
                    </tr>
                    <tr>
                        <td><strong style="font-size: 14px;color:#000">DOCUMENTADO [LBS]</strong></td>
                        <td>
                            <p style=" font-size: 14px; font-weight: bold;"><?= number_format($model->peso_total, 2) ?> lbs</p>
                        </td>
                    </tr>-->
                </table>

            </td>

            <table>
                <!-- <tr>
                        <td><strong style="font-size: 14px;color:#000">COSTO X LB</strong></td>
                        <td>
                            <p style=" font-size: 14px; font-weight: bold;"><?= $model->precio_libra_actual ?> dlls</p>
                        </td>
                    </tr>-->
                <tr>
                    <td><strong style="font-size: 14px;color:#000">ASEGURANZA</strong></td>
                    <td>
                        <p style=" font-size: 14px; font-weight: bold;">$<?= number_format($model->seguro_total, 2) ?></p>
                    </td>
                </tr>
                <tr>
                    <td><strong style="font-size: 14px;color:#000">CANTIDAD ASEGURADA</strong></td>
                    <td>
                        <p style=" font-size: 14px; font-weight: bold;">$<?= number_format($suma_asegurada, 2) ?></p>
                    </td>
                </tr>
            </table>
            </td>-->
        </tr>
        <tr>
            <td colspan="2" align="center">
                <table width="100%">
                    <tr>
                        <td style="width: 40%">
                        </td>
                        <td style="width: 60%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td style=" font-weight: bold; font-size: 14px; color: #000; padding:  5px;"><strong>COSTO TOTAL</strong></td>
                                    <td style=" font-weight: bold; font-size: 14px; color: #000; padding:  5px;"> $<?= number_format($model->total, 2)   ?> </td>
                                </tr>
                                <tr>
                                    <td style=" font-weight: bold; font-size: 14px; color: #000; padding:  5px;"><strong>BALANCE</strong></td>
                                    <td style=" font-weight: bold; font-size: 14px; color: #000; padding:  5px;"> $<?= number_format($model->total - $model->totalPagado, 2) ?> </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <p style="font-size:16px"><strong>TERMINOS Y CONDICIONES</strong></p>
            </td>
        </tr>
        <?php if (Yii::$app->user->identity->sucursal_id == 24) : ?>
            <tr style="font-size: 8px">
                <td colspan="2" align='justify' style="font-family: Georgia, serif; line-height: 15px">
                    <ul>● Todos los artículos deben ser declarados y por el valor real</ul>
                    <ul>● La aseguranza es obligatoria (8%), en caso de pérdida o robo solo se pagará el valor declarado equivalente por pieza </ul>
                    <ul>● Si su paquete no ha sido entregado dentro del plazo dado, debe esperar 30 días antes de llenar el formato de reclamo</ul>
                    <ul>● El embalaje de los paquetes es responsabilidad del cliente </ul>
                </td>
            </tr>

        <?php else : ?>
            <tr>
                <td colspan="2" align='justify' style="font-family: Georgia, serif;  font-size: 8px;">
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">1.-EL COBRO POR ENVIO MINIMO ES POR 30 LIBRAS EQUIVALENTE A $ DOLARES.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">2.-TODOS LOS OBJETOS DEL VALOR TIENEN QUE SER DECLARADOS Y ENTREGADOS EN LA MANO DEL ENCARGADO DELA SUCURSAL.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">3.-TODO PAQUETE ENVIADO DEBE TENER UNA ASEGURANZA OBLIGATORIA.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">4.-SE COBRA EL 7% DE LA ASEGURANZA SOBRE EL VALOR DECLARADO DEL PAQUETE.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">5.-TODOS LOS EQUIPOS ELECTRONICOS Y ELECTRODOMESTICOS PAGAN IMPUESTOS.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">6.-EL COSTO POR REENVIO ES DE $45 A PARTIR DE 100 LIBRAS AUMENTA 45CENTAVOS POR LIBRA</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">7.-ES RESPONSABILIDAD DEL CLIENTE EMPACAR Y PROTEGER SU ENVIO DE UNA FORMA SEGURA.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">8.-NO SOMOS RESPONSABLES POR DANOS O PERDIDA DERIVADO DE UNA MALAPROTECCION DE LA MERCANCIA.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">9.-ES OBLIGACION DEL CLIENTE QUE RECIBE REVISAR SU PAQUETE EN FRENTE DEL ENCARGADO DE LA SURCUSAL SI LLEGARA A ENCONTRAR ALGUN FALTANTE TENDRA QUE REPOTARLO EN ESE INSTANTE UNA VEZ SALIENDO DE LA SURCUSAL NO ACEPTAMOS RECLAMOS</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">10.-NO SE ACEPTAN HIERBAS SECAS, PLANTAS, FRUTAS, SEMILLAS PARA SEMBRAR Y/O VERDURA CRUDAS, LIQUIDOS FLAMABLES O TOXICOS, ARMAS FUEGO O ARMAS BLANCAS (DE NINGÜN TIPO) NI MUNICIONES, JOYAS, MATERIALES FRAGILES (COMO CERÅMICA, VIDRIO OCUALQUIER OTRO) DINERO EN EFECTIVO Y NINGUN PRODUCTO</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">11.-PUEDE HABER VARIACION EN LA FECHA DE ENTREGA DEL PAQUETE, DEBIDO A CIRCUNSTANCIAS FUERA DE NUESTRO CONTROL.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">12.-EN CASO DE PERDIDA DE SU PAQUETE (QUE NO HA SUCEDIDO) USTED DEBERÅ PRESENTAR LA COPIA AMARILLA DE ESTE CONTRATO PARA RECLAMAR UN REEMBOLSO EN CASO DE NO TENERLO, SU BALANCE SERÅ APLICADO COMO CRÉDITO PARA EL SIGUIENTE ENVIO DE PAQUETE.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">13._TODOS LOS COSTOS DE ENVIO DE PAQUETERIA SON SUJETOS A TAXES DE LA CIUDAD A LA QUE CORRESPONDE LA SURCUSAL.</p>
                    <br>
                    <p style="line-height: 15px; font-size: 14px;color: #000;font-family: Georgia, serif;">14.-PARA HACER EL REEMBOLSO CORRECTO DE ALGÜN PAQUETE PERDIDO, SE LE REQUERIRA OUE PRESENTE LOS RECIBOS ORIGINALES DE LOS PRODUCTOS QUE USTED DECLARO ENVIADOS.</p>
                    <br>
                    <br>
                    <br>
                    <br />
                    <br />
                    <br />
                </td>
            </tr>

        <?php endif ?>
        <tr>
            <td colspan="2" align="center" style="font-family: Georgia, serif; font-size: 14px; color: #000;line-height: 15px">
                Consulta terminos y condiciones en <strong>http://www.sea-express.com</strong>
            </td>
        </tr>

        <tr>
            <td colspan="2"><br><br><br><br></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 14px; color: #000;">Firma</td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 14px; color: #000;">Acepto términos y condiciones establecidos</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 14px; color: #000;">Su envío a tiempo
                <?php if (Yii::$app->user->identity->id == 26) : ?>
                    <br>www.MOVI.RED
                <?php else : ?>
                    <br>www.SEA-EXPRESS.com
                <?php endif ?>
            </td>
        </tr>
        <tr>
            <?php if (Yii::$app->user->identity->id == 26) : ?>
                <td colspan="2" align="center"> <img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" />
                    <p style="font-size: 14px; color: #000;"> Buscar tus paquetes desde nuestra App. Descargala en PlayStore/AppStore <strong>MOVI RED</strong></p>
                </td>
            <?php else : ?>
                <td colspan="2" align="center"> <img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" />
                    <p style="font-size: 14px; color: #000;"> Buscar tus paquetes desde nuestra App. Descargala en PlayStore/AppStore <strong>SEA EXPRESS</strong></p>
                </td>
            <?php endif ?>
        </tr>
    </table>

</body>