<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->nombreCompleto;

$this->params['breadcrumbs'][] = ['label' => 'Paises', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['breadcrumbs'][] = 'Editar';
$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']])
?>

<p>
    <?= $can['update'] ?
        Html::a('Editar', ['update', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            #'data' => [
            #    'confirm' => '¿Estás seguro de que deseas eliminar este país?',
            #    'method' => 'post',
            #],
        ]) : '' ?>
</p>

<div class="pagos-pago-view container-fluid">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'nombre')->textInput(['class' => 'form-control', 'disabled' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'codigo_iso')->textInput(['class' => 'form-control', 'disabled' => true]) ?>
                </div>
                <div class="col-md-4 text-center">
                    <?php if (isset($model->imagen) && !empty($model->imagen)) : ?>
                        <?= Html::img('@web/uploads/flags/' . $model->imagen, ['alt' => 'Bandera', 'class' => 'img-flag', 'id' => 'img-avatar']) ?>
                    <?php else : ?>
                        <?= Html::img('@web/uploads/flags/default.jpeg', ['class' => 'img-flag', 'id' => 'img-avatar']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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