<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\producto\Producto;
use app\models\esys\EsysSetting;
use app\models\envio\Envio;
/* @var $this yii\web\View */

$this->title =  $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="productos-producto-view">
    <p>
        <?= $can['update']?
            Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

        <?= $can['delete']?
            Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas eliminar este producto?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>
    <div class="panel panel-info">
        <div class="ibox-title">
            <h5 ><?= Producto::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información producto</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nombre',
                            "categoria.singular",
                            "unidadMedida.singular",
                        ],
                    ]) ?>
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
        <div class="col-sm-3">
        	<?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


