<?php
/* @var $this yii\web\View */
$this->title = 'Nueva promoción';
$this->params['breadcrumbs'][] = ['label' => 'Promociones', 'url' => ['index']];
?>
<div class="promociones-promocion-create">

    <?= $this->render('_form', [
		'model' => $model,
	]) ?>
	
	<?= $this->render('_modal_create_complemento', [
		'model' => $model,
	]) ?>

</div>

