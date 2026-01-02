<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
//use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\descarga\DescargaBodega;


/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="descarga-bodega-form">

    <?php $form = ActiveForm::begin() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Guardar configuración' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('Cancelar', ['index', 'tab' => 'index'], ['class' => 'btn btn-white']) ?>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-content">
                    <?= $form->field($model, 'tipo')->dropDownList(DescargaBodega::$tipoList, ['prompt' => 'SELECCIONAR ZONA']) ?>

                    <?= $form->field($model, 'estado_id')->widget(Select2::classname(), [
                        'language' => 'es',
                        'data' => EsysListaDesplegable::getEstados(),
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'options' => [
                            'placeholder' => 'Selecciona el estado',
                        ],
                        'pluginEvents' => [
                            "change" => "function(){ onEstadoChange() }",
                        ]
                    ]) ?>

                    <div class="div_municipio" style="display:none">
                        <?= $form->field($model, 'municipio_id')->widget(Select2::classname(), [
                            'language' => 'es',
                            'data' => $model->estado_id? EsysListaDesplegable::getMunicipios(['estado_id' => $model->estado_id]): [],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'options' => [
                                'placeholder' => 'Selecciona el municipio'
                            ],
                        ]) ?>
                    </div>
                    <?= $form->field($model, 'bodega_descarga')->dropDownList(DescargaBodega::$descargaList, ['prompt' => '']) ?>

                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script type="text/javascript">
var $inputEstado       = $('#descargabodega-estado_id'),
    $inputMunicipio    = $('#descargabodega-municipio_id'),
    $inputTipo         = $('#descargabodega-tipo'),
    VAR_MUNICIPIO      = <?= DescargaBodega::DESCARGA_MUNICIPIO  ?>,
    municipioSelected  = null;


    $(document).ready(function() {

    });

    /************************************
    / Estados y municipios
    /***********************************/
    function onEstadoChange() {
        var estado_id = $inputEstado.val();
        municipioSelected = estado_id == 0 ? null : municipioSelected;

        $inputMunicipio.html('');

        $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : estado_id}, function(json) {
            $.each(json, function(key, value){
                $inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
            });

            $inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
            $inputMunicipio.trigger('change');

        }, 'json');

    }


    $inputTipo.change(function(){
        $('.div_municipio').hide();
        $inputMunicipio.val(null).change();
        if ($inputTipo.val() == VAR_MUNICIPIO) {
            $('.div_municipio').show();
        }
    });



</script>
