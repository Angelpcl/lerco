<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use app\widgets\CreatedByView;
use app\models\sucursal\Sucursal;
use app\models\sucursal\ListaPrecioMx;
use app\models\esys\EsysListaDesplegable;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title =  $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Sucursales', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="sucursales-sucursal-view">
    <p>
        <?= $can['update']?
            Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']): '' ?>

        <?= $can['delete']?
            Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas eliminar esta sucursal?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>
    <div class="panel panell-info">
        <div class="ibox-title">
            <h5 ><?= Sucursal::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información sucursal</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'nombre',
                            "email:email",
                            "rfc",
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'telefono',
                            "telefono_movil",
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                             [
                                 'attribute' => 'Encargado',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->encargadoSucursal->nombre) ?  Html::a($model->encargadoSucursal->nombre ." ". $model->encargadoSucursal->apellidos , ['/admin/user/view', 'id' => $model->encargadoSucursal->id], ['class' => 'text-primary']) : '' ,
                             ]
                        ],
                    ]) ?>
                </div>
            </div>


            <?php if ($model->origen == Sucursal::ORIGEN_USA ): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>LISTA DE PRECIO (LIBRA) MEX  </h5>
                        <?= Html::a('NUEVO PRECIO', false, ['class' => 'btn   btn-warning', "data-target" => "#modal-precio-mx", "data-toggle" => "modal" ])?>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>QUIEN ENVIA</th>
                                    <th>COSTO DE LIBRA</th>
                                    <th>DEFAULT</th>
                                    <th>¿ DESTINO ?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 0 ?>
                                <?php foreach ($model->listaPrecioLibraMx as $key => $precio): ?>
                                    <?php $count++ ?>
                                    <tr class="text-center">
                                        <td><?= $count ?></td>
                                        <td><?= $precio->sucursal_envia_id ? $precio->sucursalEnvia->nombre : 'N/A' ?></td>
                                        <td><?= number_format($precio->precio_libra,2) ?> dlls.</td>
                                        <td><?= $precio->default == ListaPrecioMx::IS_DEFAULT ? "SI": "NO" ?></td>
                                        <td><?= isset($precio->destino->singular) ? $precio->destino->singular : 'GENERAL' ?></td>
                                    </tr>
                                <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>



            <?php if ($model->origen == Sucursal::ORIGEN_USA ): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>LISTA DE PRECIO (COSTO EXTRA) MEX  </h5>
                        <?= Html::a('NUEVO COSTO EXTRA', false, ['class' => 'btn   btn-warning', "data-target" => "#modal-impuesto-mx", "data-toggle" => "modal" ])?>
                    </div>
                    <div class="ibox-content">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>QUIEN ENVIA</th>
                                    <th>DEFAULT</th>
                                    <th>CATEGORIA</th>
                                    <th>APARTIR DE</th>
                                    <th>INTERVALO</th>
                                    <th>COSTO EXTRA</th>
                                    <th>¿ DESTINO ?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 0 ?>
                                <?php foreach ($model->listaPrecioImpuestoMx as $key => $precio): ?>
                                    <?php $count++ ?>
                                    <tr class="text-center">
                                        <td><?= $count ?></td>
                                        <td><?= $precio->sucursal_envia_id ? $precio->sucursalEnvia->nombre : 'N/A'?></td>
                                        <td><?= $precio->default == ListaPrecioMx::IS_DEFAULT ? "SI": "NO" ?></td>
                                        <td><?= isset($precio->categoria->singular) ? $precio->categoria->singular : 'N/A' ?></td>
                                        <td><?= $precio->required ?></td>
                                        <td><?= $precio->intervalo ?></td>
                                        <td><?= number_format($precio->impuesto,2) ?> dlls.</td>
                                        <td><?= isset($precio->destino->singular) ? $precio->destino->singular : 'GENERAL' ?></td>
                                    </tr>
                                <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif ?>


            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información extra / Comentarios</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'informacion:ntext',
                            'comentarios:ntext',
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <!--<iframe width="100%" class="ibox" height="500px" src="<?= Url::to(['imprimir-qr', 'id' => $model->id ])  ?>"></iframe>-->

            <div class="panel panel-info">
                <div class="ibox-title">
                    <h5 ><?= Sucursal::$tipoList[$model->tipo] ?></h5>
                </div>
            </div>
            <div class="panel ">
                <div class="ibox-title">
                        <h5 ><?= Sucursal::$origenList[$model->origen] ?> </h5>
                </div>
            </div>
            <?php if ($model->origen == Sucursal::ORIGEN_MX): ?>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Dirección</h5>
                    </div>
                    <div class="ibox-content">
                        <?= DetailView::widget([
                            'model' => $model->direccion,
                            'attributes' => [
                                'direccion',
                                'num_ext',
                                'num_int',
                                'esysDireccionCodigoPostal.colonia',
                                'colonia_new',
                            ]
                        ]) ?>
                        <?= DetailView::widget([
                            'model' => $model->direccion,
                            'attributes' => [
                                "estado.singular",
                                "municipio.singular",
                            ]
                        ]) ?>

                        <?= DetailView::widget([
                            'model' => $model->direccion,
                            'attributes' => [
                                'esysDireccionCodigoPostal.codigo_postal',
                            ]
                        ]) ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($model->origen == Sucursal::ORIGEN_USA): ?>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 >Dirección</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'direccion',
                                    'num_ext',
                                    'num_int',
                                    'colonia_usa',

                                ]
                            ]) ?>
                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    "estado_usa",
                                    "municipio_usa",
                                ]
                            ]) ?>

                            <?= DetailView::widget([
                                'model' => $model->direccion,
                                'attributes' => [
                                    'codigo_postal_usa',
                                ]
                            ]) ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>
            <div class="ibox">
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model->direccion,
                        'attributes' => [
                            'lng',
                            'lat',
                        ]
                    ]) ?>
                </div>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
     <div class="row">
        <div class="col-md-12">
            <div class="ibox">
                 <div class="ibox-title">
                    <h5 >Google Maps</h5>
                </div>
                <div class="ibox-content">
                    <div id="map" style="height: 400px; width: 100%; "></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fade modal inmodal" id="modal-precio-mx" role="dialog" tabindex="-1" aria-labelledby="modal-show-label">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">PRECIO DE LIBRA (MEX)</h4>
            </div>

            <?php $form = ActiveForm::begin(['action' => 'save-precio-mx' ]) ?>

            <?= $form->field($precioMex, 'sucursal_recibe_id')->hiddenInput(["value" => $model->id])->label(false) ?>
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?= $form->field($precioMex, 'sucursal_envia_id')->dropDownList(Sucursal::getItemsMexico(), [ "prompt" => "DEFAULT" ]) ?>

                                        <?= $form->field($precioMex, 'default')->checkbox([ "style" => "margin:35px" ]) ?>
                                    </div>
                                    <div class="col-sm-6">

                                        <?= $form->field($precioMex, 'destino_id')->dropDownList(EsysListaDesplegable::getItems('destino_usa')) ?>

                                        <?= $form->field($precioMex, 'precio_libra')->textInput(['type' => 'number', 'autocomplete' => 'off', 'style' => 'font-size: 24px', "step" =>"0.01"]) ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Cerrar</button>
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>


<div class="fade modal inmodal" id="modal-impuesto-mx" role="dialog" tabindex="-1" aria-labelledby="modal-show-label">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">COSTO EXTRA (MEX)</h4>
            </div>

            <?php $form = ActiveForm::begin(['action' => 'save-impuesto-mx' ]) ?>

            <?= $form->field($precioMex, 'sucursal_recibe_id')->hiddenInput(["value" => $model->id])->label(false) ?>
            <div class="modal-body">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?= $form->field($precioMex, 'sucursal_envia_id')->dropDownList(Sucursal::getItemsMexico(), [ "prompt" => "DEFAULT" ]) ?>

                                        <?= $form->field($precioMex, 'categoria_id')->dropDownList(EsysListaDesplegable::getItems('categoria_paquete_mex')) ?>
                                    </div>
                                    <div class="col-sm-6">

                                        <?= $form->field($precioMex, 'destino_id')->dropDownList(EsysListaDesplegable::getItems('destino_usa')) ?>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <?= $form->field($precioMex, 'required')->textInput(['type' => 'number', 'autocomplete' => 'off', 'style' => 'font-size: 24px; text-align: center;']) ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?= $form->field($precioMex, 'intervalo')->textInput(['type' => 'number', 'autocomplete' => 'off', 'style' => 'font-size: 24px; text-align: center;']) ?>
                                    </div>
                                    <div class="col-sm-4">
                                        <?= $form->field($precioMex, 'impuesto')->textInput(['type' => 'number', 'autocomplete' => 'off', 'style' => 'font-size: 24px; text-align: center;']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Cerrar</button>
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<script>
function initMap() {
  // The location of Uluru
  var uluru = {lat: <?= $model->direccion->lat  ?>, lng: <?= $model->direccion->lng  ?>};
  // The map, centered at Uluru
  var map = new google.maps.Map(
      document.getElementById('map'), {zoom: 15, center: uluru});
  // The marker, positioned at Uluru
  var marker = new google.maps.Marker({position: uluru, map: map});
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAsJAAEIYeTJb9Q-ZZtQYiiUND4HNaZ0Ok&callback=initMap">
</script>

