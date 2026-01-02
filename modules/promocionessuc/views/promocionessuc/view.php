<?php

use app\models\promocion\Promocion;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Promociones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['breadcrumbs'][] = 'Editar';
$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']])
?>

<p>
    <?= $can['update'] ?
        Html::a('Editar', ['update', 'id' => $model->id], [
            'class' => 'btn btn-primary',
            //'data' => [
            //    'confirm' => '¿Estás seguro de que deseas eliminar este país?',
            //    'method' => 'post',
            //],
        ]) : '' ?>
</p>
<div class="row panel">

    <div class="pagos-pago-form">
        <div class="modeles-widget">
            <h3>Detalles de Promoción</h3>

            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td><?= Html::encode($model->id) ?></td>
                </tr>
                <tr>
                    <th>Fecha de Inicio</th>
                    <td><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_inicio)) ?></td>
                </tr>
                <tr>
                    <th>Fecha de Fin</th>
                    <td><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_fin)) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= Html::encode(Promocion::$statusList[$model->status]) ?></td>
                </tr>
                <tr>
                    <th>Costo Libra Peso Sucursal</th>
                    <td><?= Html::encode($model->costo_libra_peso_suc) ?></td>
                </tr>
                <tr>
                    <th>Costo Libra Peso Cliente</th>
                    <td><?= Html::encode($model->costo_libra_peso_cli) ?></td>
                </tr>
                <tr>
                    <th>Costo Libra Caja Cliente</th>
                    <td><?= Html::encode($model->costo_libra_caja_cli) ?></td>
                </tr>
                <tr>
                    <th>Costo Libra Caja Sucursal</th>
                    <td><?= Html::encode($model->costo_libra_caja_suc) ?></td>
                </tr>
                <tr>
                    <th>Costo Caja Límite Cliente</th>
                    <td><?= Html::encode($model->costo_caja_limite_cli) ?></td>
                </tr>
                <tr>
                    <th>Costo Caja Límite Sucursal</th>
                    <td><?= Html::encode($model->costo_caja_limite_suc) ?></td>
                </tr>
            </table>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<style>
    .img-flag {
        width: 150px;
        /* Ajusta según tus necesidades */
        height: 100px;
        /* Ajusta según tus necesidades */
        object-fit: cover;
        /* Mantiene la proporción y recorta si es necesario */
        border: 1px solid #ddd;
        /* Agrega un borde para mayor definición */
        border-radius: 5px;
        /* Bordes redondeados */
    }

    .text-center {
        text-align: center;
        /* Centra la imagen dentro del contenedor */
    }

    .panel {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background-color: #fff;
    }

    .panel-body {
        padding: 15px;
    }

    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
    }
</style>