<?php

$this->title = $model->folio;

$this->params['breadcrumbs'][] = ['label' => 'Envios', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>


<div class="operaciones-envio-update">
    <?= $this->render('_form', [
    	'model' => $model,
    	'producto' => $producto,
    ]) ?>

    <?= $this->render('../envio/_modal_create_user', [
		'model' => $model,
	]) ?>

</div>
