<?php

$this->title = 'Nuevo reparto';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Repartos', 'url' => ['index']];
?>

<div class="logistica-reparto-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
