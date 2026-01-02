<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\viaje\Viaje;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\caja\CajaMex;
$this->title = "Fecha de salida : " . Esys::fecha_en_texto($model->fecha_salida);

$this->params['breadcrumbs'][] = ['label' => 'Viajes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
//print_r($model->viajeDetalles);die;
?>

<p>
    <?= $can['update'] &&  $model->status != Viaje::STATUS_TERMINADO?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['delete'] &&  $model->status != Viaje::STATUS_TERMINADO?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta Viaje?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>
<div class="logistica-viaje-mex-view">
    <div class="ibox panel-mint">
        <div class="ibox-title">
            <h5 ><?= Viaje::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información Viaje</h5>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'fecha_salida:date',
                        ],
                    ]) ?>
                </div>
            </div>
             <div class="ibox">
                <div class="ibox-title">
                    <h5 >Unidad de trailer / Chofer</h5>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nombre_chofer',
                            'placas',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel panel-primary panel-colorful">
                <div class="pad-all text-center">
                    <span class="text-3x text-thin"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?= count($model->viajeDetalles) ?></font></font></span>
                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"># Paquetes</font></font></p>
                    <i class="demo-pli-shopping-bag icon-lg"></i>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Paquetes relacionados al Viaje Mex </h5>
                </div>
                <div class="panel-body">
                     <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Categoria</th>
                                <th style="text-align: center;">Tracked</th>
                                <th style="text-align: center;">Nombre</th>
                                <th style="text-align: center;">Peso</th>
                                <th style="text-align: center;">Estatus</th>
                                <th style="text-align: center;">Accion</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->viajeDetalles as $key => $item): ?>
                                <?php if ($item->tipo == MovimientoPaquete::TIPO_PAQUETE): ?>
                                    <tr>
                                        <td><?= isset($item->envioDetalleMex->producto->categoria->singular) ? $item->envioDetalleMex->producto->categoria->singular : '' ?></td>
                                        <td><?=   Html::a($item->tracked, [ '/operacion/envio-mex/view' ,'id' => $item->envioDetalleMex->envio_id ],["class" => "text-primary"]) ?></td>
                                        <td><?= $item->envioDetalleMex->producto->nombre ?></td>
                                        <td><?= $item->envioDetalleMex->peso ?> Lbs</td>
                                        <td><?= EnvioDetalle::$statusList[$item->envioDetalleMex->status] ?></td>
                                        <td>
                                            <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                                                <?=  Html::a("<i class='fa fa-times'></i>", ['producto-remove','viaje_id' => $model->id, 'paquete_id' => $item->envioDetalleMex->id, "tipo" => $item->tipo ], [
                                                    'class' => 'btn btn-dark btn-circle ',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de que deseas remover este paquete?',
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Cajas relacionados al Viaje Mex </h5>
                </div>
                <div class="panel-body">
                     <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Categoria</th>
                                <th style="text-align: center;">Folio</th>
                                <th style="text-align: center;">Nombre</th>
                                <th style="text-align: center;">Estatus</th>
                                <th style="text-align: center;">Accion</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->viajeDetalles as $key => $item): ?>
                                <?php if ($item->tipo == MovimientoPaquete::TIPO_CAJA): ?>
                                    <tr>
                                        <td><?= $item->cajaMex->categoria->singular ?></td>
                                        <td><?= Html::a($item->tracked, [ '/operacion/caja/view' ,'id' => $item->cajaMex->id ],["class" => "text-primary"]) ?></td>
                                        <td><?= $item->cajaMex->nombre ?></td>
                                        <td><?= CajaMex::$statusList[$item->cajaMex->status] ?></td>
                                        <td>
                                            <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                                                <?=  Html::a("<i class='fa fa-times'></i>", ['producto-remove','viaje_id' => $model->id, 'paquete_id' => $item->cajaMex->id, "tipo" => $item->tipo ], [
                                                    'class' => 'btn btn-dark btn-circle ',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de que deseas remover esta Caja?',
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'nota:ntext',

                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
              <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                    <div class="ibox">
                        <?= Html::a('Cancelar Viaje  Mex',['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CANCEL ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas cancelar viaje?'] ])?>
                    </div>
                    <div class="ibox">
                        <?= Html::a('Enviar Viaje Mex', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CERRADO ], ['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Enviar/Cerrar el viaje?'] ])?>
                    </div>
                <?php endif ?>
                <?php if ($model->status == Viaje::STATUS_CERRADO): ?>
                    <div class="ibox">
                        <?= Html::a('Habilitar Viaje Mex', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_ACTIVE ], ['class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Habilitar el viaje?'] ])?>
                    </div>
                    <div class="ibox">
                        <?= Html::a('Terminar / Concluir Viaje',['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_TERMINADO ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Terminar/Concluir el viaje?'] ])?>
                    </div>
                <?php endif ?>
                <div class="ibox">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte',null,['id' => 'reporte_download_viaje','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


<script>
    var $reporte_download = $('#reporte_download_viaje'),
        set_viaje_id      = <?= $model->id ?>;

$reporte_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-viaje-ajax') ?>?viaje_id='+set_viaje_id;
});
</script>


