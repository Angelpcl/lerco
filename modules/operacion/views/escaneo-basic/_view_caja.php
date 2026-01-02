<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\models\sucursal\Sucursal;
use app\models\movimiento\MovimientoPaquete;
use app\models\Esys;
 ?>
<?php if (isset($caja)): ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="ibox">
            <div class="ibox-title">
                <h5 >Paquetes relacionados con la caja</h5>
            </div>
            <div class="ibox-content nano" style="    height: 500px;padding: 0;">
                <div class="nano-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Categoria</th>
                                <th style="text-align: center;">Tracked</th>
                                <th style="text-align: center;">Producto</th>
                                <th style="text-align: center;">Peso</th>
                                <th style="text-align: center;">Estatus</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($caja->cajaDetalleMex as $key => $item): ?>
                                <tr>
                                    <td><?= isset($item->envioDetalle->producto->categoria->singular) ? $item->envioDetalle->producto->categoria->singular : null ?></td>
                                    <td><?= $item->tracked ?></td>
                                    <td><?= $item->envioDetalle->producto->nombre ?></td>
                                    <td><?= $item->envioDetalle->peso ?> Lbs</td>
                                    <td><?= EnvioDetalle::$statusList[$item->envioDetalle->status] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="ibox">
            <div class="ibox-title">
                <h5 >Historial de movimiento</h5>
            </div>
            <div class="ibox-content">
                 <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Tracked</th>
                            <th style="text-align: center;">Movimiento</th>
                            <th style="text-align: center;">Usuario que realizo el movimiento</th>
                            <th style="text-align: center;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody  style="text-align: center;">
                        <?php foreach (MovimientoPaquete::getMovimientoItem($caja->tracked_movimiento) as $key => $item): ?>
                            <tr>
                                <td><?= $item->tracked  ?></td>
                                <td><?=  MovimientoPaquete::$tipoMexList[ $item->tipo_movimiento] ?></td>
                                <td><?= isset($item->createdBy->nombreCompleto) ? $item->createdBy->nombreCompleto : ''  ?></td>
                                <td><?= Esys::fecha_en_texto($item->created_at,true) ?></td>
                            </tr>

                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif ?>

<script>
var $tipo_movimiento                = $('#tipo_movimiento'),
    $div_trancurso_mex_caja_select  = $('.div_trancurso_mex_caja_select'),
    $TIPO_ENVIO_MEX                 =  <?= Envio::TIPO_ENVIO_MEX  ?>,
    $MEX_TRANSCURSO                 = <?= MovimientoPaquete::MEX_TRANSCURSO ?>;
$(document).ready(function() {
    $tipo_movimiento.trigger('change');
});

$tipo_movimiento.change(function(){
    $div_trancurso_mex_caja_select.hide();
    if (parseInt($(this).val()) == $MEX_TRANSCURSO )
        $div_trancurso_mex_caja_select.show();


});
</script>
