<?php
$this->title = 'Crear país';
$this->params['breadcrumbs'][] = 'Nuevo';
$this->params['breadcrumbs'][] = ['label' => 'Zona roja', 'url' => ['index']];
?>
<div class="pagos-pago-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
