<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysSetting;
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
?>
<body>
    <table style="font-size: 12px">
        <?php if ($model->status == Envio::STATUS_SOLICITADO ): ?>
            <tr>
                <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 16px; padding:  10px;">
                    <strong style="font-weight: bold;"><?= Envio::$statusList[$model->status]  ?> </strong>
                </td>
            </tr>
        <?php endif ?>
        <tr>
            <td colspan="2" align="center">
                <?= Html::img('@web/img/logo-cora_dark.png', ["height"=>"150px"]) ?>
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 16px; padding:  10px;">
                <p><strong style="font-weight: bold;">REMITENTE: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 14px;">
                <?= isset($model->clienteEmisor->nombreCompleto) ?  $model->clienteEmisor->nombreCompleto: ''  ?></p>
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  10px; font-size: 14px;">
                <p><strong style="font-weight: bold;">SUCURSAL E.: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 14px;">
                <p><?= $model->sucursalEmisor->nombre ?> (<?= isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : ''  ?>)</p>
            </td>
        </tr>

        <?php /* ?>
            <tr>
                <td align='center' colspan="2" style="background-color:<?php echo $color; ?>;  color: white;">
                    <strong style="font-size: 14px;">Paquetes del Envío</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2"></td>
            </tr>*/
            ?>

    	<?php foreach ($model->envioDetalles as $key => $item): ?>

            <tr style="padding: 0">
                <td colspan="2" style="padding: 0">
                    <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
                </td>
            </tr>

            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                    <p><strong style="font-weight: bold;">SUCURSAL(<?= $key + 1 ?>): </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                    <p>
                        <?= isset( $item->sucursalReceptor->nombre) ?  $item->sucursalReceptor->nombre : ''  ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 12px; padding:  5px;">
                    <p><strong style="font-weight: bold;">REENVIO(<?= $key + 1 ?>): </strong></p>
                </td>
                <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                    <p>
                        <?= isset( $item->is_reenvio ) && $item->is_reenvio == EnvioDetalle::REENVIO_ON ?  'SI' : 'NO'  ?>
                        /<?php if (isset($item->direccion)): ?>
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
            <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO ): ?>
                <tr>
                    <td align='center' colspan="2" style="background-color:#d32f2f; color: white; font-size: 16px; padding: 5px">
                        <strong style="font-size: 16px;font-weight: bold;">==== PAQUETE CANCELADO ===</strong>
                    </td>
                </tr>
            <?php endif ?>
            <tr>
                <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold;font-size: 16px;">PAQ # <?= $item->tracked ?>    <small style="font-size: 10px;">      <?= $item->cantidad ?> PZAS</small></strong></td>
            </tr>

        	<tr>
                <td colspan="2">
                    <table style="width: 100%; border-width: 0px;" >
                        <tbody  >
                            <?php if ($item->status != EnvioDetalle::STATUS_CANCELADO) {
                                $total_pieza = $total_pieza + $item->cantidad;?>
                                <?php for ($i=0; $i < $item->cantidad; $i++) {
                                    $pieza_count = $i + 1; ?>
                                    <tr>
                                        <td style=" text-align: center; font-size: 16px; font-family: Georgia, serif; line-height: 15px">PAQ #<?= $item->tracked  ?>/<?= $pieza_count ?> </td>
                                        <td style=" text-align: center; font-size: 16px; font-family: Georgia, serif; line-height: 15px"><?= $item->producto->nombre ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <?php if ($item->observaciones): ?>
                                        <td style=" text-align: center; font-size: 12px; font-family: Georgia, serif; line-height: 15px"><?= $item->observaciones  ?> </td>
                                    <?php endif ?>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </td>
            </tr>

            <?php /* ?><tr>
                <td width="40%" style="font-weight: bold">Categoria</td>
                <td width="60%" style="font-weight: bold">Producto</td>
            </tr>

            <tr style="font-size: 8px">
                <td><?= $item->categoria->singular ?></td>
                <td><?= $item->producto->nombre ?></td>
            </tr>
            <br>

            <tr>
                <td width="60%" style="font-weight: bold">N° de piezas</td>
                <td width="40%" style="font-weight: bold">N° Elementos</td>
            </tr>
            <tr style="font-size: 8px">
                <td><?= $item->cantidad ?></td>
                <td><?= $item->cantidad_piezas?></td>
            </tr>
            <br>
            <tr>
                <td width="40%" style="font-weight: bold">Seguro</td>
                <td width="60%" style="font-weight: bold">Valor Declarado</td>
            </tr>
            <tr style="font-size: 8px">
                <td><?= $item->seguro == 1 ? "Si": "No" ?></td>
                <td><?= $item->valor_declarado ?> / Seguro por pieza: <?= number_format(($model->tipo_envio == Envio::TIPO_ENVIO_TIERRA ? (EsysSetting::getCobroSeguroTierra() * $item->valor_declarado ) / 100 : (EsysSetting::getCobroSeguroLax() * $item->valor_declarado ) / 100 ) / $item->cantidad,2)   ?> </td>
            </tr>
            <br>
            <tr>
                <td  colspan="2" style="font-weight: bold">Observaciones
                </td>
            </tr>
            <tr>
                <td colspan="2"><?= $item->observaciones ?></td>
            </tr>

            */?>

            <?php if ($item->status ==  EnvioDetalle::STATUS_CANCELADO ): ?>
                <tr>
                    <td align='center' colspan="2" style="background-color:#d32f2f; color: white;">
                        <strong>=============================</strong>
                    </td>
                </tr>
            <?php endif ?>
    	<?php endforeach ?>


        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white; font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>
        <tr>
            <td width="50%">
                <table>
                    <tr>
                        <td><small><strong style="font-size: 9px;">TOTAL DE PIEZAS</strong></small></td>
                        <td> <?= $model->pienzasTotal ?></td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">PESO TOTAL EN LBS</strong></small></td>
                        <td>  <?= number_format($model->peso_total, 2) ?> lb</td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">LIBRAS DE REGALO</strong></small></td>
                        <td>
                            <?php $totalLibraFree =  0;  ?>
                            <?php foreach ($model->envioComplementoPromocion as $key => $envioComplemento): ?>
                                <?php if ($envioComplemento->complemento->is_lb_free  == PromocionComplemento::ON_LBFREE): ?>
                                        <?php $totalLibraFree = $totalLibraFree + $envioComplemento->complemento->lb_free  ?>
                                <?php endif ?>
                            <?php endforeach ?>
                            <?= $totalLibraFree  ?>
                         </td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">LIBRAS PAGADAS</strong></small></td>
                        <td>

                            <?php foreach ($model->envioComplementoPromocion as $key => $envioComplemento): ?>
                                <?php if ($envioComplemento->complemento->is_lb_free  == PromocionComplemento::ON_LBFREE): ?>
                                    <?php   $libras_sobrantes  =  $model->peso_total - $model->promocionDetalle->lb_requerida;
                                            $libras_restantes = $libras_sobrantes - $envioComplemento->complemento->lb_free;
                                            $libras_pagas     =  $libras_restantes > 0 ?  $model->promocionDetalle->lb_requerida + $libras_restantes : $model->promocionDetalle->lb_requerida;
                                    ?>

                                    <?= number_format($libras_pagas, 2) ?>
                                <?php endif ?>
                            <?php endforeach ?>

                        </td>
                    </tr>
                </table>

            </td>
            <td width="50%">
                <table>
                    <tr>
                        <td><small><strong style="font-size: 9px;">COSTO POR LB DLLS</strong></small></td>
                        <td> <?= $model->promocion_detalle_id ?  $model->codigo_promocional_id ? $model->promocionDetalle->costo_libra_code : $model->promocionDetalle->costo_libra_sin_code  : $model->precio_libra_actual ?></td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">PAGO DE AEGURANZA</strong></small></td>
                        <td> $<?= number_format($model->seguro_total, 2) ?>  </td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">CANTIDAD ASEGURADA</strong></small></td>
                        <td> $<?= number_format($suma_asegurada, 2) ?>  </td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">CARGO DE REENVIO DLLS</strong></small></td>
                        <td>$<?= number_format($model->costo_reenvio, 2) ?> </td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">OTROS CARGOS</strong></small></td>
                        <td>$<?= number_format($model->impuesto, 2) ?>  </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <table width="100%">
                    <tr>
                        <td style="width: 50%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>COSTO TOTAL</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->total, 2)   ?> </td>
                                </tr>
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>PAGO</strong></small></td>
                                    <td   style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->totalPagado, 2)   ?>  </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 50%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>BALANCE</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->total - $model->totalPagado , 2) ?> </td>
                                </tr>
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>CAMBIO</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format(0, 2)   ?>  </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <?php /* ?>
        <tr>
            <td colspan="2">
                <table style="width: 100%; border-style: solid; border-width: 1px;" >
                    <thead >
                        <tr style="background-color: <?php echo $color; ?>;">
                            <th style="text-align: center; color:white;">Paquete</th>
                            <th style="text-align: center; color:white;">Tracked</th>
                            <th style="text-align: center; color:white;">Pieza</th>
                        </tr>
                    </thead>
                    <tbody  style="text-align: center;">
                        <?php foreach ($model->envioDetalles as $key => $item):
                            if ($item->status != EnvioDetalle::STATUS_CANCELADO) {
                                $total_pieza = $total_pieza + $item->cantidad;
                            ?>
                                <?php for ($i=0; $i < $item->cantidad; $i++) {
                                    $pieza_count = $i + 1; ?>
                                    <tr>
                                        <td style=" text-align: center;"><?= $item->producto->nombre ?></td>
                                        <td style=" text-align: center;"><?= $item->tracked  ?>/<?= $pieza_count ?> </td>
                                        <td style=" text-align: center;">1</td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style=" text-align: center;"><strong>Total de piezas</strong></td>
                            <td style=" text-align: center;"><strong><?= $total_pieza ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        */?>

        <?php /* ?>
        <tr>
            <td align='center' colspan="2" style="background-color:<?php echo $color; ?>; color: white;">
                <strong style="font-size: 14px;">Datos del Envío</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>

        <tr>
            <td align='center' colspan="2" style="background-color: #9C9B99; font-size: 14px;">Cliente que Envía
            </td>
        </tr>
        <tr>
            <td>Cliente:</td>
            <td><?= isset($model->clienteEmisor->nombreCompleto) ? $model->clienteEmisor->nombreCompleto : ''?></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><?= isset($model->clienteEmisor->email) ? $model->clienteEmisor->email : ''?></td>
        </tr>
        <tr>
            <td>Teléfonos</td>
            <td><?= isset($model->clienteEmisor->telefono) ? $model->clienteEmisor->telefono : ''?> / <?= isset($model->clienteEmisor->telefono_movil) ? $model->clienteEmisor->telefono_movil : ''?></td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td align='center' colspan="2" style="background-color: #9C9B99; font-size: 14px;">Cliente que Recibe
            </td>
        </tr>

        <?php foreach ($model->envioDetalles as $key => $item): ?>
            <?php if (!in_array($item->clienteReceptor->id, $array_cliente_id)): ?>
                <tr>
                    <td>Cliente:</td>
                    <td><?= isset($item->clienteReceptor->nombreCompleto) ? $item->clienteReceptor->nombreCompleto : ''?></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><?= isset($item->clienteReceptor->email) ? $item->clienteReceptor->email : ''?></td>
                </tr>
                <tr>
                    <td>Teléfono:</td>
                    <td><?= isset($item->clienteReceptor->telefono) ? $item->clienteReceptor->telefono : ''?></td>
                </tr>
                <tr>
                    <td>Estado:</td>
                    <td><?= isset($item->clienteReceptor->direccion->estado->singular) ? $item->clienteReceptor->direccion->estado->singular : ''?></td>
                </tr>
                <tr>
                    <td>Municipio:</td>
                    <td><?= isset($item->clienteReceptor->direccion->municipio->singular) ? $item->clienteReceptor->direccion->municipio->singular : ''?></td>
                </tr>
                <tr>
                    <td>Colonia:</td>
                    <td><?= isset($item->clienteReceptor->direccion->esysDireccionCodigoPostal->codigo_postal) ? $item->clienteReceptor->direccion->esysDireccionCodigoPostal->colonia : ''?></td>
                </tr>
                <tr>
                    <td>Dirección:</td>
                    <td><?= isset($item->clienteReceptor->direccion->direccion) ? $item->clienteReceptor->direccion->direccion : ''?></td>
                </tr>
                <tr>
                    <td>CP:</td>
                    <td><?= isset($item->clienteReceptor->direccion->esysDireccionCodigoPostal->codigo_postal) ? $item->clienteReceptor->direccion->esysDireccionCodigoPostal->codigo_postal : ''?></td>
                </tr>

                <?php array_push($array_cliente_id, $item->clienteReceptor->id); ?>
            <?php endif ?>
        <?php endforeach ?>
*/?>

        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>
        <tr style="font-size: 8px">
            <td colspan="2" align='justify' style="font-family: Georgia, serif; line-height: 15px">
                Declaro bajo juramento que los presentes datos obedecen a la verdad,
                sometiéndome a las disposiciones administrativas de la paquetería. En caso
                de extravío de mi mercancía tomaré como aceptable el valor que estoy
                declarando aceptando que debo declarar toda la mercancía que envío y
                también así la negociación que la paquetería me ofrezca en caso de falsedad
                en esta declaración y perderé todo derecho de reclamo sobre mi envío
                aceptando el término de negociación que la paquetería me ofrezca.
                <br>
                <br>

                <strong>Por favor indicar a sus familiares al momento de recibir su paquete que lo
                revise enfrente del representante de la paquetería</strong>   o de la persona que se
                lo entregue ya que no aceptamos reclamos una vez que ha salido o recibido
                su paquete. En caso de algún faltante anotarlo al momento de firmar e
                inmediatamente llamar a la paquetería en Puebla al número
                011-52-222-483-88-29 ó 574-35-10. Gracias.
                <br/>
                <br/>
                <br/>
            </td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px">
                Consulta terminos y condiciones en <strong>http://www.paqueterialacora.com</strong>
          </td>
        </tr>
        <?php /* ?>
        <tr>
            <td align='center' colspan="2" style="background-color:<?php echo $colorGris; ?>; color: black; ">
                <strong style="font-size: 16px;">POLITICAS DE ENVÍO</strong>
            </td>
        </tr>
        <br>

        <tr>
            <td colspan="2" align="justify" style="font-family: Georgia, serif; line-height: 15px">
            ESTIPULANDO UD. EL VALOR DE SU MERCANCIA PAGARA EL 5% DE VIAJE POR TIERRA Y
            EL 4% DE VIAJE EN AVION


            <strong>1.- SI USTED NO DECLARA OBJETOS DE VALOR LA
            EMPRESA NO SE HACE RESPONSABLE EN CASO DE PERDIDA, ROBO O EXTRAVIO.</strong>

            2.-PAGANDO ASEGURANZA Y PRESENTANDO SUS NOTAS DE COMPRA SE REEMBOLSA EL 100%
            DEL VALOR DECLARADO.

            3.- EL TIEMPO DE PAGO CON ASEGURANZA ES DE 15 A 20
            DIAS.

            4.-SI NO PAGA ASEGURANZA SE LE REEMBOLSA SOLAMENTE EL 30% DEL VALOR
            DECLARADO Y NECESARIAMENTE TENDRA QUE PRESENTAR FACTURAS DE COMPRA, NO SE
            ACEPTAN NOTAS HECHAS A MANO.

            5.- EL TIEMPO DE PAGO DE PAQUETES SIN
            ASEGURANZA ES INDEFINIDO HASTA CULMINAR LA NEGOCIACION Y ACREDITACIÓN DE LA
            MERCANCIA SOLO CON FACTURAS Y CON FECHA NO POSTERIOR AL ENVIO.

            6.- EL PAGO POR REPOSICION SE DIVIDIRA ENTRE LAS PIEZAS DECLARADAS.
            </td>
        </tr>

        */ ?>

        <tr>
            <td colspan="2"><br><br><br><br></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">Firma</td>
        </tr>
        <tr>
            <td colspan="2" align="center">Acepto términos y condiciones establecidos</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 10px">Su envío a tiempo
                <br>www.paqueterialacora.com
            </td>
        </tr>
        <tr>

            <td colspan="2" align="center"> <img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /> <p> Buscar tus paquetes desde nuestra App. Descargala en PlayStore/AppStore <strong>AppCora</strong></p> </td>
        </tr>
    </table>

</body>
