<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\reparto\Reparto;
use app\models\ruta\Ruta;

$this->title = "Fecha de salida : " . Esys::fecha_en_texto($model->fecha_salida);

$this->params['breadcrumbs'][] = ['label' => 'Repartos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['update'] && $model->status != Reparto::STATUS_TERMINADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>
    <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
        <?= $can['update'] || $can['create'] ?
            Html::button('Agregar fila', ['class' => 'btn btn-mint',  "data-target" => "#modal-create-fila" ,"data-toggle"=>"modal"]) : ''?>
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
    <div class="panel panel-mint">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Reparto::$statusList[$model->status] ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Reparto</h3>
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
                    <h3 class="panel-title">Filas asignadas al reparto </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Fila</th>
                                <th style="text-align: center;">Chofer</th>
                                <th style="text-align: center;">Clave / N° de unidad</th>
                                <th style="text-align: center;">Rutas</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->repartoFila as $key => $repartoFila): ?>
                                <tr>
                                    <td><?=   $repartoFila->nombre->singular ?></td>
                                    <td><?=   $repartoFila->chofer->singular ?></td>
                                    <td><?=   $repartoFila->numCamion->singular ?></td>
                                    <td>
                                        <?php foreach ($repartoFila->filaRutas as $key => $fila): ?>
                                            <li class="list-group bg-trans " style="    margin: 5%;">
                                                <?= Html::a($fila->ruta->nombre, ['/logistica/ruta/view', 'id' => $fila->ruta->id], ['class' => ' text-primary'])?>
                                                <div class=" pull-right">
                                                <?=  Html::a("<i class='fa fa-times'></i>", ['delete-ruta','reparto_id' => $repartoFila->reparto_id, 'fila_id' => $fila->fila_id, 'ruta_id' => $fila->ruta_id], [
                                                    'class' => 'btn btn-danger btn-xs btn-circle ',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de que deseas remover esta Ruta?',
                                                        'method' => 'post',
                                                    ],

                                                ]) ?>
                                                <?=  Html::button("<i class='fa fa-clipboard'></i>",  [
                                                        "id"    => "reporte_recoleccion",
                                                        'class' => 'btn btn-mint btn-circle btn-xs',
                                                        "data-target" => "#modal-create-recoleccion",
                                                        "data-toggle"=> "modal",
                                                        "onclick" => "init_ruta_recoleccion(" . $repartoFila->reparto_id .", ". $fila->ruta_id .")"
                                                ]) ?>
                                                </div>
                                            </li>
                                        <?php endforeach ?>
                                    </td>
                                    <td>
                                        <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                                            <?=  Html::a("<i class='fa fa-times'></i>", ['delete-fila','reparto_id' => $repartoFila->reparto_id, 'fila_id' => $repartoFila->id], [
                                                'class' => 'btn btn-danger btn-circle ',
                                                'data' => [
                                                    'confirm' => '¿Estás seguro de que deseas remover esta Fila?',
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        <?php endif ?>
                                        <?=  Html::button("<i class='fa fa-road'></i>",  [
                                                "id"    => "reporte_recoleccion",
                                                'class' => 'btn btn-mint btn-circle ',
                                                "data-target" => "#modal-create-reparto" ,
                                                "data-toggle"=>"modal",
                                                "onclick" => "init_asignacion_ruta_fila(" . $model->id .", ". $repartoFila->id .")",

                                        ]) ?>
                                    </td>
                                    <?php /* ?>
                                    <td>
                                        <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                                             <?=  Html::a("<i class='fa fa-times'></i>", ['delete-ruta','reparto_id' => $repartoFila->reparto_id, 'ruta_id' => $repartoFila->ruta_id], [
                                                'class' => 'btn btn-danger btn-circle ',
                                                'data' => [
                                                    'confirm' => '¿Estás seguro de que deseas remover esta Ruta?',
                                                    'method' => 'post',
                                                ],
                                        ]) ?>
                                        <?php endif ?>
                                        <?=  Html::button("<i class='fa fa-clipboard'></i>",  [
                                                "id"    => "reporte_recoleccion",
                                                'class' => 'btn btn-mint btn-circle ',
                                                "data-target" => "#modal-create-recoleccion",
                                                "data-toggle"=> "modal",
                                                "onclick" => "init_ruta_recoleccion(" . $repartoFila->ruta_id .", ". $repartoFila->reparto_ruta_id .")"
                                        ]) ?>
                                    </td>
                                    */ ?>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Asignación de paquetes a las filas </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Fila</th>
                                <th style="text-align: center;">Ruta</th>
                                <th style="text-align: center;">PQ</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">

                            <?php foreach ($model->repartoFila as $key => $repartoFila): ?>

                                <?php foreach ($repartoFila->filaRutas as $key => $filaRutas): ?>
                                    <tr>
                                        <td><?=   $filaRutas->fila->nombre->singular ?></td>
                                        <td><?= Html::a($filaRutas->ruta->nombre, ['/logistica/ruta/view', 'id' => $filaRutas->ruta->id], ['class' => ' text-primary'])?></td>
                                        <td>
                                            <?php foreach ($filaRutas->filaPaquetes as $key => $paquete): ?>
                                                <li> <?= $key + 1 ?> - <?= $paquete->tracked ?></li>
                                            <?php endforeach ?>
                                        </td>
                                        <td>
                                            <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                                            <?=  Html::button("<i class='fa fa-cubes'></i>",  [
                                                "id"    => "reporte_recoleccion",
                                                'class' => 'btn btn-warning btn-circle  btn-xs',
                                                "data-target" => "#modal-create-paquete",
                                                "data-toggle"=> "modal",
                                                "onclick" => "init_fila_paquete(" . $filaRutas->id .",". $filaRutas->ruta->id.")"
                                            ]) ?>
                                            <?php endif ?>
                                        </td>
                                        <?php /* ?>
                                        <td>
                                            <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                                                 <?=  Html::a("<i class='fa fa-times'></i>", ['delete-ruta','reparto_id' => $repartoFila->reparto_id, 'ruta_id' => $repartoFila->ruta_id], [
                                                    'class' => 'btn btn-danger btn-circle ',
                                                    'data' => [
                                                        'confirm' => '¿Estás seguro de que deseas remover esta Ruta?',
                                                        'method' => 'post',
                                                    ],
                                            ]) ?>
                                            <?php endif ?>
                                            <?=  Html::button("<i class='fa fa-clipboard'></i>",  [
                                                    "id"    => "reporte_recoleccion",
                                                    'class' => 'btn btn-mint btn-circle ',
                                                    "data-target" => "#modal-create-recoleccion",
                                                    "data-toggle"=> "modal",
                                                    "onclick" => "init_ruta_recoleccion(" . $repartoFila->ruta_id .", ". $repartoFila->reparto_ruta_id .")"
                                            ]) ?>
                                        </td>
                                        */ ?>
                                    </tr>

                                <?php endforeach ?>
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
              <?php if ($model->status == Reparto::STATUS_ACTIVE): ?>
                    <div class="panel">
                        <?= Html::a('Cancelar Reparto',['set-status-reparto', 'id' => $model->id, 'status' => Reparto::STATUS_CANCEL ],  ['class' => 'btn btn-primary btn-lg btn-block', 'style'=>'padding: 6%;','data' => ['confirm' => '¿Estás seguro de que deseas cancelar reparto?'] ])?>
                    </div>
                    <div class="panel">
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

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>

<script>
    var $reporte_download = $('#reporte_download_reparto'),
        set_reparto_id = <?= $model->id ?>;

$reporte_download.click(function(event){
    event.preventDefault();
    window.location = '<?= Url::to('reporte-reparto-ajax') ?>?reparto_id='+set_reparto_id;
});
</script>

<?= $this->render('_modal_create_reparto-recoleccion',[
    'model' => $model,
]) ?>

<?= $this->render('_modal_create_reparto-fila',[
    'model' => $model,
]) ?>

<?= $this->render('_modal_create_reparto-ruta',[
    'model' => $model,
]) ?>

<?= $this->render('_modal_create_reparto-paquete',[
    'model' => $model,
]) ?>
</div>
