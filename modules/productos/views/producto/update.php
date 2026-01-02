<?php

$this->title = $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>


<div class="productos-producto-update">

    <?= $this->render('_form', [
    	'model' => $model,
        'modelCaja'=> $modelCaja
    ]) ?>

</div>
