<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\envio\Envio;
use app\assets\BootboxAsset;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysCambiosLog;
use app\models\promocion\PromocionComplemento;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\promocion\Promocion;
use app\models\envio\EnvioPromocion;
use app\models\producto\Producto;
use app\models\ticket\Ticket;
use app\models\Esys;
use app\models\esys\EsysDireccion;
use app\models\cliente\ClienteCodigoPromocion;
use app\models\descarga\DescargaBodega;

/* @var $this yii\web\View */
BootboxAsset::register($this);
$this->title =  '#'. $model->folio ;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';


?>
<style>
.modal-backdrop {
    position: relative;
}

.modal {
    z-index: 1050 !important;
}
</style>
<p>
    <?= $can['update_basic'] && $model->status != Envio::STATUS_ENTREGADO && $model->status != Envio::STATUS_CANCELADO  ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['cancel_basic'] && $model->status != Envio::STATUS_ENTREGADO && $model->status != Envio::STATUS_CANCELADO  ?
        Html::a('Cancelar', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas cancelar este envio?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>

<div class="operaciones-envio-view">
    <div class="row">
        <div class="col-md-9 col-sm-8 col-xs-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Información Envio</h3>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                 'attribute' => 'Tipo de envío',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->tipo_envio) ? Envio::$tipoList[$model->tipo_envio] : '' ,
                             ],
                             [
                                 'attribute' => 'Origen',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->origen) ? Envio::$origenList[$model->origen] : '' ,
                             ],
                             'peso_total',
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Costos del envio</h3>
                </div>
                <div class="ibox-content">

                    <div class="row totales cobros">
                        <div class="col-sm-3">
                            <span class="label" style="background-color: #fff">Subtotal</span>
                            <span class="neto monto">$ <?= number_format($model->subtotal, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-3">
                            <span class="label" style="background-color: #fff">Impuestos</span>
                            <span class="impuestos monto">$ <?= number_format($model->impuesto, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-3" >
                            <span class="label" style="background-color: #fff">Seguro</span>
                            <span class="impuestos monto">$ <?= number_format($model->seguro_total, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-3">
                            <span class="label" style="background-color: #fff">Total</span>
                            <span class="total monto">$ <?= number_format($model->total, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Cobros realizado</h3>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Tipo</th>
                                <th style="text-align: center;">Metodo de pago</th>
                                <th style="text-align: center;">Cobro</th>

                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <tbody>
                                <?php $cobroTotal = 0; ?>
                                <?php foreach ($model->cobroRembolsoEnvios as $key => $item): ?>
                                    <tr>
                                        <td class="text-center"><?= CobroRembolsoEnvio::$tipoList[$item->tipo] ?></td>
                                        <td class="text-center"><?= CobroRembolsoEnvio::$servicioList[$item->metodo_pago] ?></td>
                                        <?php if ($item->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION ): ?>
                                            <td class="text-center"> - <?= number_format($item->cantidad,2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal - $item->cantidad; ?>
                                        <?php else: ?>
                                            <td class="text-center"><?= number_format($item->cantidad,2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal + $item->cantidad; ?>
                                        <?php endif ?>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">Total</td>
                                    <td style="font-size: 17px"><?= number_format($model->total,2) ?> USD</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">Efectivo (Cobro)</td>
                                    <td style="font-size: 17px"><?= number_format($cobroTotal,2) ?> USD</td>
                                </tr>
                            </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h3 >Sucursal Emisor</h3>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                     'attribute' => 'Sucursal Emisor',
                                     'format'    => 'raw',
                                     'value'     =>  isset($model->sucursalEmisor->nombre) ?  Html::a($model->sucursalEmisor->nombre, ['/sucursales/sucursal/view', 'id' => $model->sucursalEmisor->id], ['class' => 'text-primary']) : '' ,
                                    ],
                                    'sucursalEmisor.encargadoSucursal.nombreCompleto',
                                    [
                                     'attribute' => 'Tipo de sucursal',
                                     'format'    => 'raw',
                                     'value'     =>  isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : '' ,
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h3 >Cliente Emisor</h3>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                     [
                                         'attribute' => 'Cliente Emisor',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->clienteEmisor->nombreCompleto) ?  Html::a($model->clienteEmisor->nombreCompleto, ['/crm/cliente/view', 'id' => $model->clienteEmisor->id], ['class' => 'text-primary']) : '' ,
                                     ],
                                     "clienteEmisor.telefono",

                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Tickets</h3>
                </div>
                <div class="ibox-content">
                    <div class="row">
                    <?php foreach ($model->tickets as $key => $item): ?>
                        <div class="col-sm-4">
                            <div class="panel panel-colorful panel-<?= Ticket::$alertStatusList[$item->status] ?> ">
                                <div class="ibox-title">
                                    <div class="panel-control">
                                        <span class="label label-<?= Ticket::$alertStatusList[$item->status]  ?>"><?= Ticket::$statusList[$item->status]  ?></span>
                                        <a href="<?= Url::to(['/operacion/ticket/view','id' => $item->id])  ?>" class="text-link"><i class="fa fa-eye fa-lg fa-fw"></i></a>
                                    </div>
                                    <h3 ><strong><?= $item->tipo->singular  ?></strong></h3>
                                </div>
                                <div class="ibox-content">
                                    <p><strong>Fecha: </strong><?= Esys::fecha_en_texto($item->created_at, true) ?></p>
                                    <p><strong>Descripción de ticket: </strong><?= $item->descripcion  ?></p>
                                    <hr>
                                    <p><strong>Descripción de seguimiento: </strong><?= $item->nota  ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Paquetes relacionados con el envio</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                    <th class="min-col text-center text-uppercase">BODEGA [DISTRIBUIDORA] </th>
                                    <th class="min-col text-center text-uppercase">Cliente R.</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>

                                    <th class="min-col text-center text-uppercase">N° Piezas</th>
                                    <th class="min-col text-center text-uppercase">Valor declarado</th>


                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Seguro</th>
                                    <th class="min-col text-center text-uppercase">Dirección de entrega</th>
                                    <th class="min-col text-center text-uppercase" colspan="2">Dirección de entrega</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>
                                    <th class="min-col text-center text-uppercase">Comentarios</th>
                                    <th class="min-col text-center text-uppercase">Acciones</th>
                                    <th class="min-col text-center text-uppercase">Movimiento</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->envioDetalles as $key => $item): ?>
                                    <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO || $item->status == EnvioDetalle::STATUS_SOLICITADO ): ?>
                                        <tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>" >
                                            <td>
                                                <?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?>

                                                <?php if (Yii::$app->user->can('admin')): ?>
                                                    <i class="fa fa-edit btn text-warning" data-target = "#modal-sucursal" data-toggle ="modal" onclick = "init_model_sucursal('<?= $item->id ?>')"></i>
                                                <?php endif ?>

                                            </td>
                                            <td><?= $item->bodega_descarga ? DescargaBodega::$descargaList[$item->bodega_descarga ]  : 'N/A' ?></td>

                                            <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
                                            <td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular : '' ?></td>
                                            <td><?= $item->producto->nombre ?></td>

                                            <td><?= $item->cantidad ?></td>
                                            <td><?= $item->valor_declarado ?></td>


                                            <td><?= $item->peso ?></td>
                                            <td><?= $item->seguro == 1 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
                                            <td><?= $item->is_reenvio == EnvioDetalle::REENVIO_ON ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
                                            <td colspan="2">
                                                <?php if ($item->is_reenvio == EnvioDetalle::REENVIO_ON ): ?>
                                                    <?= Html::button('<i class="fa fa-eye"></i>', [ 'class' =>  'btn btn-small btn-primary', 'style' => 'margin-top: 20px', 'data-target' => "#modal-show-reenvio", 'data-toggle' =>"modal", "onclick" => "init_reenvio_direccion($item->id)"]) ?>
                                                <?php endif ?>
                                                <?php /* ?>
                                                <?php if (isset($item->direccion)): ?>
                                                    Estado: <strong><small><?= isset($item->direccion->estado->singular) ? $item->direccion->estado->singular : 'N/A' ?></small></strong> /
                                                    Municipio: <strong><small><?= isset($item->direccion->municipio->singular)  ? $item->direccion->municipio->singular : 'N/A' ?></small></strong> /
                                                    Colonia: <strong><small><?= isset($item->direccion->esysDireccionCodigoPostal->colonia)  ? $item->direccion->esysDireccionCodigoPostal->colonia : 'N/A' ?></small></strong> /
                                                    Dirección: <strong><small><?= isset($item->direccion)  ? $item->direccion->direccion : 'N/A' ?></small></strong> /
                                                    Referencia: <strong><small><?= isset($item->direccion)  ? $item->direccion->referencia : 'N/A' ?></small></strong>
                                                <?php endif ?>
                                                */?>
                                            </td>
                                            <td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                            <td><?= $item->observaciones ?></td>
                                            <td>
                                                <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO): ?>
                                                    <button class='btn btn-warning btn-circle imprimir-etiqueta' type="button" data-id="<?= $item->id ?>" data-impresion = "<?= $item->getImpresionEtiqueta()  ?>" ><i class='fa fa-barcode'></i></button>
                                                <?php else: ?>
                                                    <button class='btn btn-dark btn-circle imprimir-etiqueta' disabled="true"  type="button" data-id="<?= $item->id ?>"  ><i class='fa fa-barcode'></i></button>
                                                <?php endif ?>
                                            </td>
                                            <td>
                                                <?= Html::button('<i class="fa fa-eye"></i>', [ 'class' =>  'btn btn-circle btn-small btn-success',  'data-target' => "#modal-show-movimiento", 'data-toggle' =>"modal", "onclick" => "init_reenvio($item->id)"]) ?>
                                            </td>
                                        </tr>
                                    <?php endif ?>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Información extra / Comentarios</h3>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'comentarios:ntext',
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-4 col-xs-12">
            <iframe width="100%" class="ibox" height="500px" src="<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>"></iframe>

            <div class="ibox">
                <?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>
            </div>
            <div class="ibox">
                <?= Html::a('Ticket Comprimido', false, ['class' => 'btn  btn-lg btn-block', 'id' => 'imprimir-ticket-comprimido','style'=>'    padding: 6%;'])?>
            </div>

            <div class="ibox">
                <?= Html::a('Imprimir Etiquetas', false, ['class' => 'btn  btn-lg btn-block btn-success', 'id' => 'imprimir-etiquetas-all','style'=>'    padding: 6%;'])?>
            </div>

            <div class="ibox">
                <?= Html::a('Negociación reembolso / Ticket', false, ['class' => 'btn btn-dark btn-lg btn-block', 'style'=>' padding: 6%;', 'data-target' => "#modal-create-ticket", 'data-toggle' =>"modal" ])?>
            </div>
            <?php if ($model->total < $cobroTotal  && Yii::$app->user->can('admin')): ?>
            <div class="ibox">
                <?= Html::a('<i class="fa fa-money"></i> AJUSTE MANUAL', false, ['class' => 'btn btn-dark btn-lg btn-block', 'style'=>' padding: 6%;', 'data-target' => "#modal-ajuste-cobrado", 'data-toggle' =>"modal" ])?>
            </div>
            <?php endif ?>
            <div class="panel <?= Envio::$statusAlertList[$model->status] ?>">
                <div class="ibox-title">
                    <h3 ><?= Envio::$statusList[$model->status] ?></h3>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Historial de cambios</h3>
                </div>
                <div class="ibox-content historial-cambios nano">
                    <div class="nano-content">
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Envio(), $model->id],
                        ], 50, true) ?>

                    </div>
                </div>
                <div class="ibox-footer">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                </div>
            </div>
            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h3 >Historial de cambios dirección  de Entrega</h3>
                    </div>
                    <div class="ibox-content historial-cambios nano">
                        <div class="nano-content">
                            <?php foreach ($model->envioDetalles as $key => $paquete): ?>
                                <div style="border-style: solid;border-style: solid;padding: 5%; margin: 5px;">
                                    <h3><?= $paquete->tracked  ?></h3>
                                    <?php if (isset($paquete->direccion->id)): ?>
                                        <?= EsysCambiosLog::getHtmlLog([
                                            [new EsysDireccion(), $paquete->direccion->id],
                                        ], 50, true) ?>
                                    <?php endif ?>
                                </div>

                            <?php endforeach ?>

                        </div>
                    </div>
                    <div class="panel-footer">
                        <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                    </div>
                </div>
            <?php endif ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h3 >Historial de impresión</h3>
                </div>
                <div class="ibox-content historial-cambios nano" style="overflow: auto;">
                    <div class="nano-content" >
                        <?php foreach ($model->envioDetalles as $key => $paquete): ?>
                            <ul class="mar-btn">
                            <?php foreach ($paquete->impresionEtiquetaAll as $key => $item): ?>
                                <div>
                                    <span class="pull-right">
                                        <p class="text-muted">
                                            <?= date("Y-m-d h:i a",$item->created_at)  ?>
                                        </p>
                                    </span>
                                </div>
                                <div class="mar-btm">
                                    <span><a class="text-primary" href="<?= Url::to(['/admin/user/view','id' => $item->user_id ]) ?>"><?= $item->user->nombre  ?></a></span>
                                </div>

                                <p><i>El </i><b><?= $paquete->tracked  ?></b> <i>imprimio etiquetas</i></p>

                            <?php endforeach ?>
                            </ul>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>

            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
                <?php if (isset($model->direccion)): ?>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h3 >Dirección de entrega</h3>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'direccion',
                                    'num_ext',
                                    'num_int',
                                    'esysDireccionCodigoPostal.colonia',
                                ]
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    "esysDireccionCodigoPostal.estado.singular",
                                    "esysDireccionCodigoPostal.municipio.singular",
                                ]
                            ]) ?>

                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'esysDireccionCodigoPostal.codigo_postal',
                                ]
                            ]) ?>
                        </div>
                    </div>
                  <?php endif ?>
            <?php endif ?>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<script>


    $('#imprimir-ticket').click(function(event){
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>",
        'imprimir',
        'width=600,height=500');
    });

    $('#imprimir-ticket-comprimido').click(function(event){
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-ticket-comprimido', 'id' => $model->id ])  ?>",
        'imprimir',
        'width=600,height=500');
    });


    $('#imprimir-etiquetas-all').click(function(event){
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-etiquetas-all', 'id' => $model->id ])  ?>",
        'imprimir',
        'width=600,height=500');
    });





    $('.imprimir-etiqueta').click(function(event){
        event.preventDefault();
        if ($(this).data('impresion') == 10) {
            window.open("<?= Url::to(['imprimir-etiqueta']) ?>?id=" +$(this).data('id'),
            'imprimir',
            'width=600,height=500');
        }else{
            $.niftyNoty({
                type: 'warning',
                icon : 'pli-cross icon-2x',
                message : 'Aviso, Solo puedes imprimir una vez las etiquedas, contacta al administrador.',
                container : 'floating',
                timer : 5000
            });
        }
    });

</script>
<?= $this->render('_modal_direccion_reenvio', [
    "model" => $model,
]) ?>

<?php if ($model->promocion_id && $model->promocion_detalle_id): ?>
    <?= $this->render('_modal_promocion_show', [
        "model" => $model,
    ]) ?>
<?php endif ?>

<?= $this->render('_modal_ajuste_manual', [
    "model" => $model,
    "cobroTotal" => isset($cobroTotal) && $cobroTotal ? $cobroTotal : 0,
]) ?>

<?= $this->render('_modal_sucursal',[
    "model" => $model,
]) ?>

<?= $this->render('_modal_movimiento_paquete') ?>

<?= $this->render('_modal_create_ticket', [
    'model' => $model,
]) ?>
