<?php

$this->title = 'Nuevo viaje Mex';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Viaje', 'url' => ['index']];

?>


<div class="logistica-viajesmex-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
