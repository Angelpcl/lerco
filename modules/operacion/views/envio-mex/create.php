<?php
/* @var $this yii\web\View */


$this->title = 'Nuevo envio MEX - USA';
$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];

?>


<div class="operacion-envio-create">

    <?= $this->render('_form', [
		'model' => $model,
		'producto' => $producto,
	]) ?>

	<?= $this->render('../envio/_modal_create_user', [
		'model' => $model,
	]) ?>

</div>

