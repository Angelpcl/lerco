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
/* @var $this yii\web\View */

$this->title =  '#'. $model->folio ;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['empaquetado'] && $model->status == Envio::STATUS_RECOLECTADO ?
        Html::a('Empaquetar', ['empaquetado-update', 'id' => $model->id], ['class' => 'btn btn-warning']): '' ?>

<p>
    <?= $can['empaquetado'] && $model->status == Envio::STATUS_PREAUTORIZADO ?
        Html::a('Recolectar', ['empaquetado-update', 'id' => $model->id], ['class' => 'btn btn-dark']): '' ?>

</p>
<div class="operaciones-envio-view">
    <div class="ibox <?= Envio::$statusAlertList[$model->status] ?>">
        <div class="ibox-title">
            <h5 ><?= Envio::$statusList[$model->status] ?></h5>
        </div>
    </div>
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
                             'peso_total',
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
                                    <th class="min-col text-center text-uppercase">N° Elementos</th>
                                    <th class="min-col text-center text-uppercase">Cantidad de piezas</th>
                                    <th class="min-col text-center text-uppercase">Costo extra</th>
                                    <th class="min-col text-center text-uppercase">Peso</th>
                                    <th class="min-col text-center text-uppercase">Estatus</th>
                                    <th class="min-col text-center text-uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
    							<?php foreach ($model->envioDetalles as $key => $item): ?>
    								<tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>">
                                        <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                        <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
    									<td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular : ''?></td>
    									<td><?= $item->producto->nombre ?></td>
    									<td><?= $item->cantidad ?></td>
                                        <td><?= $item->cantidad_piezas ?></td>
    									<td><?= $item->impuesto ?></td>
                                        <td><?= $item->peso ?> Lbs</td>
    									<td><?= EnvioDetalle::$statusList[$item->status] ?></td>

    									<td>
                                            <?php if ($item->status == EnvioDetalle::STATUS_HABILITADO): ?>
                                                    <button class='btn btn-warning btn-circle imprimir-etiqueta'  type="button" data-id="<?= $item->id ?>"  ><i class='fa fa-barcode'></i></button>
                                                <?php else: ?>
                                                    <button class='btn btn-dark btn-circle imprimir-etiqueta' disabled="true"  type="button" data-id="<?= $item->id ?>"  ><i class='fa fa-barcode'></i></button>
                                            <?php endif ?>
                                        </td>

    								</tr>
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

	$('.imprimir-etiqueta').click(function(event){
		event.preventDefault();

 		window.open("<?= Url::to(['imprimir-etiqueta']) ?>?id=" +$(this).data('id'),
        'imprimir',
        'width=600,height=500');
	});
</script>
