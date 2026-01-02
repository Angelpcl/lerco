<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\viaje\Viaje;
use app\assets\HighchartsAsset;
use app\models\envio\EnvioDetalle;
use app\models\movimiento\MovimientoPaquete;
use app\models\descarga\DescargaBodega;
use app\models\envio\DetailEnvioProduct;

HighchartsAsset::register($this);

$this->title = "Fecha de salida : " . Esys::fecha_en_texto($model->fecha_salida);

$this->params['breadcrumbs'][] = ['label' => 'Viajes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

$total_carga    = 0;
$total_bodega   = 0;
ini_set('memory_limit', '-1');
?>

<p>
    <?= $can['update'] && $model->status != Viaje::STATUS_TERMINADO ?
        Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>

    <?= $can['delete'] && $model->status != Viaje::STATUS_TERMINADO ?
        Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Estás seguro de que deseas eliminar esta Viaje?',
                'method' => 'post',
            ],
        ]) : '' ?>
</p>
<div class="logistica-viaje-tierra-view">
    <div class="ibox panel-mint">
        <div class="ibox-title">
            <h5><?= Viaje::$statusList[$model->status] ?></h5>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel">
                        <div class="ibox-title">
                            <h5>Información Viaje</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'num_viaje',
                                    'fecha_salida:date',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel">
                        <div class="ibox-title">
                            <h5>Unidad de trailer / Chofer</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'nombre_chofer',
                                    'placas',
                                    'transportista',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="panel panel-primary panel-colorful">
                        <div class="pad-all text-center">
                            <span class="text-3x text-thin">
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;"><?= count($model->viajeDetalles) ?></font>
                                </font>
                            </span>
                            <p>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;"># Paquetes</font>
                                </font>
                            </p>
                            <i class="demo-pli-shopping-bag icon-lg"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel panel-primary panel-colorful">
                        <div class="pad-all text-center">
                            <span class="text-3x text-thin">
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;"><?= number_format($model->getPesoTotalViaje(), 2) ?></font>
                                </font>
                            </span>
                            <p>
                                <font style="vertical-align: inherit;">
                                    <font style="vertical-align: inherit;"># LBS</font>
                                </font>
                            </p>
                            <i class="demo-pli-shopping-bag icon-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-------------------------------------- SECCION  TAGS PAQUETES  START ---------------------------------------------->
            <div class="tabs-container">
                <ul class="nav nav-tabs" role="tablist">
                    <li>
                        <a class="nav-link active" data-toggle="tab" href="#tab-index"><strong>INGRESADOS</strong> </a>
                    </li>
                    <li>
                        <a class="nav-link" data-toggle="tab" href="#tab-etapa-uno">BODEGA USA</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" id="tab-index" class="tab-pane active">
                        <div class="ibox ">
                            <div class="ibox-content " style="overflow-y: scroll;height:  500px;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">#</th>
                                            <th style="text-align: center;">BODEGA [DISTRIBUIDORA]</th>
                                            <th style="text-align: center;">Categoria</th>
                                            <th style="text-align: center;">Tracked</th>
                                            <th style="text-align: center;">Nombre</th>
                                            <th style="text-align: center;">Peso</th>
                                            <th style="text-align: center;">Peso MX</th>
                                            <th style="text-align: center;">Estatus</th>
                                            <th style="text-align: center;">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody style="text-align: center; ">
                                        <?php $count = 0;  ?>
                                        <?php foreach ($model->viajeDetalles as $key => $item): ?>
                                            <?php $total_carga = $total_carga + 1;
                                            //echo "<pre>";
                                            $modelDetalles = DetailEnvioProduct::find()
                                                ->where(['detalle_envio_id' => $item->paquete_id])
                                                ->one();
                                            //echo "<pre>   dfhbdfhdfh";
                                            //print_r($item->tracked);
                                            //die;
                                            $array = explode('/', $item->tracked);
                                            $pos = $array[1] - 1 ;

                                            $json = $modelDetalles ? json_decode($modelDetalles->detalle_json, true) : null;
                                            if ($json) {
                                                try{
                                                #    echo "<pre>   dfhbdfhdfh";
                                                #print_r($json[$pos]['tipo_producto']);
                                                #die;
                                                $tipo = intval($json[$pos]['tipo_producto']);
                                                $peso = $tipo == 30 ? 'Caja sin límite' : $json[$pos]['peso_max'] . ' LBS';

                                                }catch(\Throwable  $e){
                                                    #echo "<pre>   dfhbdfhdfh";
                                                    #print_r($array);
                                                    #die;
                                                    $peso = "Update failed";
                                                }
                                                
                                            } else {
                                                $peso =  round($item->envioDetalleLaxTierra->peso / $item->envioDetalleLaxTierra->cantidad, 2) . ' LBS';
                                            }


                                            ?>
                                            <?php if (Yii::$app->user->identity->sucursal_id && Yii::$app->user->can('Encargado de sucursal')): ?>
                                                <?php if ($item->envioDetalleLaxTierra->envio->sucursal_emisor_id == Yii::$app->user->identity->sucursal_id): ?>

                                                    <?php $count++;  ?>
                                                    <tr>

                                                        <td><?= $count  ?></td>

                                                        <td><?= isset($item->envioDetalleLaxTierra->bodega_descarga) && $item->envioDetalleLaxTierra->bodega_descarga ? DescargaBodega::$descargaList[$item->envioDetalleLaxTierra->bodega_descarga]  : 'N/A' ?></td>

                                                        <td><?= isset($item->envioDetalleLaxTierra->producto->categoria->singular) ? $item->envioDetalleLaxTierra->producto->categoria->singular : ''  ?></td>

                                                        <td><?= $item->tracked ?></td>

                                                        <td><?= $item->envioDetalleLaxTierra->producto->nombre ?></td>

                                                        <td><?= $peso  ?> </td>

                                                        <td><?= number_format($item->peso_mx) ?> Lbs</td>

                                                        <td><?= EnvioDetalle::$statusList[$item->envioDetalleLaxTierra->status] ?></td>

                                                        <td>

                                                            <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>

                                                                <?= Html::a("<i class='fa fa-times'></i>", ['producto-remove', 'viaje_id' => $model->id, 'paquete_id' => $item->envioDetalleLaxTierra->id, "tipo" => $item->tipo], [

                                                                    'class' => 'btn btn-dark btn-circle ',

                                                                    'data' => [

                                                                        'confirm' => '¿Estás seguro de que deseas remover este paquete?',

                                                                        'method' => 'post',

                                                                    ],

                                                                ]) ?>
                                                            <?php endif ?>
                                                        </td>
                                                    </tr>
                                                <?php endif ?>
                                            <?php elseif (Yii::$app->user->can('Encargado de bodega')): ?>
                                                <?php if (Yii::$app->user->identity->bodega_descarga_asignado == $item->envioDetalleLaxTierra->bodega_descarga): ?>

                                                    <?php $count++  ?>

                                                    <tr>
                                                        <td><?= $count  ?></td>
                                                        <td><?= isset($item->envioDetalleLaxTierra->bodega_descarga) && $item->envioDetalleLaxTierra->bodega_descarga ? DescargaBodega::$descargaList[$item->envioDetalleLaxTierra->bodega_descarga]  : 'N/A' ?></td>
                                                        <td><?= isset($item->envioDetalleLaxTierra->producto->categoria->singular) ? $item->envioDetalleLaxTierra->producto->categoria->singular : '' ?></td>
                                                        <td><?= $item->tracked ?></td>
                                                        <td><?= $item->envioDetalleLaxTierra->producto->nombre ?></td>
                                                        <td><?= $peso  ?> Lbs</td>
                                                        <td><?= number_format($item->peso_mx) ?> Lbs</td>
                                                        <td><?= EnvioDetalle::$statusList[$item->envioDetalleLaxTierra->status] ?></td>
                                                        <td>
                                                            <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>

                                                                <?= Html::a("<i class='fa fa-times'></i>", ['producto-remove', 'viaje_id' => $model->id, 'paquete_id' => $item->envioDetalleLaxTierra->id, "tipo" => $item->tipo], [

                                                                    'class' => 'btn btn-dark btn-circle ',
                                                                    'data' => [
                                                                        'confirm' => '¿Estás seguro de que deseas remover este paquete?',
                                                                        'method' => 'post',
                                                                    ],
                                                                ]) ?>
                                                            <?php endif ?>
                                                        </td>
                                                    </tr>
                                                <?php endif ?>
                                            <?php else: ?>

                                                <?php $count++;    ?>
                                                <tr>
                                                    <td><?= $count  ?></td>

                                                    <td><?= isset($item->envioDetalleLaxTierra->bodega_descarga) && $item->envioDetalleLaxTierra->bodega_descarga ? DescargaBodega::$descargaList[$item->envioDetalleLaxTierra->bodega_descarga]  : 'N/A' ?></td>

                                                    <td><?= isset($item->envioDetalleLaxTierra->producto->categoria->singular) ? $item->envioDetalleLaxTierra->producto->categoria->singular : '' ?></td>

                                                    <td><?= $item->tracked ?></td>

                                                    <td><?= $item->envioDetalleLaxTierra->producto->nombre ?></td>

                                                    <td><?= $peso ?> </td>

                                                    <td><?= number_format($item->peso_mx) ?> Lbs</td>

                                                    <td><?= EnvioDetalle::$statusList[$item->envioDetalleLaxTierra->status] ?></td>

                                                    <td>

                                                        <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>

                                                            <?= Html::a("<i class='fa fa-times'></i>", ['producto-remove', 'viaje_id' => $model->id, 'paquete_id' => $item->envioDetalleLaxTierra->id, "tipo" => $item->tipo], [

                                                                'class' => 'btn btn-dark btn-circle ',

                                                                'data' => [

                                                                    'confirm' => '¿Estás seguro de que deseas remover este paquete?',

                                                                    'method' => 'post',

                                                                ],

                                                            ]) ?>

                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" id="tab-etapa-uno" class="tab-pane">
                        <div class="ibox ">
                            <div class="ibox-content " style="overflow-y: scroll;height:  500px;">
                                <table class="table ">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">#</th>
                                            <th style="text-align: center;">BODEGA [DISTRIBUIDORA]</th>
                                            <th style="text-align: center;">Tracked</th>
                                            <th style="text-align: center;">Movimiento</th>
                                            <th style="text-align:center">Monto Pagado</th>
                                            <th style="text-align:center">Monto Total</th>
                                            <th style="text-align:center">Fecha Pago</th>
                                            <th style="text-align:center">Denegar</th>
                                        </tr>
                                    </thead>
                                    <tbody style="text-align: center; ">
                                        <?php $count = 0; ?>
                                        <?php foreach (Viaje::getEtapa($model->id) as $key => $item): ?>
                                            <?php $count++  ?>
                                            <tr class="<?= MovimientoPaquete::LAX_TIER_SUCURSAL == $item["tipo_movimiento"] ? 'btn-danger' : 'btn-warning' ?>">
                                                <td><?= $count  ?></td>
                                                <td><?= $item["bodega_descarga"] ? DescargaBodega::$descargaList[$item["bodega_descarga"]]  : 'N/A' ?></td>
                                                <td><?= $item["tracked"] ?></td>
                                                <td><?= MovimientoPaquete::$tipoLaxTierList[$item["tipo_movimiento"]]  ?></td>
                                                <td><?= number_format($item["total_pagado"], 2)  ?></td>
                                                <td><?= number_format($item["total"], 2)  ?></td>
                                                <td><?= date('Y-m-d', $item["fecha_pago"])   ?></td>
                                                <td>
                                                    <?php if (Yii::$app->user->can('admin')): ?>
                                                        <?php if (MovimientoPaquete::LAX_TIER_SUCURSAL == $item["tipo_movimiento"]): ?>
                                                            <?php $total_bodega = $total_bodega + 1 ?>
                                                            <?php if ($item["is_denegado"] == 0): ?>
                                                                <?= Html::a('Denegar', ['denegar-paquete', 'viaje_id' => $model->id, 'tracked' => $item["tracked"], 'paquete_id' => $item['paquete_id']], [

                                                                    'class' => 'btn btn-dark btn-xs',
                                                                    'data' => [
                                                                        'confirm' => '¿Estás seguro de que deseas DENEGAR el acceso al paquete?',
                                                                        'method' => 'post',
                                                                    ],
                                                                ]) ?>
                                                            <?php else: ?>
                                                                <?= Html::a('Aprobar', ['aprobar-paquete', 'viaje_id' => $model->id, 'tracked' => $item["tracked"], 'paquete_id' => $item['paquete_id']], [
                                                                    'class' => 'btn btn-mint btn-xs',
                                                                    'data' => [
                                                                        'confirm' => '¿Estás seguro de que deseas APROBAR el acceso al paquete?',
                                                                        'method' => 'post',
                                                                    ],
                                                                ]) ?>
                                                            <?php endif ?>


                                                        <?php endif ?>
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
            </div>
            <!-------------------------------------- SECCION TAGS PAQUETES END ------------------------------------------------->


            <!-------------------------------------- SECCION COMENTARIO START -------------------------------------------------->
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
            <!-------------------------------------- SECCION COMENTARIO END -------------------------------------------------->

        </div>
        <div class="col-md-3">
            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte Conciliación', null, ['id' => 'reporte_download_concilacion', 'class' => 'btn btn-warning btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>
            <?php if ($model->status == Viaje::STATUS_ACTIVE): ?>
                <div class="panel">
                    <?= Html::a('Cancelar Viaje  Tierra', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CANCEL],  ['class' => 'btn btn-primary btn-lg btn-block', 'style' => 'padding: 6%;', 'data' => ['confirm' => '¿Estás seguro de que deseas cancelar viaje?']]) ?>
                </div>
                <div class="panel">
                    <?= Html::a('Enviar Viaje Tierra', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_CERRADO], ['class' => 'btn btn-warning btn-lg btn-block', 'style' => 'padding: 6%;', 'data' => ['confirm' => '¿Estás seguro de que deseas Enviar/Cerrar el viaje?']]) ?>
                </div>
            <?php endif ?>
            <?php if ($model->status == Viaje::STATUS_CERRADO): ?>
                <div class="panel">
                    <?= Html::a('Habilitar Viaje Tierra', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_ACTIVE], ['class' => 'btn btn-mint btn-lg btn-block', 'style' => 'padding: 6%;', 'data' => ['confirm' => '¿Estás seguro de que deseas Habilitar el viaje?']]) ?>
                </div>
                <div class="panel">
                    <?= Html::a('Terminar / Concluir Viaje Tierra', ['set-status-viaje', 'id' => $model->id, 'status' => Viaje::STATUS_TERMINADO],  ['class' => 'btn btn-primary btn-lg btn-block', 'style' => 'padding: 6%;', 'data' => ['confirm' => '¿Estás seguro de que deseas Terminar/Concluir el viaje?']]) ?>
                </div>
            <?php endif ?>
            <?php if ($model->status == Viaje::STATUS_TERMINADO): ?>
                <?php /* ?>
                    <div class="panel">
                        <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte concilación',null,['id' => 'reporte_download_concilacion', 'class' => 'btn btn-warning btn-lg btn-block', 'style'=>'padding: 6%;'] )?>
                    </div>
                    */ ?>
            <?php endif ?>

            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reporte', null, ['id' => 'reporte_download_viaje', 'class' => 'btn btn-mint btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>

            <div class="panel">
                <?= Html::a('<i class="fa fa-edit mar-rgt-5px"></i> Reporte de paquetes', null, ['id' => 'reporte_download_verificacion', 'class' => 'btn btn-dark btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>

            <?php /*  ?>

                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar reetiquetas',null,['id' => 'reporte_download_reetiquetas','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%; display:none;' ])?>
                </div>
                */ ?>

            <div class="panel">
                <?= Html::a('<i class="fa fa-print mar-rgt-5px"></i> Imprimir Etiquetas', null, ['id' => 'imprimir_download_reetiquetas', 'class' => 'btn btn-white btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>

            <?php /* ?>

                <div class="panel">
                    <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar Julio',null,['id' => 'reporte_download_julio','class' => 'btn btn-mint btn-lg btn-block', 'style'=>'padding: 6%;' ])?>
                </div>

                */ ?>

            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte Entrada', null, ['id' => 'reporte_download_entrada', 'class' => 'btn btn-warning btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>


            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte Administracion ', null, ['id' => 'reporte_download_administracion', 'class' => 'btn btn-warning btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>

            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Generar Checklist', null, ['id' => 'reporte_download_checklist', 'class' => 'btn btn-mint btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>
            <div class="panel">
                <?= Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte de Carga', null, ['id' => 'reporte_download_carga', 'class' => 'btn btn-mint btn-lg btn-block', 'style' => 'padding: 6%;']) ?>
            </div>
            <div class="dashboard-index" style="margin-bottom: 10%;">
                <div id="demo-chart"></div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
<script>
    var $reporte_download = $('#reporte_download_viaje'),
        $reporte_download_verificacion = $('#reporte_download_verificacion'),
        $reporte_download_concilacion = $('#reporte_download_concilacion'),
        $reporte_download_reetiquetas = $('#reporte_download_reetiquetas'),
        $imprimir_download_reetiquetas = $('#imprimir_download_reetiquetas'),
        $reporte_download_julio = $('#reporte_download_julio'),
        $reporte_download_checklist = $('#reporte_download_checklist'),
        $reporte_download_carga = $('#reporte_download_carga'),
        $reporte_download_administracion = $('#reporte_download_administracion'),
        $reporte_download_entrada = $('#reporte_download_entrada'),
        set_viaje_id = <?= $model->id ?>;
    set_envio_ini = "<?= $model->envio_ini_id ?>";
    set_envio_fin = "<?= $model->envio_fin_id ?>";

    $reporte_download.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-ajax') ?>?viaje_id=' + set_viaje_id;
    });

    $reporte_download_verificacion.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-verificacion-ajax') ?>?viaje_id=' + set_viaje_id;
    });

    $reporte_download_concilacion.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-concilacion-ajax') ?>?viaje_id=' + set_viaje_id;
    });

    $reporte_download_reetiquetas.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-reetiquetas-ajax') ?>?viaje_id=' + set_viaje_id;
    });

    $imprimir_download_reetiquetas.click(function(event) {
        event.preventDefault();
        window.open('<?= Url::to(['imprimir-reetiquetas-pdf']) ?>?viaje_id=' + set_viaje_id,
            'imprimir',
            'width=600,height=500');
    });

    $reporte_download_julio.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-julio-ajax') ?>?viaje_id=' + set_viaje_id;
    });

    $reporte_download_checklist.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-check-list-ajax') ?>?envio_ini=' + set_envio_ini + '&envio_fin=' + set_envio_fin + '&viaje_id=' + set_viaje_id;
    });

    $reporte_download_carga.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-viaje-carga-ajax') ?>?envio_ini=' + set_envio_ini + '&envio_fin=' + set_envio_fin + '&viaje_id=' + set_viaje_id;
    });

    $reporte_download_administracion.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-administracion') ?>?viaje_id=' + set_viaje_id;
    });



    $reporte_download_entrada.click(function(event) {
        event.preventDefault();
        window.location = '<?= Url::to('reporte-entrada') ?>?viaje_id=' + set_viaje_id;
    });
</script>

<script>
    $total_carga = <?= $total_carga   ?>;
    $total_bodega = <?= $total_bodega   ?>;

    $(document).ready(function() {
        loadVendedorCliente();
    });
    var loadVendedorCliente = function() {
        Highcharts.chart('demo-chart', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Proceso de Embarque'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                name: 'Porcentaje',
                colorByPoint: true,
                data: [{
                    name: 'Embarque',
                    y: $total_carga,
                    sliced: true,
                    selected: true
                }, {
                    name: 'Bodega',
                    y: $total_bodega
                }]
            }]
        });
    }
</script>