<?php

$this->title = 'Nueva Ruta';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Rutas', 'url' => ['index']];

?>


<div class="logistica-ruta-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
