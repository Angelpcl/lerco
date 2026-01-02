<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\esys\EsysListaDesplegable;

$this->title = "Mapeo #" . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Mapeo', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['delete']?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar el mapeo ?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>
<div class="logistica-reparto-view">
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Mapeo</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nombre',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <?php foreach (EsysListaDesplegable::getItems('mapeo',true) as $key => $mapeo): ?>
                    <div class="col-lg-4 col-sm-4 col-xs-6">
                        <div class="panel">
                            <div class="panel-body historial-cambios nano" style="overflow: scroll;">
                                <div class="nano-content">
                                    <h3 class="panel-title"><?= $mapeo->singular ?> <strong>(<?= count($model->getPaqueteFila($mapeo->id,$model->id))  ?>)</strong>   </h3>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Fila</th>
                                                <th style="text-align: center;">Paquete #ID</th>
                                                <th style="text-align: center;">Tracked</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center;">
                                            <?php foreach ($model->getPaqueteFila($mapeo->id,$model->id) as $key => $paquete): ?>
                                                <tr>
                                                    <td><?=   $paquete->fila->singular ?></td>
                                                    <td><?=   $paquete->paquete_id ?></td>
                                                    <td><?=   $paquete->tracked ?></td>
                                                </tr>

                                            <?php endforeach ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>


        </div>
        <div class="col-md-3">
            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte de descarga de trailer',null,['id' => 'reporte_download_reparto','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%; display:none;' ])?>
            </div>
            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte de carga de unidades',null,['id' => 'reporte_download_carga_unidades','class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
            </div>
            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte de planeación',null,['id' => 'reporte_download_planeacion','class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
<script>
    var $reporte_download = $('#reporte_download_reparto'),
        $reporte_download_carga_unidades = $('#reporte_download_carga_unidades'),
        $reporte_download_planeacion = $('#reporte_download_planeacion'),
        set_reparto_id = <?= $model->id ?>;

$reporte_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-mapeo-ajax') ?>?mapeo_id='+set_reparto_id;
});

$reporte_download_carga_unidades.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-carga-unidades-ajax') ?>?mapeo_id='+set_reparto_id;
});

$reporte_download_planeacion.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-planeacion') ?>?mapeo_id='+set_reparto_id;
});

</script>
</div>
