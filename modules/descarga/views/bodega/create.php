<?php

$this->title = 'Nueva configuracion';
$this->params['breadcrumbs'][] = 'Descarga bodega';
$this->params['breadcrumbs'][] = ['label' => 'Configuraciones', 'url' => ['index']];

?>


<div class="descarga-bodega-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
