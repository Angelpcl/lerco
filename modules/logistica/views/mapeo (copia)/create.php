<?php

$this->title = 'Nuevo mapeo';
$this->params['breadcrumbs'][] = 'Logistica';
$this->params['breadcrumbs'][] = ['label' => 'Mapeo', 'url' => ['index']];
?>

<div class="logistica-mapeo-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
