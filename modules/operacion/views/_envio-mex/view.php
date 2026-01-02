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


$this->title =  '#'. $model->folio ;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>


<p>
    <?= $can['update_mex'] && $model->status != Envio::STATUS_ENTREGADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['cancel_mex'] && $model->status != Envio::STATUS_ENTREGADO && $model->status != Envio::STATUS_CANCELADO ?
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
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información Envio</h5>
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
                        ],
                    ]) ?>
                </div>
            </div>
            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Aplico Reenvío</h5>
                </div>
                <div class="ibox-content">
                    <div class="row totales cobros">

                        <div class="col-sm-4">
                            <span class="label">Costo de reenvío: </span>
                            <span class="total monto">$ <?= number_format($model->costo_reenvio, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>

            <?php if ($model->is_descuento_manual == Envio::DESCUENTO_ON): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Aplico un descuento Manual</h5>
                </div>
                <div class="ibox-content">
                    <div class="row totales cobros">
                         <div class="col-sm-4">
                            <span class="label">Precio originar: </span>
                            <span class="total monto" style="    text-decoration: line-through;text-decoration-style: double;">$ <?= number_format($model->descuento_manual + $model->total, 2) ?> USD</span>
                        </div>
                        <div class="col-sm-4">
                            <span class="label">Se aplico un descuento de: </span>
                            <span class="total monto">$ <?= number_format($model->descuento_manual, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>
            <?php if ($model->status == Envio::STATUS_EMPAQUETADO || $model->status == Envio::STATUS_NOAUTORIZADO): ?>
            <?php
                $precioLibra = 0;
                $subtotal    = 0;
                $total       = 0;
                $peso_minimo = 4;

                foreach (Envio::$precioMexList as $key => $precio) {
                    if($model->peso_total >=floatval($precio["rango_ini"]) && $model->peso_total <= floatval($precio["rango_fin"]) )
                        $precioLibra = EsysSetting::getPrecioMex($key);
                }
                if ($precioLibra == 0  && $model->peso_total >= floatval(Envio::$precioMexList["PRECION_MEX_5"]["rango_ini"]))
                   $precioLibra = EsysSetting::getPrecioMex("PRECION_MEX_5");

                if ($precioLibra == 0  && $model->peso_total <= floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]))
                   $precioLibra = EsysSetting::getPrecioMex("PRECION_MEX_1");

                $subtotal = (floatval($model->peso_total) <= floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]) ? floatval(Envio::$precioMexList["PRECION_MEX_1"]["rango_ini"]) : floatval($model->peso_total) ) * floatval($precioLibra);

                $total = floatval($subtotal) + floatval($model->impuesto) + floatval($model->seguro_total);
             ?>

            <?php endif ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Peso total / Precio de libra</h5>
                </div>
                <div class="ibox-content">
                    <div class="row text-center">
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"><?= number_format($model->peso_mex_sin_empaque, 2) ?> Lbs</h2>
                            <strong>PESO (RECOLECCIÓN)</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"><?= number_format($model->peso_mex_con_empaque, 2) ?> Lbs</h2>
                            <strong>PESO (EMPAQUETADO)</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label"><?= number_format($model->peso_total, 2) ?> Lbs</h2>
                            <strong>PESO FINAL</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label">$ <?= number_format(isset($precioLibra) ? $precioLibra : $model->precio_libra_actual , 2) ?> USD</h2>
                            <strong>PRECIO DE LIBRA OTORGADA</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Costos del envio</h5>
                </div>
                <div class="ibox-content">

                    <div class="row text-center">
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label">$ <?= number_format(isset($subtotal) ? $subtotal : $model->subtotal, 2) ?> USD</h2>
                            <strong>SUBTOTAL</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label">$ <?= number_format($model->impuesto, 2) ?> USD</h2>
                            <strong>COSTO EXTRA</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label">$ <?= number_format($model->seguro_total, 2) ?> USD</h2>
                            <strong>SEGURO</strong>
                        </div>
                        <div class="col-sm-3">
                            <h2 class="product-main-price"  id="envio-impuesto-label">$ <?= number_format(isset($total) ? $total: $model->total, 2) ?> USD</h2>
                            <strong>TOTAL</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Cobros realizado</h5>
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
                        <tbody  style="text-align: center;">
                            <tbody>
                                <?php $cobroTotal = 0; ?>
                                <?php foreach ($model->cobroRembolsoEnvios as $key => $item): ?>
                                    <tr class="<?= $item->tipo ==  CobroRembolsoEnvio::TIPO_DEVOLUCION ? 'danger': ''  ?>">
                                        <td class="text-center"><?= CobroRembolsoEnvio::$tipoList[$item->tipo] ?></td>
                                        <td class="text-center"><?= CobroRembolsoEnvio::$servicioList[$item->metodo_pago] ?></td>
                                        <?php if ($item->tipo == CobroRembolsoEnvio::TIPO_DEVOLUCION ): ?>
                                            <td class="text-center"> - <?= number_format($item->cantidad,2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal - $item->cantidad; ?>
                                        <?php else: ?>
                                            <td class="text-center"><?= number_format($item->cantidad,2) ?> USD</td>
                                            <?php $cobroTotal =  $cobroTotal + $item->cantidad; ?>
                                        <?php endif ?>
                                        <td class="text-center"><?= $item->nota ?> </td>
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
                            <h5 >Sucursal Emisor</h5>
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
                            <h5 >Cliente Emisor</h5>
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
                    <h5 >Tickets</h5>
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
                                    <h5 ><strong><?= $item->tipo->singular  ?></strong></h5>
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
                    <h5 >Paquetes relacionados con el envio</h5>
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
                                    <th class="min-col text-center text-uppercase">Cantidad de Elementos</th>
                                    <th class="min-col text-center text-uppercase">Costo extra</th>
                                    <th class="min-col text-center text-uppercase">Valor asegurado</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Seguro</th>
                                    <th class="min-col text-center text-uppercase">Costo del seguro</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>
                                    <th class="min-col text-center text-uppercase">Comentarios</th>
                                    <th class="min-col text-center text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
    							<?php foreach ($model->envioDetalles as $key => $item): ?>
                                    <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO || $item->status == EnvioDetalle::STATUS_SOLICITADO ): ?>
                                    <tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>">
                                        <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
    									<td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular : '' ?></td>
    									<td><?= $item->producto->nombre ?></td>
    									<td><?= $item->cantidad ?></td>
                                        <td><?= $item->cantidad_piezas ?></td>
    									<td><?= $item->impuesto ?> USD</td>
                                        <td><?= $item->valor_declarado ? $item->valor_declarado : 0  ?> USD</td>
                                        <td><?= $item->peso ?> Lbs</td>
                                        <td><?= $item->seguro == 1 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
                                        <td><?= $item->costo_seguro  ? $item->costo_seguro : 0 ?> USD</td>
    									<td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                        <td><?= $item->observaciones ?></td>
    									<td>
                                            <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO || $item->status == EnvioDetalle::STATUS_SOLICITADO ): ?>
                                                    <button class='btn btn-warning btn-circle imprimir-etiqueta'  type="button" data-id="<?= $item->id ?>"  ><i class='fa fa-barcode'></i></button>
                                                <?php else: ?>
                                                    <button class='btn btn-dark btn-circle imprimir-etiqueta' disabled="true"  type="button" data-id="<?= $item->id ?>"  ><i class='fa fa-barcode'></i></button>
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
                    <h5 >Información extra / Comentarios</h5>
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
        <div class="col-sm-3">
            <?php if ($can['seguimiento']): ?>
                <?php if ($model->status == Envio::STATUS_EMPAQUETADO || $model->status == Envio::STATUS_NOAUTORIZADO): ?>
                    <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>
                        <?= Html::hiddenInput('subtotal', $subtotal) ?>
                        <?= Html::hiddenInput('total', $total) ?>
                        <?= Html::hiddenInput('precioLibra', $precioLibra) ?>
                        <div class="ibox">
                            <?= Html::submitButton('Autorizar Envío',  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas autorizar el envio?'] ])?>
                        </div>
                    <?php ActiveForm::end(); ?>
                <?php endif ?>
                <?php if ($model->status == Envio::STATUS_EMPAQUETADO || $model->status == Envio::STATUS_NOAUTORIZADO): ?>
                        <div class="ibox">
                            <?= Html::submitButton('Pendiende Envío',  ['class' => 'btn btn-dark btn-lg btn-block', 'style'=>'padding: 6%;',  'data-target' => "#modal-contacto-envio", 'data-toggle' =>"modal"  ] )?>
                        </div>
                <?php endif ?>

                <?php if ($model->status == Envio::STATUS_SOLICITADO): ?>
                     <?php $form = ActiveForm::begin(['id' => 'form-envios']) ?>
                        <?= Html::hiddenInput('status', Envio::STATUS_PREAUTORIZADO) ?>
                        <div class="ibox">
                            <?= Html::submitButton('Pre autorizar',  ['class' => 'btn btn-dark btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas pre autorizar el envio?']    ] )?>
                        </div>
                        <?php ActiveForm::end(); ?>
                <?php endif ?>

            <?php endif ?>
            <div class="ibox">
            	<?= Html::a('Imprimir Ticket', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>
            </div>
            <div class="ibox">
                <?= Html::a('Negociación reembolso / Ticket', false, ['class' => 'btn btn-dark btn-lg btn-block', 'style'=>' padding: 6%;', 'data-target' => "#modal-create-ticket", 'data-toggle' =>"modal" ])?>
            </div>
            <div class="panel <?= Envio::$statusAlertList[$model->status] ?>">
                <div class="ibox-title">
                    <h5 ><?= Envio::$statusList[$model->status] ?></h5>
                </div>
            </div>
        	<div class="ibox">
                <div class="ibox-title">
                    <h5 >Historial de cambios</h5>
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
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Historial de seguimiento</h5>
                </div>
                <div class="ibox-content">
                    <?php foreach ($model->historialCall as $key => $history): ?>
                    <li class="mar-btn" style="list-style:none;">
                        <div>
                            <span class="pull-right">
                                <p class="text-muted">hace <small title="<?= Esys::fecha_en_texto($history->created_at) ?>"><?= Esys::hace_tiempo_en_texto($history->created_at) ?></small></p>
                            </span>
                            <span><?= html::a(
                                $history->createdBy->nombreCompleto . ' [' . $history->created_by . ']',
                                ['/admin/user/view', 'id' => $history->created_by ],
                                ['class' => 'text-primary']
                            ) ?></span>
                        </div>
                        <div class="mar-btm">
                             Tipo:  &nbsp; <b> <?= $history->tipoRespuesta->singular ?></b> &nbsp; comentario:  &nbsp; <b><?= $history->comentario ?></b>
                        </div>
                    </li>
                    <?php endforeach ?>
                </div>
            </div>
            <?php if ($model->is_reenvio == Envio::REENVIO_ON && $model->direccion): ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Dirección de reenvío</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'direccion',
                            'num_ext',
                            'num_int',
                            'colonia_usa',
                        ]
                    ]) ?>
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            "estado_usa",
                            "municipio_usa",
                        ]
                    ]) ?>

                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'codigo_postal_usa',
                        ]
                    ]) ?>

                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'referencia:text',
                        ]
                    ]) ?>
                </div>
            </div>
            <?php endif ?>
        	<?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
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
	$('#imprimir-ticket').click(function(event){
		event.preventDefault();
 		window.open("<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>",
        'imprimir',
        'width=600,height=500');
	});

	$('.imprimir-etiqueta').click(function(event){
		event.preventDefault();

 		window.open("<?= Url::to(['imprimir-etiqueta']) ?>?id=" +$(this).data('id'),
        'imprimir',
        'width=600,height=500');
	});



</script>
