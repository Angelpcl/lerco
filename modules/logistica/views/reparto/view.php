<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\reparto\Reparto;
use app\models\ruta\Ruta;


$this->title = "Reparto # : " . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Repartos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['update'] && $model->status != Reparto::STATUS_TERMINADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>
    <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
        <?= $can['update'] || $can['create'] ?
            Html::button('Agregar paquete', ['class' => 'btn btn-success',  "data-target" => "#modal-add-paquete" ,"data-toggle"=>"modal"]) : ''?>
    <?php endif ?>

    <?= $can['delete'] && $model->status != Reparto::STATUS_TERMINADO ?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta reparto?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>
<div class="logistica-reparto-view">
    <div class="ibox panel-mint">
        <div class="ibox-title">
            <h5 ><?= Reparto::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información Reparto</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'chofer.singular',
                            'numUnidad.singular',
                            [
                                 'attribute' => 'Viaje',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->viaje->id) ?  Html::a('['.$model->viaje->placas.'] '.$model->viaje->nombre_chofer . '/ Fecha de salida : ['. date('Y-m-d', $model->viaje->fecha_salida) .']' , ['/logistica/viaje-tierra/view', 'id' => $model->viaje->id], ['class' => 'text-primary']) : '' ,
                             ],
                            'ruta.nombre',
                            'ruta_nombre',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel panel-primary panel-colorful">
                <div class="pad-all text-center">
                    <span class="text-3x text-thin"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?= count($model->repartoDetalles) ?></font></font></span>
                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"># Paquetes</font></font></p>
                    <i class="demo-pli-shopping-bag icon-lg"></i>
                </div>
            </div>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Paquetes relacionados con el reparto </h5>
                </div>
                <div class="ibox-content nano" style="    height: 500px;padding: 0;overflow-y: scroll;">
                    <div class="nano-content">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">#</th>
                                    <th style="text-align: center;">Categoria</th>
                                    <th style="text-align: center;">Tracked</th>
                                    <th style="text-align: center;">Producto</th>
                                    <th style="text-align: center;">Peso</th>
                                    <?php /* ?><th style="text-align: center;">Peso (MX)</th> */?>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->repartoDetalles as $key => $item): ?>
                                    <tr>
                                        <td><?= ($key + 1) ?></td>
                                        <td><?= isset($item->paquete->producto->categoria->singular) ? $item->paquete->producto->categoria->singular : '' ?></td>
                                        <td><?= $item->tracked ?></td>
                                        <td><?= $item->paquete->producto->nombre ?></td>
                                        <td><?= $item->paquete->peso ?> Lbs</td>
                                        <?php /* ?><td><?= $item->peso_reparto ? $item->peso_reparto : 0 ?> (MX) Lbs</td> */?>
                                        <td>
                                            <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                                                <?=  Html::a("<i class='fa fa-times'></i>", ['producto-remove','reparto_id' => $model->id, 'paquete_id' => $item->paquete->id ], [
                                                    'class' => 'btn btn-dark btn-circle imprimir-etiqueta',
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
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
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
              <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                    <div class="ibox">
                        <?= Html::a('Cancelar Reparto',['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_CANCEL ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas cancelar reparto?'] ])?>
                    </div>
                    <div class="ibox">
                        <?= Html::a('Enviar Reparto', ['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_CERRADO ], ['class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Enviar/Cerrar el reparto?'] ])?>
                    </div>
                <?php endif ?>
                <?php if ($model->status == Reparto::STATUS_CERRADO): ?>
                    <div class="panel">
                        <?= Html::a('Habilitar Reparto', ['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_ACTIVE ], ['class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Habilitar el reparto?'] ])?>
                    </div>
                    <div class="panel">
                        <?= Html::a('Terminar / Concluir Reparto',['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_TERMINADO ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas Terminar/Concluir el reparto?'] ])?>
                    </div>
                <?php endif ?>
                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte',null,['id' => 'reporte_download_reparto','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Hoja de ruta',null,['id' => 'reporte_bitacora_download_reparto','class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

                 <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar facturas',null,['id' => 'facturas_download_reparto','class' => 'btn btn-success btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
<?= $this->render('_modal_add-paquete',[
    'model' => $model,
]) ?>
<script>
    var $reporte_download  = $('#reporte_download_reparto'),
        $reporte_bitacora_download  = $('#reporte_bitacora_download_reparto'),
        $facturas_download          = $('#facturas_download_reparto'),
        set_reparto_id = <?= $model->id ?>;

$reporte_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-reparto-ajax') ?>?reparto_id='+set_reparto_id;
});

$reporte_bitacora_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-hoja-ruta-excel') ?>?reparto_id='+set_reparto_id;
});



$facturas_download.click(function(event){
        event.preventDefault();
        window.open("<?= Url::to(['facturas-reparto-ajax', 'reparto_id' => $model->id  ])  ?>",
        'imprimir',
        'width=700,height=900');
});
</script>
