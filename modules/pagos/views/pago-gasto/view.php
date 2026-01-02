<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Egreso', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<p>
    <?= $can['delete']?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar este egreso?',
                'method' => 'post',
            ],
        ]): '' ?>
</p>
<div class="pagos-pago-view">
    <div class="row">
        <div class="col-md-9">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Información Egreso</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'concepto.singular',
                            'fecha_pago:date',
                            'nota:ntext',
                            'created_at:date'
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

    </div>
</div>


