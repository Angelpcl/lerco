<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\ruta\Ruta;


$this->title =  $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Rutas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="logistica-ruta-view">
    <p>
        <?= $can['update']?
            Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

        <?= $can['update'] || $can['create'] ?
            Html::button('Agregar sucursal', ['class' => 'btn btn-success',  "data-target" => "#modal-create-ruta" ,"data-toggle"=>"modal", "onclick" => "init_asignacion_sucursal(" . $model->id .")"]): '' ?>

        <?= $can['delete']?
            Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas eliminar esta ruta?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>
    <div class="ibox panel-mint">
        <div class="ibox-title">
            <h5 ><?= Ruta::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="panel">
                <div class="ibox-title">
                    <h5 >Información Ruta</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nombre',
                            'orden',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel">
                <div class="ibox-title">
                    <h5 >Sucursales asignas a la ruta <strong><?= $model->nombre ?></strong></h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Sucursal</th>
                                <th style="text-align: center;">Encargado</th>
                                <th style="text-align: center;">Orden</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->rutaSucursals as $key => $sucursal): ?>

                                <tr>
                                    <td><?= $sucursal->sucursal->nombre ?></td>
                                    <td><?= isset($sucursal->sucursal->encargado_id) ? $sucursal->sucursal->encargadoSucursal->nombreCompleto : '' ?></td>
                                    <td><?= $sucursal->orden ?></td>
                                    <td>
                                        <a  href="<?= Url::to(['delete-sucursal','ruta_id' => $sucursal->ruta_id,'sucursal_id'=>$sucursal->sucursal_id ])  ?>"  class="btn btn-primary btn-circle btn-xs" ><i class="fa fa-remove line icon-lg"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
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
        <div class="col-md-5">
            <div class="ibox panel-info">
                <div class="ibox-title">
                    <h5 ><?= Ruta::$tipoList[$model->tipo] ?></h5>
                </div>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>

<?= $this->render('_modal_create_sucursal-ruta',[
    'model' => $model,
]) ?>
</div>

