<?php

$this->title = 'Nuevo viaje Lax';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Viaje', 'url' => ['index']];

?>


<div class="logistica-viaje-lax-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
