<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = 'admin';
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
  <?= $can['create']?
            Html::a('<i class="fa fa-plus-circle" aria-hidden="true"></i> NUEVO USUARIO', ['create'], ['class' => 'btn btn-success add', 'style' => 'background-color: #0b1f2e; color: white; border-radius: 9999px; padding: 8px 20px; font-weight: 600;']): '' ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a data-toggle="tab"  class="nav-link active" href="#tab-index">Usuarios Internos</a>
        </li>
        <li>
            <a data-toggle="tab" class="nav-link" href="#tab-usuario-externo">Usuarios Externos</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab-index" class="tab-pane active">
            <?= $this->render('_index_interno',[
                "can"   => $can
                ]) ?>
        </div>
        <div id="tab-usuario-externo" class="tab-pane">
                <?= $this->render('_index_externo',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
