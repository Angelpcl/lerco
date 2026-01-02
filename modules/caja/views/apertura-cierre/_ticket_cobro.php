<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Esys;

$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";

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
            <td colspan="2" align="center" style="background-color:<?php echo $color; ?>;  color: white;  padding: 5px"><strong style="font-weight: bold;font-size: 16px;">DETALLE CIERRE DE CAJA</strong></td>
        </tr>

    	<tr>
            <td colspan="2">
                <table style="width: 100%; border-width: 0px;" >
                    <tbody  >
                        <tr>
                            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  5px; font-size: 12px;">
                                <p><strong style="font-weight: bold;">APERTURA : </strong></p>
                            </td>
                            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 12px;">
                                <p><?= Esys::fecha_en_texto($model->fecha_apertura, true)  ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:<?php echo $color; ?>;  color: white; padding:  5px; font-size: 12px;">
                                <p><strong style="font-weight: bold;">CIERRE : </strong></p>
                            </td>
                            <td style="background-color:<?php echo $colorGris; ?>;  color: black;font-size: 12px;">
                                <p><?= $model->fecha_cierre ? Esys::fecha_en_texto($model->fecha_cierre, true) : NULL ?></p>
                            </td>
                        </tr>

                    </tbody>
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
                        <td>
                            <p><strong>BILLETES DE 100</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_100 ? $model->bill_100 :  0 ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 50</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_50 ?  $model->bill_50  : 0 ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 20</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_20  ? $model->bill_20  : 0 ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 10</strong></p>

                        </td>
                        <td><span class="text-3x text-thin"><?= $model->bill_10  ? $model->bill_10  : 0 ?></span></td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 5</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_5 ? $model->bill_5 : 0  ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 2</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_2 ? $model->bill_2 : 0  ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>BILLETES DE 1</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->bill_1 ? $model->bill_1 :  0 ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p><strong>Monedas / Change</strong></p>

                        </td>
                        <td>
                            <span class="text-3x text-thin"><?= $model->change ? $model->change : 0  ?></span>
                        </td>
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
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>MONTO APERTURA</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->cantidad_apertura, 2)   ?> </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 50%">
                            <table style="border-collapse:collapse; border: none;">
                                <tr>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"><small><strong>MONTO CIERRE</strong></small></td>
                                    <td  style="background-color:<?php echo $color; ?>;  color: white; font-weight: bold; font-size: 10px; padding:  5px;"> $<?= number_format($model->cantidad_cierre, 2) ?> </td>
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
            <td colspan="2"></td>
        </tr>

    </table>
</body>
