<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\models\esys\EsysListaDesplegable;
use app\models\ticket\Ticket;
use app\models\envio\Envio;
use yii\web\JsExpression;
?>

<div class="operacion-ticket-form">

    <?php $form = ActiveForm::begin(['id' => 'form-ticket', 'options' => ['enctype' => 'multipart/form-data'] ]) ?>
  
    <div class="row">
        <div class="col-lg-8 col-md-7 col-sm-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 >Información Producto</h5>
                </div>
                <div class="ibox-content">
                    <div id="error-add-ticket" class="has-error" style="display: none"></div>
                    <div class="row">
                        <div class="col-lg-6">
                            
                                <?= $form->field($model, 'nombre')->textInput()->label("Nombre del producto") ?>
                                <?= $form->field($model, 'descripcion')->textInput()->label("Describa el producto para el cliente") ?>
        
                       
                        </div>
                     
                    </div>
                </div>
            </div>
        </div>
      
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Crear Producto' : 'Guardar cambios', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'btnGuardarTicket']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-white']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>







<script>

</script>
