<?php
$this->title = 'Crear ticket';
$this->params['breadcrumbs'][] = 'Ticket';
$this->params['breadcrumbs'][] = ['label' => 'Ticket', 'url' => ['index']];
?>
<div class="operacion-ticket-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
