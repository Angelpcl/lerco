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
        <?= $can['update'] ?
            Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>

        <?= $can['delete'] ?
            Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas eliminar este producto?',
                    'method' => 'post',
                ],
            ]) : '' ?>
    </p>

    <div class="row">
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Información producto</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'nombre',
                                    'pais.nombre',
                                    "categoria.singular",
                                    "unidadMedida.singular",
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <?php if ($model->is_producto == Producto::TIPO_CAJA) : ?>
                        <div class="panel">
                            <div class="panel-body text-center">
                                <div class="row">
                                    <div class="col">
                                        <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block"> <?= $model->sucursal ? $model->sucursal->nombre:""  ?></span>
                                            <small class="text-muted m-b block"><strong>SE GENERO PARA LA SUCURSAL</strong></small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block"> $<?= number_format($model->costo_total, 2)  ?></span>
                                            <small class="text-muted m-b block"><strong>PRECIO A COBRAR (POR UNIDAD)</strong></small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class=" m-l-md">
                                            <span class="h5 font-bold m-t block"> $<?= number_format($model->costo_suc, 2)  ?></span>
                                            <small class="text-muted m-b block"><strong>TOTAL A COBRAR EN SUCURSAL</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                </div>
                <div class="col-md-6">
                    <?php if ($model->is_caja_sin_limite_id) : ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Caja sin límite </h5>
                            </div>
                            <div class="ibox-content text-center">
                                <div class="row">
                                    <div class="col-md-4 ">
                                        <span class="h5 font-bold m-t block"> <?= $model->cajaSinLimite->largo  ?></span>
                                        <small class="text-muted m-b block"><strong>LARGO</strong></small>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="h5 font-bold m-t block"> <?= $model->cajaSinLimite->ancho  ?></span>
                                        <small class="text-muted m-b block"><strong>ANCHO</strong></small>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="h5 font-bold m-t block"> <?= $model->cajaSinLimite->alto  ?></span>
                                        <small class="text-muted m-b block"><strong>ALTO</strong></small>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-md-6">
                                        <span class="h5 font-bold m-t block"> <?= $model->cajaSinLimite->costo_suc  ?></span>
                                        <small class="text-muted m-b block"><strong>PRECIO A COBRAR (POR UNIDAD)</strong></small>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="h5 font-bold m-t block"> <?= $model->cajaSinLimite->costo_cli ?></span>
                                        <small class="text-muted m-b block"><strong>TOTAL A COBRAR EN SUCURSAL</strong></small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6">
                                        <span class="h5 font-bold m-t block"> <?= $model->sucursal ? $model->sucursal->nombre:""  ?></span>
                                        <small class="text-muted m-b block"><strong>SE GENERO PARA LA SUCURSAL</strong></small>

                                    </div>
                                    <div class="col-md-3"></div>

                                </div>
                            </div>

                        </div>
                    <?php endif ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Información extra / Comentarios</h5>
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
                <div class="col-md-3"></div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel">
                <div class="panel-body">
                    <div class="panel panel-info">
                        <div class="ibox-title">
                            <h5><?= Producto::$statusList[$model->status] ?></h5>
                        </div>
                    </div>
                    <div class="panel panel-info">
                        <div class="ibox-title">
                            <h5><?= Envio::$tipoList[$model->tipo_servicio] ?></h5>
                        </div>
                    </div>
                    <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
                </div>
            </div>

        </div>
    </div>
</div>