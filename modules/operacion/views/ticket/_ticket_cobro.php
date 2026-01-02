<?php
use yii\helpers\Html;
use yii\helpers\Url;

use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysSetting;
use app\models\promocion\PromocionComplemento;
use app\models\cobro\CobroRembolsoEnvio;

$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";

$is_pagos = [];
foreach (CobroRembolsoEnvio::getRembolso($envio->id) as $key => $pagos) {
    array_push($is_pagos,$pagos);
}



?>
<body>
    <table style="font-size: 12px">
        <tr>
            <td colspan="2" align="center">
                <?= Html::img('@web/img/logo-cora_dark.png', ["height"=>"150px"]) ?>
            </td>
        </tr>
        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold;font-size: 16px;">NOTA DE CREDITO ( <?= $model->ticket_item_id  ?> / <?= count(json_decode($ticket->fecha_rembolso)) ?>)</strong></td>
        </tr>
        <tr>
            <td colspan="2">
                <table style="width: 100%; border-width: 0px;" >
                    <tbody>
                        <?php if (count(json_decode($ticket->fecha_rembolso)) > 0): ?>
                            <tr>
                                <td style="text-align: center;">#</td>
                                <td style="text-align: center;">Monto</td>
                                <td style="text-align: center;">Fecha</td>
                                <td style="text-align: center;">Total</td>
                            </tr>
                        <?php endif ?>
                        <?php $total =  $ticket->totalRembolso ?>

                        <?php if ($ticket->fecha_rembolso): ?>
                            <?php foreach (json_decode($ticket->fecha_rembolso) as $key => $item): ?>
                                <?php $is_pago = false; ?>
                                <?php foreach ($is_pagos as $key => $pago): ?>
                                    <?php if ($pago["ticket_item_id"] == $item->id): ?>
                                        <?php $is_pago = true; ?>
                                    <?php endif ?>
                                <?php endforeach ?>
                                <tr >
                                    <td style="text-align: center; <?= $is_pago ? 'text-decoration: line-through;text-decoration-style: double;' : ''?> " ><?= $item->id ?></td>
                                    <td style="text-align: center; <?= $is_pago ? 'text-decoration: line-through;text-decoration-style: double;' : ''?> " ><?= $item->monto ?></td>
                                    <td style="text-align: center; <?= $is_pago ? 'text-decoration: line-through;text-decoration-style: double;' : ''?> " ><?= $item->fecha ?></td>
                                    <td style="text-align: center; <?= $is_pago ? 'text-decoration: line-through;text-decoration-style: double;' : ''?> " ><strong>$<?=  number_format($total,2) ?></strong></td>
                                </tr>
                                <?php $total =  $ticket->totalRembolso  - floatval($item->monto) ?>
                            <?php endforeach ?>

                        <?php endif ?>

                    </tbody>
                </table>
            </td>
        </tr>

    	<tr>
            <td colspan="2">
                <table style="width: 100%; border-width: 0px;" >
                    <tbody  >
                        <tr>
                            <td style=" text-align: center; font-size: 16px; font-family: Georgia, serif; line-height: 15px"><?= $model->cantidad  ?> </td>
                        </tr>
                        <tr>
                            <td style=" text-align: center; font-size: 12px; font-family: Georgia, serif; line-height: 15px"><?= $model->nota  ?> </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white; font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN DE ENVIO</strong></td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="table table-bordered invoice-summary">
                    <tr>
                        <th class="min-col text-uppercase" > FOLIO</th>
                        <th class="min-col text-center text-uppercase" ><?= $envio->folio ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase" > TOTAL ENVIO</th>
                        <th class="min-col text-center text-uppercase" ><?= number_format($envio->total,2) ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase"> N° PIEZAS</th>
                        <th class="min-col text-center text-uppercase" ><?= $envio->pienzasTotal ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase"> N° PIEZAS EN DISPUTA</th>
                        <th class="min-col text-center text-uppercase" ><?= count($ticket->ticketDetalle) ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase">SUMA ASEGURADA</th>
                        <th class="min-col text-center text-uppercase" ><?= number_format($envio->valorTotal,2) ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase">COSTO PAGADO DEL SEGURO</th>
                        <th class="min-col text-center text-uppercase" ><?= number_format($envio->seguro_total,2) ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase">COSTO PROPORCIONAL DEL SEGURO POR PIEZA</th>
                        <th class="min-col text-center text-uppercase" ><?= number_format($envio->seguro_total / $envio->pienzasTotal,2) ?></th>
                    </tr>
                    <tr>
                        <th class="min-col text-uppercase">BALANCE A PAGAR</th>
                        <th class="min-col text-center text-uppercase" ><?= $model->cantidad ?></th>
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
            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white; font-size: 16px; padding: 5px"><strong style="font-weight: bold;">RESUMEN</strong></td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <table width="100%">
                    <tr>
                        <td style="width: 50%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>COSTO TOTAL</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($envio->total, 2)   ?> </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 50%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>REEMBOLSO</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->cantidad , 2) ?> </td>
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
        <tr style="font-size: 8px">
            <td colspan="2" align='justify' style="font-family: Georgia, serif; line-height: 15px">
                Declaro bajo juramente que los presentes datos obedecen a la verdad,
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
    </table>

</body>
