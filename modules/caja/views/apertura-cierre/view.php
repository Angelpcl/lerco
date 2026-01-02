<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\Esys;
use app\models\envio\Envio;
use app\models\cobro\CobroRembolsoEnvio;

/* @var $this yii\web\View */
/* @var $model common\models\ViewSucursal */

$this->title = "CAJA #" . $model->id;

$this->params['breadcrumbs'][] = ['label' => 'Aperturas y Cierres', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="operaciones-aperturas-cierres-view">
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <h3 class="text-main text-normal text-2x mar-no">Apertura de caja</h3>
                                <h5 class="text-uppercase text-muted text-normal"><?= Esys::fecha_en_texto($model->fecha_apertura, true)  ?></h5>
                                <hr class="new-section-xs">
                                <div class="row mar-top">
                                    <div class="col-sm-5">
                                        <div class="text-lg"><p class="text-5x text-thin text-main mar-no">$ <?= $model->cantidad_apertura  ?></p></div>
                                        <p class="text-sm">Monto con el que se aperturo la cuenta</p>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="list-group bg-trans mar-no">
                                            <a class="list-group-item" href="#"><i class="demo-pli-information icon-lg icon-fw"></i> Las cantidades mostradas se relaciona al efectivo en caja</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs"><strong>Comentario: </strong><?= $model->comentario_apertura  ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="col-md-12">
                                <h3 class="text-main text-normal text-2x mar-no">Cierre de caja</h3>
                                <h5 class="text-uppercase text-muted text-normal"><?=$model->fecha_cierre ?  Esys::fecha_en_texto($model->fecha_cierre, true) :NULL ?></h5>
                                <hr class="new-section-xs">
                                <div class="row mar-top">
                                    <div class="col-sm-5">
                                        <div class="text-lg"><p class="text-5x text-thin text-main mar-no">$ <?= $model->cantidad_cierre  ?></p></div>
                                        <p class="text-sm">Monto que registro como cierre de caja</p>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="list-group bg-trans mar-no">
                                            <a class="list-group-item" href="#"><i class="demo-pli-information icon-lg icon-fw"></i> Las cantidades mostradas se relaciona al efectivo en caja</a>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs"><strong>Comentario: </strong><?= $model->comentario_cierre  ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Cobros relacionados con la caja</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered invoice-summary">
                                <thead>
                                    <tr class="bg-trans-dark">
                                        <th class="min-col text-center text-uppercase">Folio</th>
                                        <th class="min-col text-center text-uppercase">Tipo servicio</th>
                                        <th class="min-col text-center text-uppercase">Tipo pago</th>
                                        <th class="min-col text-center text-uppercase">Total</th>
                                        <th class="min-col text-center text-uppercase">Fecha de cobro</th>
                                    </tr>
                                </thead>
                                <tbody  style="text-align: center;">
                                    <?php $totalCobrado = 0  ?>
                                    <?php foreach ($model->getCobroRelaciandos() as $key => $cobro): ?>
                                        <?php $totalCobrado = $totalCobrado + $cobro->cantidad  ?>
                                        <tr>
                                            <td><?= $cobro->envio->folio  ?></td>
                                            <td><?= Envio::$tipoList[$cobro->envio->tipo_envio]  ?> <strong>[<span class="text-mint"><?= Envio::$statusList[$cobro->envio->status]  ?></span>]</strong></td>
                                            <td><strong><?= CobroRembolsoEnvio::$servicioList[$cobro->metodo_pago]  ?></strong></td>
                                            <td>$<?= number_format($cobro->cantidad,2)  ?></td>
                                            <td><?= Esys::fecha_en_texto($cobro->created_at,true)  ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-main text-right"><strong>Total cobrado</strong></td>
                                        <td class="text-center">$<?= number_format($totalCobrado,2)  ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-4">
            <iframe width="100%" class="panel" height="500px" src="<?= Url::to(['imprimir-ticket', 'id' => $model->id ])  ?>"></iframe>
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_100 ? $model->bill_100 :  0 ?></span>
                                    <p>BILLETES DE 100</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_50 ?  $model->bill_50  : 0 ?></span>
                                    <p>BILLETES DE 50</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_20  ? $model->bill_20  : 0 ?></span>
                                    <p>BILLETES DE 20</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_10  ? $model->bill_10  : 0 ?></span>
                                    <p>BILLETES DE 10</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_5 ? $model->bill_5 : 0  ?></span>
                                    <p>BILLETES DE 5</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_2 ? $model->bill_2 : 0  ?></span>
                                    <p>BILLETES DE 2</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->bill_1 ? $model->bill_1 :  0 ?></span>
                                    <p>BILLETES DE 1</p>
                                    <i class="fa fa-money icon-lg"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-primary panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-3x text-thin"><?= $model->change ? $model->change : 0  ?></span>
                                    <p>Monedas / Change</p>
                                    <i class="fa fa-usd icon-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>

