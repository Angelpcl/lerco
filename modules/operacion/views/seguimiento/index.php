<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
?>
<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a data-toggle="tab" class="nav-link active"  href="#tab-index">Envio Tierra</a>
        </li>
        <li>
            <a data-toggle="tab" class="nav-link"  href="#tab-envio-mex">Envio Mex</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel"  id="tab-index" class="tab-pane active ">
            <?= $this->render('../envio/index',[
                "can"   => $can
                ]) ?>
        </div>
        <div role="tabpanel" id="tab-envio-mex" class="tab-pane">
            <?= $this->render('../envio-mex/index',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
<?php
$this->title = 'Seguimiento';
$this->params['breadcrumbs'][0] = $this->title;
?>
