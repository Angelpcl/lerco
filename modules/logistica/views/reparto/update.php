<?php
use app\models\Esys;

$this->title =  "#".$model->id;
//$this->params['breadcrumbs'][] = 'Clientes';
$this->params['breadcrumbs'][] = ['label' => 'Reparto', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';

?>


<div class="logistica-reparto-update">

    <?= $this->render('_form', [
    	'model' => $model,
    ]) ?>

</div>
