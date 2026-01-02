<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysCambiosLog;
use app\models\promocion\PromocionComplemento;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\promocion\Promocion;
use app\models\envio\EnvioPromocion;
use app\models\producto\Producto;
/* @var $this yii\web\View */

$this->title =  '#'. $model->folio ;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>


<p>
    <?= Html::a('Nuevo envio', ['pre-envio'], ['class' => 'btn btn-success add'])  ?>
</p>


<div class="operaciones-envio-view">
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Envio</h3>
                </div>
                <div class="panel-body">
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
            <p>
                <?= Html::a('Terminar', ['/admin/user/logout'], ['class' => 'btn btn-block btn-lg btn-primary add','data' => [
                        //'confirm' => '¿Estás seguro de que deseas terminar de capturar envios?',
                        'method' => 'post',
                    ]  ])  ?>
            </p>
            <div class="row">
                <div class="col-sm-12" style="border-style: dotted;padding: 5%; text-align: center;">
                    <h4 class="title">Descarga y presenta tu ticket en tu sucursal mas cercana</h4>
                    <div class="panel">
                        <?= Html::a('Imprimir Ticket', ['imprimir-ticket', 'id' => $model->id ] , ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket','style'=>'    padding: 6%;'])?>
                    </div>
                    <?php /* ?>
                    <div class="panel <?= Envio::$statusAlertList[$model->status] ?>">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?= Envio::$statusList[$model->status] ?></h3>
                        </div>
                    </div>
                    */?>
                </div>
            </div>
            <?php if ($model->is_reenvio == Envio::REENVIO_ON): ?>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Aplico Reenvío</h3>
                </div>
                <div class="panel-body">
                    <div class="row totales cobros">

                        <div class="col-sm-4">
                            <span class="label">Costo de reenvío: </span>
                            <span class="total monto">$ <?= number_format($model->costo_reenvio, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>


            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Costos del envio</h3>
                </div>
                <div class="panel-body">

                    <div class="row totales cobros">
                        <div class="col-xs-6">
                            <span class="label">Subtotal</span>
                            <span class="neto monto">$ <?= number_format($model->subtotal, 2) ?> USD</span>
                        </div>
                        <div class="col-xs-6">
                            <span class="label">Impuestos</span>
                            <span class="impuestos monto">$ <?= number_format($model->impuesto, 2) ?> USD</span>
                        </div>
                        <div class="col-xs-6">
                            <span class="label">Seguro</span>
                            <span class="impuestos monto">$ <?= number_format($model->seguro_total, 2) ?> USD</span>
                        </div>
                        <div class="col-xs-6">
                            <span class="label">Total</span>
                            <span class="total monto">$ <?= number_format($model->total, 2) ?> USD</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Cliente Emisor</h3>
                        </div>
                        <div class="panel-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                     [
                                         'attribute' => 'Cliente Emisor',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->clienteEmisor->nombreCompleto) ?  Html::a($model->clienteEmisor->nombreCompleto, false, ['class' => 'text-primary']) : '' ,
                                     ],
                                     "clienteEmisor.telefono",

                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
			<div class="panel">
				<div class="panel-heading">
                    <h3 class="panel-title">Paquetes relacionados con el envio</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">
                                    <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                    <th class="min-col text-center text-uppercase">Cliente R.</th>
                                    <th class="min-col text-center text-uppercase">Categoria</th>
                                    <th class="min-col text-center text-uppercase">Producto</th>
                                    <th class="min-col text-center text-uppercase">Tipo</th>
                                    <th class="min-col text-center text-uppercase">N° Piezas</th>
                                    <th class="min-col text-center text-uppercase">Valor declarado</th>
                                    <th class="min-col text-center text-uppercase">Valoración del paquete</th>
                                    <th class="min-col text-center text-uppercase">Impuesto</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Seguro</th>
                                    <th class="min-col text-center text-uppercase">Reenvio</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>
                                    <th class="min-col text-center text-uppercase">Comentarios</th>

                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
    							<?php foreach ($model->envioDetalles as $key => $item): ?>
    								<tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>">
    									<td><?= Html::a($item->sucursalReceptor->nombre, false, ['class' => 'text-primary']) ?></td>
                                        <td><?=  Html::a($item->clienteReceptor->nombreCompleto, false, ['class' => 'text-primary']) ?></td>
                                        <td><?= $item->categoria->singular ?></td>
    									<td><?= $item->producto->nombre ?></td>
                                        <td><?= Producto::$tipoList[$item->producto_tipo]   ?></td>
    									<td><?= $item->cantidad ?></td>
    									<td><?= $item->valor_declarado ?></td>
                                        <td><?= $item->valoracion_paquete ?></td>
    									<td><?= $item->impuesto ?></td>
    									<td><?= $item->peso ?></td>
    									<td><?= $item->seguro == 1 ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
                                        <td><?= $item->is_reenvio == EnvioDetalle::REENVIO_ON ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>" ?></td>
    									<td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                        <td><?= $item->observaciones ?></td>

    								</tr>
    							<?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información extra / Comentarios</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'comentarios:ntext',
                        ]
                    ]) ?>
                </div>
            </div>
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

	$('.imprimir-etiqueta').click(function(event){
		event.preventDefault();

 		window.open("<?= Url::to(['imprimir-etiqueta']) ?>?id=" +$(this).data('id'),
        'imprimir',
        'width=600,height=500');
	});


</script>
