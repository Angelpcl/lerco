<?php
/* @var $this yii\web\View */

$this->title = $pais ? 'NUEVO ENVIO TIERRA A ' . $pais->nombre : 'NUEVO ENVIO TIERRA';

// Breadcrumbs
$this->params['breadcrumbs'][] = [
	'label' => 'Envios',
	'url' => ['index']
];

?>


<div class="operacion-envio-create">

	<?= $this->render('_form', [
		'pais'  => $pais,
		'model' => $model,
		'can' => $can,
	]) ?>

	<?= $this->render('_modal_create_user', [
		'model' => $model,
	]) ?>

	<?= $this->render('_modal_promocion', [
		'model' => $model,
	]) ?>

	<?= $this->render('_modal_promocion_manual', [
		'model' => $model,
	]) ?>

	<?= $this->render('_modal_create_producto', [
		'producto' => $producto,
	]) ?>

</div>