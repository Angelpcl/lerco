<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysCambiosLog;
use app\models\promocion\PromocionComplemento;
use app\models\esys\EsysSetting;
use app\models\Esys;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\ticket\Ticket;

/* @var $this yii\web\View */


$this->title =  '#' . $model->folio;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>


<p>
    <?= $can['update_mex'] && $model->status != Envio::STATUS_ENTREGADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>

    <?= $can['cancel_mex'] && $model->status != Envio::STATUS_ENTREGADO && $model->status != Envio::STATUS_CANCELADO ?
        Html::a('Cancelar', ['cancel', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas cancelar este envio?',
                'method' => 'post',
            ],
        ]) : '' ?>

    <?= Html::a('Agregar Comentarios / Nota', false, ['class' => 'btn btn-dark ', 'style' => ' padding: 1%;', 'data-target' => "#modal-edit-envio", 'data-toggle' => "modal"]) ?>

</p>
<div class="operaciones-envio-view">

    <div class="row">
        <div class="col-md-9 col-sm-12 ">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Información Envio</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'TIPO DE ENVIO',
                                'format'    => 'raw',
                                'value'     =>  isset($model->tipo_envio) ?  Envio::$origenList[$model->origen] . " - [" . Envio::$tipoList[$model->tipo_envio] . "]" : '',
                            ],
                            [
                                'attribute' => 'ORIGEN / DESTINO',
                                'format'    => 'raw',
                                'value'     =>  isset($model->sucursalEmisor->nombre)  && isset($model->envioDetalles[0]->sucursalReceptor->nombre) ? $model->sucursalEmisor->nombre  . " - " . $model->envioDetalles[0]->sucursalReceptor->nombre : '',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <?php if ($model->is_descuento_manual == Envio::DESCUENTO_ON): ?>
                <div class="alert alert-warning">
                    <strong>SE REALIZO UN AJUSTE DIRECTAMENTE AL TOTAL DEL ENVIO</strong>
                </div>
            <?php endif ?>

            <div class="ibox">
                <div class="ibox-title">
                    <h5>Peso total / Precio de libra</h5>
                </div>
                <div class="ibox-content">
                    <div class="row totales cobros text-center">
                        <div class="col-sm-6">
                            <span class="label" style="background-color: #fff">Peso MX</span>
                            <span class="neto monto"> <?= number_format($model->peso_mex_con_empaque, 2) ?> Lbs</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="label" style="background-color: #fff">Peso Final</span>
                            <span class="neto monto"> <?= number_format($model->peso_total, 2) ?> Lbs</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (Yii::$app->user->can('admin')): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Costos del envio</h5>
                    </div>
                    <div class="ibox-content">

                        <div class="row totales cobros text-center">
                            <div class="col-sm-4">
                                <span class="label" style="background-color: #fff">Subtotal</span>
                                <span class="neto monto">$ <?= number_format(isset($subtotal) ? $subtotal : $model->subtotal, 2) ?> USD</span>
                            </div>
                            <div class="col-sm-4">
                                <span class="label" style="background-color: #fff">Costo extra</span>
                                <span class="impuestos monto">$ <?= number_format($model->impuesto, 2) ?> USD</span>
                            </div>
                            <div class="col-sm-4">
                                <span class="label" style="background-color: #fff">Total</span>
                                <span class="total monto">$ <?= number_format(isset($total) ? $total : $model->total, 2) ?> USD</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Cobros realizado</h5>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Tipo</th>
                                    <th style="text-align: center;">Metodo de pago</th>
                                    <th style="text-align: center;">Cobro</th>
                                    <th style="text-align: center;">Nota</th>

                                </tr>
                            </thead>
                            <tbody style="text-align: center;">
                            <tbody>
                                <?php $cobroTotal = 0; ?>
                                <?php foreach ($model->cobroRembolsoEnvios as $key => $item): ?>
                                    <tr class="<?= $item->tipo ==  CobroRembolsoEnvio::TIPO_DEVOLUCION ? 'danger' : ''  ?>">
                                        <td class="text-center"><?= CobroRembolsoEnvio::$tipoList[$item->tipo] ?></td>
                                        <td class="text-center"><?= CobroRembolsoEnvio::$servicioList[$item->metodo_pago] ?></td>
                                        <?php if ($item->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION): ?>
                                            <td class="text-center"> - <?= number_format($item->cantidad, 2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal - $item->cantidad; ?>
                                        <?php else: ?>
                                            <td class="text-center"><?= number_format($item->cantidad, 2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal + $item->cantidad; ?>
                                        <?php endif ?>
                                        <td class="text-center"><?= $item->nota ?> </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">Total</td>
                                    <td style="font-size: 17px"><?= number_format($model->total, 2) ?> USD</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">Efectivo (Cobro)</td>
                                    <td style="font-size: 17px"><?= number_format($cobroTotal, 2) ?> USD</td>
                                </tr>
                            </tfoot>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>

            <div class="row">
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Sucursal Emisor</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'attribute' => 'Sucursal Emisor',
                                        'format'    => 'raw',
                                        'value'     =>  isset($model->sucursalEmisor->nombre) ?  Html::a($model->sucursalEmisor->nombre, ['/sucursales/sucursal/view', 'id' => $model->sucursalEmisor->id], ['class' => 'text-primary']) : '',
                                    ],
                                    'sucursalEmisor.encargadoSucursal.nombreCompleto',
                                    [
                                        'attribute' => 'Tipo de sucursal',
                                        'format'    => 'raw',
                                        'value'     =>  isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : '',
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Cliente Emisor</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    [
                                        'attribute' => 'Cliente Emisor',
                                        'format'    => 'raw',
                                        'value'     =>  isset($model->clienteEmisor->nombreCompleto) ?  Html::a($model->clienteEmisor->nombreCompleto, ['/crm/cliente/view', 'id' => $model->clienteEmisor->id], ['class' => 'text-primary']) : '',
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
                    <h5>Paquetes relacionados con el envio</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                    <th class="min-col text-center text-uppercase">Cliente R.</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>
                                    <th class="min-col text-center text-uppercase">N° de piezas</th>

                                    <th class="min-col text-center text-uppercase">Costo extra</th>
                                    <th class="min-col text-center text-uppercase">Valor asegurado</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>


                                    <th class="min-col text-center text-uppercase">Estatus</th>
                                    <th class="min-col text-center text-uppercase">Comentarios</th>
                                    <th class="min-col text-center text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody style="text-align: center;">
                                <?php foreach ($model->envioDetalles as $key => $item): ?>
                                    <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO || $item->status == EnvioDetalle::STATUS_SOLICITADO): ?>
                                        <tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>">
                                            <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                            <td><?= Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
                                            <td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular : '' ?></td>
                                            <td><?= $item->producto->nombre ?></td>
                                            <td><?= $item->cantidad ?></td>

                                            <td><?= $item->impuesto ?> USD</td>
                                            <td><?= $item->valor_declarado ? $item->valor_declarado : 0  ?> USD</td>
                                            <td><?= $item->peso ?> Lbs</td>
                                            <td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                            <td><?= $item->observaciones ?></td>
                                            <td>
                                                <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO || $item->status == EnvioDetalle::STATUS_SOLICITADO): ?>
                                                    <button class='btn btn-warning btn-circle imprimir-etiqueta' type="button" data-id="<?= $item->id ?>"><i class='fa fa-barcode'></i></button>
                                                <?php else: ?>
                                                    <button class='btn btn-dark btn-circle imprimir-etiqueta' disabled="true" type="button" data-id="<?= $item->id ?>"><i class='fa fa-barcode'></i></button>
                                                <?php endif ?>
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
                    <h5>Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'comentarios:ntext',
                            'informacion_extra:ntext',
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-12">
            <div class="ibox">
                <?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket', 'style' => '    padding: 6%;']) ?>
            </div>
            <div class="ibox">
                <?= Html::a('Imprimir Etiquetas', false, ['class' => 'btn  btn-lg btn-block btn-success', 'id' => 'imprimir-etiquetas-all', 'style' => '    padding: 6%;']) ?>
            </div>


            <div class="panel <?= Envio::$statusAlertList[$model->status] ?>">
                <div class="ibox-title">
                    <h5><?= Envio::$statusList[$model->status] ?></h5>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Historial de cambios</h5>
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
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


<div class="fade modal inmodal " id="modal-edit-envio" tabindex="-1" role="dialog" aria-labelledby="modal-show-label">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> ENVIO - Comentarios / Nota </h4>
            </div>
            <!--Modal body-->
            <?php $form = ActiveForm::begin(['id' => 'form-comentario', 'action' => 'form-comentario-extra']) ?>
            <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox">
                            <div class="ibox-content">
                                <?= $form->field($model, 'informacion_extra')->textarea(['rows' => 6, 'disabled' => $model->ticket->id ? true :  false]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                <?= Html::submitButton('Guardar cambios', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?= $this->render('_modal_contacto_envio', [
    'model' => $model,
]) ?>

<?= $this->render('_modal_create_ticket', [
    'model' => $model,
]) ?>


<script>
    $('#imprimir-ticket').click(function(event) {
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-ticket', 'id' => $model->id])  ?>",
            'imprimir',
            'width=600,height=500');
    });

    $('.imprimir-etiqueta').click(function(event) {
        event.preventDefault();

        window.open("<?= Url::to(['imprimir-etiqueta']) ?>?id=" + $(this).data('id'),
            'imprimir',
            'width=600,height=500');
    });

    $('#imprimir-etiquetas-all').click(function(event){
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-etiquetas-all', 'id' => $model->id ])  ?>",
        'imprimir',
        'width=600,height=500');
    });
</script>