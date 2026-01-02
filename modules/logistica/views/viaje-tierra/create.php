<?php

$this->title = 'Nuevo viaje Tierra';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Viaje', 'url' => ['index']];

?>


<div class="logistica-viaje.tierra-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
