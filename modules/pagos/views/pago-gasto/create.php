<?php
$this->title = 'Crear egreso';
$this->params['breadcrumbs'][] = 'Egresos (Gasto - Pagos)';
$this->params['breadcrumbs'][] = ['label' => 'Egresos', 'url' => ['index']];
?>
<div class="pagos-pago-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
