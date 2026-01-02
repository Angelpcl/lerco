<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;


BootstrapTableAsset::register($this);


/* @var $this yii\web\View */

$this->title = 'Clientes';
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
<?= $can['create']?
    Html::a('Nuevo cliente', ['create'], ['class' => 'btn btn-success add']): '' ?>

<?= $can['create']?
    Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i>  Importar clientes', ['import-csv'], ['class' => 'btn btn-success add']) : '' ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a data-toggle="tab" href="#tab-index" class="nav-link active">Clientes</a>
        </li>
        <?php if ($can['create']): ?>
            <li>
                <a data-toggle="tab" href="#tab-vendedor-cliente" class="nav-link">Vendores - Clientes</a>
            </li>
        <?php endif ?>
        <?php if ($can['historicoCliente']): ?>
            <li>
                <a data-toggle="tab" href="#tab-historico-cliente" class="nav-link">Historico de ventas - Clientes</a>
            </li>
        <?php endif ?>
        <?php if ($can['historicoSucursal']): ?>
            <li>
                <a data-toggle="tab" href="#tab-historico-sucursal" class="nav-link">Historico de ventas - Sucursal</a>
            </li>
        <?php endif ?>
        <?php /* ?>
        <?php if ($can['historicoPromocion']): ?>
            <li>
                <a data-toggle="tab" href="#tab-historico-promocion" class="nav-link">Historico de promocion - Clientes</a>
            </li>
        <?php endif ?>
        */?>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-index" class="tab-pane active">
            <?= $this->render('_index',[
                "can"   => $can
                ]) ?>
        </div>
        <?php if ($can['create']): ?>
            <div role="tabpanel" id="tab-vendedor-cliente" class="tab-pane">
                    <?= $this->render('_vendedor_cliente',[
                    "can"   => $can
                    ]) ?>
            </div>
        <?php endif ?>
        <?php if ($can['historicoCliente']): ?>
            <div role="tabpanel" id="tab-historico-cliente" class="tab-pane">
                    <?= $this->render('_historico_cliente',[
                    "can"   => $can
                    ]) ?>
            </div>
        <?php endif ?>
        <?php if ($can['historicoSucursal']): ?>
            <div role="tabpanel" id="tab-historico-sucursal" class="tab-pane">
                    <?= $this->render('_historico_sucursal',[
                    "can"   => $can
                    ]) ?>
            </div>
        <?php endif ?>
        <?php /* ?>
         <?php if ($can['historicoPromocion']): ?>
            <div role="tabpanel" id="tab-historico-promocion" class="tab-pane">
                    <?= $this->render('_historico_promocion',[
                    "can"   => $can
                    ]) ?>
            </div>
        <?php endif ?>
        */?>
    </div>
</div>
