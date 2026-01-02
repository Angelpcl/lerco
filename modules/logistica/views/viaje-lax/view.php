<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\viaje\Viaje;
use app\models\envio\EnvioDetalle;

$this->title = "Fecha de salida : " . Esys::fecha_en_texto($model->fecha_salida);

$this->params['breadcrumbs'][] = ['label' => 'Viajes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['update'] && $model->status != Viaje::STATUS_TERMINADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['delete'] && $model->status != Viaje::STATUS_TERMINADO ?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta Viaje?',
                'method' => 'post',
            ],
    ]): '' ?>
</p>
<div class="logistica-viaje-lax-view">
    <div class="panel panel-mint">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Viaje::$statusList[$model->status] ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Viaje</h3>
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
             <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Unidad de trailer / Chofer</h3>
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
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Paquetes relacionados al Viaje Lax </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Categoria</th>
                                <th style="text-align: center;">Tracked</th>
                                <th style="text-align: center;">Nombre</th>
                                <th style="text-align: center;">Peso</th>
                                <th style="text-align: center;">Peso MX</th>
                                <th style="text-align: center;">Estatus</th>
                                <th style="text-align: center;">Accion</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->viajeDetalles as $key => $item): ?>
                                <tr>
                                    <td><?= isset($item->envioDetalleLaxTierra->producto->categoria->singular) ? $item->envioDetalleLaxTierra->producto->categoria->singular : '' ?></td>
                                    <td><?= $item->tracked ?></td>
                                    <td><?= $item->envioDetalleLaxTierra->producto->nombre ?></td>
                                    <td><?= $item->envioDetalleLaxTierra->peso ?> Lbs</td>
                                    <td><?= number_format($item->peso_mx) ?> Lbs</td>
                                    <td><?= EnvioDetalle::$statusList[$item->envioDetalleLaxTierra->status] ?></td>
                                    <td>
                                        <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                                            <?=  Html::a("<i class='fa fa-times'></i>", ['producto-remove','viaje_id' => $model->id, 'paquete_id' => $item->envioDetalleLaxTierra->id, "tipo" => $item->tipo ], [
                                                'class' => 'btn btn-dark btn-circle ',
                                                'data' => [
                                                    'confirm' => '¿Estás seguro de que deseas remover este paquete?',
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
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
                            'nota:ntext',

                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
              <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                    <div class="panel">
                        <?= Html::a('Cancelar Viaje  Lax',['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CANCEL ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas cancelar viaje?'] ])?>
                    </div>
                    <div class="panel">
                        <?= Html::a('Enviar Viaje Lax', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CERRADO ], ['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Enviar/Cerrar el viaje?'] ])?>
                    </div>
                <?php endif ?>
                <?php if ($model->status == Viaje::STATUS_CERRADO): ?>
                    <div class="panel">
                        <?= Html::a('Habilitar Viaje Lax', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_ACTIVE ], ['class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Habilitar el viaje?'] ])?>
                    </div>
                    <div class="panel">
                        <?= Html::a('Terminar / Concluir Viaje Lax',['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_TERMINADO ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Terminar/Concluir el viaje?'] ])?>
                    </div>



                <?php endif ?>
                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte',null,['id' => 'reporte_download_viaje','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

                <div class="panel">
                    <?= Html::a('<i class="fa fa-print mar-rgt-5px"></i> Imprimir reetiquetas',null,['id' => 'imprimir_download_reetiquetas','class' => 'btn btn-white btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>
                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar Julio',null,['id' => 'reporte_download_julio','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<script>
    var $reporte_download = $('#reporte_download_viaje'),
        $reporte_download_julio = $('#reporte_download_julio'),
        $imprimir_download_reetiquetas = $('#imprimir_download_reetiquetas'),
        set_viaje_id      = <?= $model->id ?>;

$reporte_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-viaje-ajax') ?>?viaje_id='+set_viaje_id;
});

$reporte_download_julio.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-viaje-julio-ajax') ?>?viaje_id='+set_viaje_id;
});

$imprimir_download_reetiquetas.click(function(event){
    event.preventDefault();
    window.open('<?= Url::to(['imprimir-reetiquetas-pdf']) ?>?viaje_id='+set_viaje_id,
        'imprimir',
        'width=600,height=500');
});

</script>


