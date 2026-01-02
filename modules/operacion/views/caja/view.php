<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\caja\CajaMex;
use app\models\envio\EnvioDetalle;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->folio;

$this->params['breadcrumbs'][] = ['label' => 'Cajas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['update']?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

    <?= $can['delete']?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta caja?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>
<div class="operacion-caja-view">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><?= CajaMex::$statusList[$model->status] ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información caja</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'folio',
                            'categoria.singular',
                            'nombre',
                            'peso_aprox',
                            'nota:ntext',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Paquetes relacionados con la caja</h3>
                </div>
                <div class="panel-body nano" style="    height: 500px;padding: 0;">
                    <div class="nano-content">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Categoria</th>
                                    <th style="text-align: center;">Tracked</th>
                                    <th style="text-align: center;">Producto</th>
                                    <th style="text-align: center;">Peso</th>
                                    <th style="text-align: center;">Estatus</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->cajaDetalleMex as $key => $item): ?>
                                    <tr>
                                        <td><?= isset($item->envioDetalle->producto->categoria->singular) ? $item->envioDetalle->producto->categoria->singular : '' ?></td>
                                        <td><?= $item->tracked ?></td>
                                        <td><?= $item->envioDetalle->producto->nombre ?></td>
                                        <td><?= $item->envioDetalle->peso ?> Lbs</td>
                                        <td><?= EnvioDetalle::$statusList[$item->envioDetalle->status] ?></td>
                                        <td>
                                            <?php if ($model->status == CajaMex::STATUS_ACTIVE): ?>
                                                <?=  Html::a("<i class='fa fa-times'></i>", ['producto-remove','caja_id' => $model->id, 'paquete_id' => $item->envioDetalle->id ], [
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
        </div>
        <div class="col-md-3">
            <div class="panel">

                <?php if ($model->status == CajaMex::STATUS_ACTIVE): ?>
                    <?= Html::a('Cerrar caja', ['cerrar-caja','id' => $model->id], ['class' => 'btn btn-danger btn-lg btn-block','style'=>'padding: 6%;', 'data' => [
                        'confirm' => '¿Estás seguro de que deseas cerrar la caja?',
                        'method' => 'post',
                    ],])?>
                <?php endif ?>
            </div>
            <div class="panel">
                <?= Html::a('Imprimir Etiqueta', false, ['class' => 'btn btn-primary btn-lg btn-block', 'id' => 'imprimir-etiqueta','style'=>'    padding: 6%;'])?>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


<script>
$('#imprimir-etiqueta').click(function(event){
    event.preventDefault();
    window.open("<?= Url::to(['imprimir-etiqueta', 'id' => $model->id ])  ?>",
    'imprimir',
    'width=600,height=500');
});
</script>
