<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\envio\EnvioDetalle;
use app\models\esys\EsysCambiosLog;
use app\models\promocion\PromocionComplemento;
/* @var $this yii\web\View */

$this->title =  '#'. $model->folio ;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="operaciones-envio-view">
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información Envio</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                 'attribute' => 'Tipo de envío',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->tipo_envio) ? Envio::$tipoList[$model->tipo_envio] : '' ,
                             ],
                             [
                                 'attribute' => 'Origen',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->origen) ? Envio::$origenList[$model->origen] : '' ,
                             ],
                             'peso_total',
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Sucursal Emisor</h5>
                    </div>
                    <div class="ibox-content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                [
                                 'attribute' => 'Sucursal Emisor',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->sucursalEmisor->nombre) ?  Html::a($model->sucursalEmisor->nombre, ['/sucursales/sucursal/view', 'id' => $model->sucursalEmisor->id], ['class' => 'text-primary']) : '' ,
                                ],
                                'sucursalEmisor.encargadoSucursal.nombreCompleto',
                                [
                                 'attribute' => 'Tipo de sucursal',
                                 'format'    => 'raw',
                                 'value'     =>  isset($model->sucursalEmisor->tipo) ?  Sucursal::$tipoList[$model->sucursalEmisor->tipo] : '' ,
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 >Cliente Emisor</h5>
                    </div>
                    <div class="ibox-content">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                 [
                                     'attribute' => 'Cliente Emisor',
                                     'format'    => 'raw',
                                     'value'     =>  isset($model->clienteEmisor->nombreCompleto) ?  Html::a($model->clienteEmisor->nombreCompleto, ['/crm/cliente/view', 'id' => $model->clienteEmisor->id], ['class' => 'text-primary']) : '' ,
                                 ],
                                 "clienteEmisor.telefono",

                            ]
                        ]) ?>
                    </div>
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
                            'comentarios:ntext',
                        ]
                    ]) ?>
                </div>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'form-configuracion' ]) ?>
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Paquetes relacionados con el envio</h5>
                </div>
                <div class="ibox-content">
                    <table class="table table-bordered invoice-summary">
                        <thead>
                            <tr>
                                <th class="min-col text-center text-uppercase">Sucursal R.</th>
                                <th class="min-col text-center text-uppercase">Cliente R.</th>
                                <th class="min-col text-center text-uppercase">Categoria</th>
                                <th class="min-col text-center text-uppercase">Producto</th>
                                <th class="min-col text-center text-uppercase">N° piezas</th>
                                <th class="min-col text-center text-uppercase">Cantidad de elementos</th>
                                <th class="min-col text-center text-uppercase">Costo extra</th>
                                <th class="min-col text-center text-uppercase">Estatus</th>
                                <th class="min-col text-center text-uppercase">Peso</th>

                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->envioDetalles as $key => $item): ?>
                                <tr class="<?= $item->status == EnvioDetalle::STATUS_CANCELADO ? 'danger' : ''  ?>">
                                    <td><?= Html::a($item->sucursalReceptor->nombre, ['/sucursales/sucursal/view', 'id' => $item->sucursalReceptor->id], ['class' => 'text-primary']) ?></td>
                                    <td><?=  Html::a($item->clienteReceptor->nombreCompleto, ['/crm/cliente/view', 'id' => $item->clienteReceptor->id], ['class' => 'text-primary']) ?></td>
                                    <td><?= isset($item->producto->categoria->singular) ? $item->producto->categoria->singular  :'' ?></td>
                                    <td><?= $item->producto->nombre ?></td>
                                    <td><?= Html::input('number', 'pesoItemList['.$item->id.'][cantidad]',$item->cantidad,['class' => 'form-control','style'=> 'text-align:center', "disabled" => $model->status == Envio::STATUS_RECOLECTADO ? false : true ]) ?></td>
                                    <td><?= Html::input('number', 'pesoItemList['.$item->id.'][cantidad_piezas]',$item->cantidad_piezas,['class' => 'form-control' ,'style'=> 'text-align:center', "disabled" => $model->status == Envio::STATUS_RECOLECTADO ? false : true ]) ?></td>

                                    <td><?= $item->impuesto ?></td>
                                    <td><?= EnvioDetalle::$statusList[$item->status] ?></td>
                                    <td><?= Html::input('text', 'pesoItemList['.$item->id.'][peso]',$item->peso,['class' => 'form-control peso_item' , "disabled" => $model->status == Envio::STATUS_RECOLECTADO ? false : true ]) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="row totales cobros ">
                                <div class="col-sm-offset-6 col-sm-6">
                                    <span class="label">Peso total (Recolección)</span>
                                    <div class="input-group mar-btm">
                                        <?= Html::input('text', 'peso_mex_sin_empaque',$model->peso_mex_sin_empaque,[ 'class' => 'form-control peso_mex_sin_empaque', "disabled" => $model->status == Envio::STATUS_PREAUTORIZADO ? false: true ]) ?>
                                        <span class="input-group-addon">Lbs</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6">
                            <div class="row totales cobros ">
                                <div class="col-sm-offset-6 col-sm-6">
                                    <span class="label">Peso total (Empaquetado)</span>
                                    <div class="input-group mar-btm">
                                        <?= Html::input('text', 'peso_mex_con_empaque',$model->peso_mex_con_empaque,[ 'class' => 'form-control peso_mex_con_empaque', "disabled" => $model->status == Envio::STATUS_RECOLECTADO ? false : true]) ?>
                                        <span class="input-group-addon">Lbs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <?= Html::submitButton( 'Guardar cambios', ['class' =>  'btn btn-primary']) ?>
                <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
            </div>
            <?php ActiveForm::end(); ?>


        </div>
        <div class="col-sm-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Historial de cambios</h5>
                </div>
                <div class="ibox-content historial-cambios nano">
                    <div class="nano-content">
                        <?= EsysCambiosLog::getHtmlLog([
                            [new Envio(), $model->id],
                        ], 50, true) ?>
                    </div>
                </div>
                <div class="ibox-footer">
                    <?= Html::a('Ver historial completo', ['historial-cambios', 'id' => $model->id], ['class' => 'text-primary']) ?>
                </div>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>
<script>
    /*====================================================
    *               MODIFICA EL PRECIO ACTUAL
    *====================================================*/
    $('.peso_item').change(function(){

        $peso = 0;
        $.each($('.peso_item'),function(key,item){
            $peso = $peso +  parseInt($(item).val() ? $(item).val() : 0 );
        });

        $('.peso_mex_con_empaque').val($peso);
    });
</script>

