<?php
$this->title = 'Crear promoción para sucursal';
$this->params['breadcrumbs'][] = 'Nuevo';
$this->params['breadcrumbs'][] = ['label' => 'Promociones', 'url' => ['index']];
?>
<div class="pagos-pago-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
