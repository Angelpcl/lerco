<?php
$this->title = 'Crear país';
$this->params['breadcrumbs'][] = 'Editar '.$model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'País', 'url' => ['index']];
?>
<div class="pagos-pago-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>