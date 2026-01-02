<?php
use app\models\Esys;

$this->title = "Fecha de salida : " . Esys::fecha_en_texto($model->fecha_salida);
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Viajes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

?>


<div class="logistica-viaje-lax-update">

    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?>

</div>
