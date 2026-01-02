<?php
$this->title = 'Crear caja';
$this->params['breadcrumbs'][] = 'Caja';
$this->params['breadcrumbs'][] = ['label' => 'Cajas', 'url' => ['index']];
?>
<div class="operacion-caja-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
