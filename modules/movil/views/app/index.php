<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Precaptura de envios Tierra - LAX';
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
    <?= $can['create']?
            Html::a('Nueva precaptura', ['pre-envio'], ['class' => 'btn btn-success add']): '' ?>

</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-index">Precaptua Tierra</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-envio-lax">Precaptura Lax</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab-index" role="tabpanel" class="tab-pane active">
            <?= $this->render('_index_tierra',[
                "can"   => $can
                ]) ?>
        </div>
        <div role="tabpanel" id="tab-envio-lax" class="tab-pane">
            <?= $this->render('_index_lax',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
