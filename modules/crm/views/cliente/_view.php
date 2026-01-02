<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\cliente\Cliente;
use app\models\Esys;
use app\models\esys\EsysCambiosLog;
use app\models\esys\EsysDireccion;

 ?>
<div class="cliente-user-view">
    <div class="row">
        <div class="col-lg-9">
            <div class="row">
                <div class="col-md-7">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Cuenta de cliente y datos personales</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <div class="col-md-7">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            'id',
                                            "email:email",
                                        ],
                                    ]) ?>
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            "tituloPersonal.singular",
                                            "nombre",
                                            "apellidos",
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-5">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                          [
                                             'attribute' =>  "Sexo",
                                             'format'    => 'raw',
                                             'value'     => $model->sexo ?  Cliente::$sexoList[$model->sexo] : '',
                                         ]
                                        ],
                                    ]) ?>
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            "telefono",
                                            "telefono_movil",
                                            [
                                                'attribute' => 'Servicio preferente',
                                                'format'    => 'raw',
                                                'value'     => isset($model->servicio_preferente) ?  Cliente::$servicioList[$model->servicio_preferente] : '',
                                            ],
                                            [
                                                'attribute' => 'Tipo de cliente',
                                                'format'    => 'raw',
                                                'value'     => isset($model->tipo->singular) ?  $model->tipo->singular : '',
                                            ],
                                            "costo_venta"

                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ibox">
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                     [
                                         'attribute' => 'Asignado',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->asignadoCliente->nombre) ?  Html::a($model->asignadoCliente->nombre ." ". $model->asignadoCliente->apellidos , ['/admin/user/view', 'id' => $model->asignadoCliente->id], ['class' => 'text-primary']) : '' ,
                                     ]
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
                                         'attribute' => 'Se entero a través de',
                                         'format'    => 'raw',
                                         'value'     =>  isset($model->atravesDe->id) ?  $model->atravesDe->singular : '' ,
                                     ]
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Historial de llamadas</h5>
                        </div>
                        <div class="ibox-content">
                            <?php foreach ($model->historialCall as $key => $history): ?>
                            <li class="mar-btn" style="list-style:none;">
                                <div>
                                    <span class="pull-right">
                                        <p class="text-muted">hace <small title="<?= Esys::fecha_en_texto($history->created_at) ?>"><?= Esys::hace_tiempo_en_texto($history->created_at) ?></small></p>
                                    </span>
                                    <span><?= html::a(
                                        $history->createdBy->nombreCompleto . ' [' . $history->created_by . ']',
                                        ['/admin/user/view', 'id' => $history->created_by ],
                                        ['class' => 'text-primary']
                                    ) ?></span>
                                </div>
                                <div class="mar-btm">
                                     <b>Tel: <?= $history->telefono ?></b>&nbsp; Tipo:  &nbsp; <b> <?= $history->tipoRespuesta->singular ?></b> &nbsp; comentario:  &nbsp; <b><?= $history->comentario ?></b>
                                </div>
                            </li>
                            <?php endforeach ?>
                        </div>
                    </div>


                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Información extra / Comentarios</h5>
                        </div>
                        <div class="ibox-content">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'notas:ntext',
                                ]
                            ]) ?>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <div class="panel panel-info ">
                        <div class="ibox-title">
                                <h5><?= Cliente::$statusList[$model->status] ?> </h5>
                        </div>
                    </div>
                    <div class="panel panel-info ">
                        <div class="ibox-title">
                                <h5><?= $model->origen ? Cliente::$origenList[$model->origen] : '' ?> </h5>
                        </div>
                    </div>

                    <?php if ($model->origen == Cliente::ORIGEN_MX): ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Dirección</h5>
                            </div>
                            <div class="ibox-content">
                                <?= DetailView::widget([
                                    'model' => $model->direccion,
                                    'attributes' => [
                                        'referencia',
                                        'direccion',
                                        'num_ext',
                                        'num_int',
                                        'esysDireccionCodigoPostal.colonia',

                                    ]
                                ]) ?>
                                <?= DetailView::widget([
                                    'model' => $model->direccion,
                                    'attributes' => [
                                        "esysDireccionCodigoPostal.estado.singular",
                                        "esysDireccionCodigoPostal.municipio.singular",
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
                        <?php if ($model->origen == Cliente::ORIGEN_USA): ?>
                        <div class="ibox">
                            <div class="ibox-title">
                                <h5>Dirección</h5>
                            </div>
                            <div class="ibox-content">
                                <?= DetailView::widget([
                                    'model' => $model->direccion,
                                    'attributes' => [
                                        'referencia',
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
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Historial de cambios</h5>
                </div>
                <div class="ibox-content historial-cambios nano">
                    <div class="nano-content">
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Cliente(), $model->id],
                            [new EsysDireccion(), $model->direccion->id],
                        ], 50, true) ?>
                    </div>
                </div>
                <div class="panel-footer">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                </div>
            </div>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $(".historial-cambios.nano").nanoScroller();

    });

</script>
