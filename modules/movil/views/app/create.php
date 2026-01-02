<?php
/* @var $this yii\web\View */
$this->title = 'Nuevo envio Tierra - Lax';
$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];

?>
<div class="operacion-envio-create">

    <?= $this->render('_form', [
		'model' => $model,
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

<?php /* ?>



	<?= $this->render('_modal_create_producto', [
		'producto' => $producto,
	]) ?>

	*/ ?>
</div>

