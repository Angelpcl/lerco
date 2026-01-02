<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\movimiento\MovimientoPaquete;
use app\models\Esys;
 ?>
<?php if (isset($tracked)): ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="ibox">
            <div class="ibox-title">
                <h5 >Información Envio</h5>
            </div>
            <div class="ibox-content">
                <?= DetailView::widget([
                    'model' => $tracked,
                    'attributes' => [
                        'id',
                        [
                             'attribute' => 'Tipo de envío',
                             'format'    => 'raw',
                             'value'     =>  isset($tracked->envio->tipo_envio) ? Envio::$tipoList[$tracked->envio->tipo_envio] : '' ,
                         ],
                         'envio.peso_total',
                         'observaciones:ntext',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Sucursal Emisor</h5>
                    </div>
                    <div class="ibox-content">
                        <?= DetailView::widget([
                            'model' => $tracked,
                            'attributes' => [
                                [
                                 'attribute' => 'Sucursal Emisor',
                                 'format'    => 'raw',
                                 'value'     =>  isset($tracked->envio->sucursalEmisor->nombre) ?  Html::a($tracked->envio->sucursalEmisor->nombre, ['/sucursales/sucursal/view', 'id' => $tracked->envio->sucursalEmisor->id], ['class' => 'text-primary']) : '' ,
                                ],
                                'envio.sucursalEmisor.encargadoSucursal.nombreCompleto',
                                [
                                 'attribute' => 'Tipo de sucursal',
                                 'format'    => 'raw',
                                 'value'     =>  isset($tracked->envio->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$tracked->envio->sucursalEmisor->tipo] : '' ,
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Sucursal Receptor</h5>
                    </div>
                    <div class="ibox-content">
                        <?= DetailView::widget([
                            'model' => $tracked,
                            'attributes' => [
                                [
                                     'attribute' => 'Sucursal Receptor',
                                     'format'    => 'raw',
                                     'value'     =>  isset($tracked->sucursalReceptor->nombre) ?  Html::a($tracked->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $tracked->sucursalReceptor->id], ['class' => 'text-primary']) : '' ,
                                 ],
                                'sucursalReceptor.encargadoSucursal.nombreCompleto',
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
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
                                    <th style="text-align: center;">Usuario</th>
                                    <th style="text-align: center;">Fecha</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach (MovimientoPaquete::getMovimientoItem($tracked->tracked_movimiento) as $key => $item): ?>
                                    <tr>
                                        <td><?= $item->tracked  ?></td>

                                        <td>
                                            <?= $tracked->envio->tipo_envio == Envio::TIPO_ENVIO_MEX  ? MovimientoPaquete::$tipoMexList[ $item->tipo_movimiento] : MovimientoPaquete::$tipoLaxTierList[ $item->tipo_movimiento] ?>

                                                <?php if ($item->tipo_movimiento == MovimientoPaquete::LAX_TIER_PAQUETERIA): ?>
                                                    <p><strong>PAQUETERIA: </strong> <?= $item->paqueteria ?></p>
                                                    <p><strong>N° de guia:</strong> <?= $item->paqueteria_no_guia ?></p>
                                                <?php endif ?>

                                                <?php if ($item->tipo_envio  == Envio::TIPO_ENVIO_MEX    && $item->tipo_movimiento ==  MovimientoPaquete::MEX_CAJA): ?>
                                                    : <strong><small><?= $item->caja->nombre  ?></small></strong>
                                                <?php endif ?>

                                                <?php if ($item->tipo_envio == Envio::TIPO_ENVIO_TIERRA && $item->tipo_movimiento == MovimientoPaquete::LAX_TIER_PROCESO_ENTREGA ): ?>
                                                    <p><strong>ENTREGA PROGRAMADA PARA EL: <?= date("Y-m-d", $item->fecha_entrega ) ?></strong></p>
                                                <?php endif ?>
                                        </td>


                                        <td><?= isset($item->createdBy->nombreCompleto) ? $item->createdBy->nombreCompleto : '' ?></td>
                                        <td><?= Esys::fecha_en_texto($item->created_at,true) ?></td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var $tipo_movimiento        = $('.tipo_movimiento_lax_tierra'),
    $tipo_servicio              = $('#tipo_envio'),
    $div_caja_select            = $('.div_caja_select'),
    $div_trancurso_mex_select   = $('.div_trancurso_mex_select'),
    $div_trancurso_tierra_select= $('.div_trancurso_tierra_select'),
    $div_entregado_por_select   = $('.div_entregado_por_select'),
    $div_proceso_entrega_select = $('.div_proceso_entrega_select'),
    $div_trancurso_lax_select   = $('.div_trancurso_lax_select'),
    $MEX_CAJA                   = <?= MovimientoPaquete::MEX_CAJA ?>,
    $LAX_TIER_PAQUETERIA        = <?= MovimientoPaquete::LAX_TIER_PAQUETERIA ?>,
    $MEX_TRANSCURSO             = <?= MovimientoPaquete::MEX_TRANSCURSO ?>,
    $LAX_TIER_TRANSCURSO        = <?= MovimientoPaquete::LAX_TIER_TRANSCURSO ?>,
    $LAX_TIER_PROCESO_ENTREGA        = <?= MovimientoPaquete::LAX_TIER_PROCESO_ENTREGA ?>,
    $TIPO_ENVIO_MEX             =  <?= Envio::TIPO_ENVIO_MEX  ?>,

    $TIPO_ENVIO_TIERRA          =  <?= Envio::TIPO_ENVIO_TIERRA  ?>;
$(document).ready(function() {
    $tipo_movimiento.trigger('change');
});
$tipo_movimiento.change(function(){

    $div_caja_select.hide();
    $div_trancurso_mex_select.hide();
    $div_trancurso_tierra_select.hide();
    $div_proceso_entrega_select.hide();
    $div_trancurso_lax_select.hide();
    $div_entregado_por_select.hide();

    if (parseInt($tipo_servicio.val()) == $TIPO_ENVIO_MEX ) {

        switch(parseInt($(this).val())){

            case $MEX_CAJA:
                $div_caja_select.show();
            break;

            case $MEX_TRANSCURSO:
                $div_trancurso_mex_select.show();
            break;
        }
    }

    if (parseInt($tipo_servicio.val()) == $TIPO_ENVIO_TIERRA ) {

        switch(parseInt($(this).val())){

            case $LAX_TIER_TRANSCURSO:
                $div_trancurso_tierra_select.show();
            break;

            case $LAX_TIER_PAQUETERIA:
                $div_entregado_por_select.show();
            break;

            case $LAX_TIER_PROCESO_ENTREGA:
                $div_proceso_entrega_select.show();
            break;
        }
    }


});

</script>
<?php endif ?>
