<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\esys\EsysSetting;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Configuraciones del Sitio';

?>

<div class="configuraciones-configuracion-form">

    <?php $form = ActiveForm::begin(['id' => 'form-configuracion' ]) ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-content">

                        <?php foreach ($model->configuracionAll as $key => $item): ?>
                            <?php if ( $item->clave  != EsysSetting::COBRO_SEGURO_MEX &&  $item->clave  != EsysSetting::PRECION_MEX_1 &&  $item->clave  != EsysSetting::PRECION_MEX_2  && $item->clave  != EsysSetting::PRECION_MEX_3 && $item->clave  != EsysSetting::PRECION_MEX_4 && $item->clave  != EsysSetting::PRECION_MEX_5 && $item->clave  != EsysSetting::PRODUCTO_IMPUESTO_LAX_NEW && $item->clave  != EsysSetting::PRODUCTO_IMPUESTO_LAX_OLD && $item->clave  != EsysSetting::PRODUCTO_IMPUESTO_TIERRA_NEW && $item->clave  != EsysSetting::PRODUCTO_IMPUESTO_TIERRA_OLD): ?>

                                    <?php switch ( $item->clave ) {
                                        case EsysSetting::RANGO_FILA_UNO: ?>
                                            <div class="form-group ">
                                                <?= Html::label('Rango de filas de 1 a 10', 'esysSetting_list') ?>

                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                            </div>
                                        <?php  break;
                                        case EsysSetting::RANGO_FILA_DOS: ?>
                                            <div class="form-group ">
                                                <?= Html::label('Rango de filas de 10 a 15', 'esysSetting_list') ?>

                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                            </div>
                                        <?php  break;
                                            default: ?>
                                            <div class="form-group ">
                                                <?= Html::label($item->clave, 'esysSetting_list') ?>

                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                            </div>
                                        <?php  break;
                                    } ?>
                            <?php endif ?>
                        <?php endforeach ?>

                </div>
            </div>


        </div>
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-content">
                    <h5 >Configuración de precios de servicio MEX</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <?php foreach ($model->configuracionAll as $key => $item): ?>
                            <?php if ($item->clave  == EsysSetting::COBRO_SEGURO_MEX ||  $item->clave  == EsysSetting::PRECION_MEX_1 ||  $item->clave  == EsysSetting::PRECION_MEX_2  || $item->clave  == EsysSetting::PRECION_MEX_3 || $item->clave  == EsysSetting::PRECION_MEX_4 || $item->clave  == EsysSetting::PRECION_MEX_5): ?>

                                <?php switch ( $item->clave ) {

                                      case EsysSetting::COBRO_SEGURO_MEX: ?>
                                         <div class="form-group col-sm-6">
                                            <?= Html::label('Cobro del seguro Mex %', 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                <span class="input-group-addon">%</span>
                                            </div>
                                        </div>
                                    <?php  break;

                                    case EsysSetting::PRECION_MEX_1: ?>
                                         <div class="form-group col-sm-6">
                                            <?= Html::label('.01 - 24 LBS', 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>
                                    <?php  break;
                                    case EsysSetting::PRECION_MEX_2: ?>

                                     <div class="form-group col-sm-6">
                                            <?= Html::label("25 - 49 LBS", 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                 <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>

                                    <?php  break;
                                        case EsysSetting::PRECION_MEX_3: ?>
                                        <div class="form-group col-sm-6">
                                            <?= Html::label("50 - 74 LBS", 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>
                                    <?php  break;
                                        case EsysSetting::PRECION_MEX_4: ?>
                                        <div class="form-group col-sm-6">
                                            <?= Html::label("75 - 99 LBS", 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>
                                    <?php  break;
                                        case EsysSetting::PRECION_MEX_5: ?>
                                        <div class="form-group col-sm-6">
                                            <?= Html::label("+100 LBS", 'esysSetting_list') ?>
                                            <div class="input-group mar-btm">
                                                <?= Html::input('text', 'esysSetting_list['.$item->clave.']',$item->valor,['class' => 'form-control']) ?>
                                                <span class="input-group-addon">USD</span>
                                            </div>
                                        </div>
                                    <?php  break; ?>
                               <?php } ?>

                            <?php endif ?>
                        <?php endforeach ?>
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

