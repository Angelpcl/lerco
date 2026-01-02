<?php
use app\models\pais\PaisesLatam;
$this->title = $model->folio;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
$pais = PaisesLatam::findOne($model->pais_destino_id);
$pais = $pais? $pais :PaisesLatam::find()->where(['codigo_iso' => 'MEX'])->one();
?>


<div class="operaciones-envio-update">
    <?= $this->render('_form', [
		'pais' =>$pais,
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
