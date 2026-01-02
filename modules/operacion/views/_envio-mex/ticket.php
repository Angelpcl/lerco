<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\envio\Envio;
use app\models\esys\EsysSetting;

$color = "#OOOOOO";
$colorGris  = "#D8D8D8";

$qrCode = (new QrCode($model->folio))
            ->setSize(100)
            ->setMargin(2)
            ->setErrorCorrectionLevel('medium');
$code = [];

$code['qrBase64'] =  $qrCode->writeDataUri();
$array_cliente_id =  [];
$suma_asegurada     = 0;
$total_pieza        = 0;
?>

<body>
    <table style="font-size: 12px">
        <tr>
            <td colspan="2" align="center">
                <?= Html::img('@web/img/sea-black.png', ["height"=>"150px"]) ?>
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
        </tr>
        */?>

        <?php foreach ($model->envioDetalles as $key => $item): ?>
            <?php if ($item->status != EnvioDetalle::STATUS_CANCELADO): ?>
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
                            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
                                <?= "REENVIO (" .  ( isset($model->direccion->esysDireccionCodigoPostal->estado->singular) ? $model->direccion->esysDireccionCodigoPostal->estado->singular : '' )  . " / " . (isset($model->direccion->esysDireccionCodigoPostal->municipio->singular)  ? $model->direccion->esysDireccionCodigoPostal->municipio->singular : '') ." )" ?>
                            <?php else: ?>
                                <?= isset( $item->sucursalReceptor->nombre) ?  $item->sucursalReceptor->nombre : ''  ?>
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
            <?php endif ?>
        <?php endforeach ?>


        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>
        <tr>
            <td width="50%" style="background-color:<?php echo $color; ?>; border: none; ">
                <table style="color: white;">
                    <tr>
                        <td><small><strong style="font-size: 9px;">TOTAL DE PIEZAS</strong></small></td>
                        <td> <?= $total_pieza ?></td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">PESO TOTAL EN LBS</strong></small></td>
                        <td>  <?= number_format($model->peso_total, 2) ?>lb</td>
                    </tr>
                    <tr>
                        <td><small><strong style="font-size: 9px;">COSTO POR LB DLLS</strong></small></td>
                        <td> <?= $model->precio_libra_actual ?></td>
                    </tr>
                </table>
            </td>
            <td width="50%" style="background-color:<?php echo $color; ?>; border: none; ">
                <table style="color: white;">
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
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Id est magni soluta ea aliquam tenetur, quo sequi nemo nisi nihil esse tempora vel nobis placeat ad ullam eius minima perspiciatis.
                Dicta ratione quas delectus, tenetur officia quos accusantium blanditiis facilis quia debitis minus, amet possimus illum quae reiciendis quam iusto accusamus facere saepe tempora commodi. Architecto, voluptates voluptatibus suscipit minima!
                Vel nesciunt debitis inventore assumenda, fugiat, totam sequi ipsa exercitationem saepe placeat, voluptas ab neque reprehenderit voluptatum autem? Consectetur, temporibus accusantium maxime sed, repudiandae dolorum nam inventore et odit harum?
                <br>
                <br>

                <strong>Lorem ipsum dolor sit amet consectetur adipisicing elit. Atque perferendis necessitatibus ratione vitae possimus vel tempora id ipsam itaque dignissimos aperiam reprehenderit aliquid, delectus. Impedit eius eaque nobis, itaque consequuntur.

                Lorem ipsum dolor sit amet, consectetur adipisicing, elit. Deleniti hic, minus nostrum harum quis numquam amet impedit voluptatem voluptates aliquid quod possimus facilis magni commodi iure labore officiis odit itaque.
                <br/>
                <br/>
                <br/>
            </td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px">
                Consulta terminos y condiciones en <strong>http://www.sea-express.com</strong>
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
                <br>www.SEA-EXPRESS.com
            </td>
        </tr>
        <tr>

            <td colspan="2" align="center"> <img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /> <p> Buscar tus paquetes desde nuestra App. Descargala en PlayStore/AppStore <strong>AppSea</strong></p> </td>
        </tr>
    </table>

</body>
