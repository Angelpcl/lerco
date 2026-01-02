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
            <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold;   padding:  10px;">
                <p><strong style="font-weight: bold;  font-size: 16px;">REMITENTE: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 14px;">
                <p style="color: #000; font-size: 16px;"><?= isset($model->clienteEmisor->nombreCompleto) ?  $model->clienteEmisor->nombreCompleto: ''  ?></p>
            </td>
        </tr>
        <tr>
            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  10px;  ">
                <p><strong style="font-weight: bold;  font-size: 15px;">SUCURSAL E.: </strong></p>
            </td>
            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 14px;">
                <p style="color: #000; font-size: 16px;"><?= $model->sucursalEmisor->nombre ?> (<?= isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : ''  ?>)</p>
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
                    <td style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; padding:  5px;">
                        <p><strong style="font-weight: bold; font-size: 16px;">SUCURSAL(<?= $key + 1 ?>): </strong></p>
                    </td>
                    <td style="background-color:<?php echo $colorGris; ?>;  color: black; font-size: 12px;">
                        <p style="color: #000; font-size: 16px;">
                            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
                                <?= "REENVIO (" .  ( isset($model->direccion->esysDireccionCodigoPostal->estado->singular) ? $model->direccion->esysDireccionCodigoPostal->estado->singular : '' )  . " / " . (isset($model->direccion->esysDireccionCodigoPostal->municipio->singular)  ? $model->direccion->esysDireccionCodigoPostal->municipio->singular : '') ." )" ?>
                            <?php else: ?>
                                <?= isset( $item->sucursalReceptor->nombre) ?  $item->sucursalReceptor->nombre : ''  ?>
                            <?php endif ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color:<?php echo $color; ?>;  color: white; padding:  5px;  ">
                        <p><strong style="font-weight: bold; font-size: 15px;">DESTINARIO(<?= $key + 1 ?>) : </strong></p>
                    </td>
                    <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 12px;">
                        <p style="color: #000; font-size: 16px;"><?= isset($item->clienteReceptor->nombreCompleto) ?  $item->clienteReceptor->nombreCompleto : ''  ?></p>
                    </td>
                </tr>

                <?php $suma_asegurada = $suma_asegurada + ($item->status !=  EnvioDetalle::STATUS_CANCELADO ? $item->valor_declarado : 0) ?>

                <tr>
                    <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold; font-size: 18px;">PAQ # <?= $item->tracked ?>    <small style="font-size: 14px;">      <?= $item->cantidad ?> PZAS</small></strong></td>
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
                                            <td style=" text-align: center; color: #000; font-size: 16px; font-family: Georgia, serif; line-height: 15px">PAQ #<?= $item->tracked  ?>/<?= $pieza_count ?> </td>
                                            <td style=" text-align: center; color: #000; font-size: 16px; font-family: Georgia, serif; line-height: 15px"><?= $item->producto->nombre ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <?php if ($item->observaciones): ?>
                                            <td style=" text-align: center; color: #000; font-size: 14px; font-family: Georgia, serif; line-height: 15px"><?= $item->observaciones  ?> </td>
                                        <?php endif ?>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
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
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>
        <tr>
            <td width="50%" style="border: none; ">
                <table >
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">TOTAL DE PIEZAS</strong></td>
                        <td style=" color: #000; font-size: 14px;"> <?= $total_pieza ?></td>
                    </tr>
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">PESO TOTAL EN LBS</strong></td>
                        <td style=" color: #000; font-size: 14px;">  <?= number_format($model->peso_total, 2) ?>lb</td>
                    </tr>
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">COSTO POR LB DLLS</strong></td>
                        <td style=" color: #000; font-size: 14px;"> <?= $model->precio_libra_actual ?></td>
                    </tr>
                </table>
            </td>
            <td width="50%" style="border: none; ">
                <table >
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">PAGO DE ASEGURANZA</strong></td>
                        <td style=" color: #000; font-size: 14px;"> $<?= number_format($model->seguro_total, 2) ?>  </td>
                    </tr>
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">CANTIDAD ASEGURADA</strong></td>
                        <td style=" color: #000; font-size: 14px;"> $<?= number_format($suma_asegurada, 2) ?>  </td>
                    </tr>
                    <tr>
                        <td><strong style="color: #000; font-size: 14px;">OTROS CARGOS</strong></td>
                        <td style=" color: #000; font-size: 14px;">$<?= number_format($model->impuesto, 2) ?>  </td>
                    </tr>
                </table>
            </td>
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
                                    <td  style=" font-weight: bold; font-size: 16px; padding:  5px;"><small><strong>COSTO TOTAL</strong></small></td>
                                    <td  style=" font-weight: bold; font-size: 16px; padding:  5px;"> $<?= number_format($model->total, 2)   ?> </td>
                                </tr>
                                <tr>
                                    <td  style=" font-weight: bold; font-size: 16px; padding:  5px;"><small><strong>BALANCE</strong></small></td>
                                    <td  style=" font-weight: bold; font-size: 16px; padding:  5px;"> $<?= number_format($model->total - $model->totalPagado , 2) ?> </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>



        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" align='justify' style="font-family: Georgia, serif; line-height: 15px">
                <p style="line-height: 15px; font-size: 16px;color: #000;font-family: Georgia, serif;">
                Lorem ipsum dolor sit amet consectetur adipisicing, elit. Doloribus a quisquam impedit quam molestiae perferendis, veniam veritatis cumque similique commodi voluptatem quidem eius accusamus inventore consequuntur? In consequuntur error unde.
                <br>
                <br>

                Lorem ipsum dolor sit amet consectetur, adipisicing, elit. Necessitatibus, aliquid nobis temporibus facere voluptatum, veritatis quod accusantium enim. Sit aliquid provident repellat dolore autem ipsa commodi pariatur rerum veniam rem.
                <br/>
                <br/>
                <br/>
                </p>
            </td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px;font-size: 16px;color: #000;">
                Consulta terminos y condiciones en <strong>http://www.seaexpresslogistica.com</strong>
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
            <td colspan="2" align="center" style="font-size: 14px;color: #000;">Firma</td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 14px;color: #000;">Acepto términos y condiciones establecidos</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 14px;color: #000;">Su envío a tiempo
                <br>www.seaexpresslogistica.com
            </td>
        </tr>
    </table>
</body>


