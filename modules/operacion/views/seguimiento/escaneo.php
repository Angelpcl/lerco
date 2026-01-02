<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title =  'Escaneo';
$this->params['breadcrumbs'][] = 'Escaneo / Busqueda';
?>

<div class="row">
    <div class="col-md-6  col-sm-offset-3">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Ingresa el folio del envio</h5>
            </div>
            <div class="ibox-content">
            	<?php $form = ActiveForm::begin(['id' => 'form-escaneo' ]) ?>
            		<div class="row">
            			<div class="col-sm-6 col-sm-offset-3">
							<?=  Html::input('text','folio',null,[ 'class' => 'form-control','placeholder'=>'TIE-00000']); ?>
							<br>
            	    		<?= Html::submitButton( 'Buscar envio', ['class' => 'btn btn-primary btn-block btn-lg']) ?>
						</div>
					</div>
            	<?php ActiveForm::end(); ?>
            </div>
        </div>
        <img src="http://www.oca.com.ar/wp-content/themes/oca_theme/assets/buscador-envios/1.svg" alt="" class="img-responsive" style="filter: hue-rotate(130deg) brightness(134%)">
    </div>
</div>
