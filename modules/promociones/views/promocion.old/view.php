<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\promocion\Promocion;
use app\models\envio\Envio;
use app\models\promocion\PromocionComplemento;
use app\models\promocion\PromocionDetalleComplemento;
/* @var $this yii\web\View */

$this->title =  $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Promocion', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$this->params['breadcrumbs'][] = 'Editar';

?>

<div class="promociones-promocion-view">
    <p>
        <?= $can['cancel']?
            Html::a('Cancelar', ['cancel', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas cancelar esta promoción?',
                    'method' => 'post',
                ],
            ]): '' ?>
    </p>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><?= Promocion::$statusList[$model->status] ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Información promocion</h3>
                        </div>
                        <div class="panel-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'nombre',
                                    "fecha_inicia:date",
                                    "fecha_expira:date",
                                ],
                            ]) ?>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Banner</h3>
                        </div>
                        <div class="panel-body">
                            <?php if ($model->banner_imagen): ?>
                                <?= Html::img('@web/uploads/'. $model->banner_imagen, ['alt' => 'Banner', 'class' => 'img-responsive' ]) ?>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Libras requeridas</th>
                                    <th style="text-align: center;">Costo Con/ID</th>
                                    <th style="text-align: center;">Costo Sin/ID</th>
                                    <th style="text-align: center;">Complementos</th>
                                </tr>
                            </thead>
                            <tbody  style="text-align: center;">
                                <?php foreach ($model->promocionDetalles as $key => $item): ?>
                                    <tr>
                                        <td><?= $item->lb_requerida ?></td>
                                        <td><?=$item->costo_libra_code ?></td>
                                        <td><?= $item->costo_libra_sin_code ?></td>
                                        <td>
                                            <table class="table">
                                                <thead>
                                                    <th style="text-align: center;">N° de productos</th>
                                                    <th style="text-align: center;">Tipo de complemento</th>
                                                    <th style="text-align: center;">Aplica categoria</th>
                                                    <th style="text-align: center;">Categoria</th>
                                                    <th style="text-align: center;">Aplica producto</th>
                                                    <th style="text-align: center;">Producto</th>
                                                    <th style="text-align: center;">Valor del articulo</th>
                                                    <th style="text-align: center;">Libra/Gratis</th>
                                                    <th style="text-align: center;">Envio/Gratis</th>
                                                    <th style="text-align: center;">Impuesto/Gratis</th>
                                                    <!--<th style="text-align: center;">Libra/excedente</th>-->
                                                </thead>
                                                <tbody  style="text-align: center;">
                                                    <?php foreach ($item->promocionDetalleComplementos as $key2 => $promocion_complemento): ?>
                                                        <tr>
                                                            <td><?= $promocion_complemento->num_producto  ?></td>
                                                            <td><?= PromocionDetalleComplemento::$complementoList[$promocion_complemento->tipo_complemento] ?></td>
                                                            <td><?= PromocionDetalleComplemento::$tipoList[$promocion_complemento->is_categoria] ?></td>
                                                            <td><?= isset($promocion_complemento->categoria->singular) ? $promocion_complemento->categoria->singular : 'N/A'  ?></td>
                                                            <td><?= PromocionDetalleComplemento::$productoTipoList[ $promocion_complemento->is_producto] ?></td>
                                                            <td><?= isset($promocion_complemento->producto->nombre) ? $promocion_complemento->producto->nombre : 'N/A' ?> </td>
                                                            <td>
                                                                <?php if (isset($promocion_complemento->is_valor_paquete) &&  $promocion_complemento->is_valor_paquete == PromocionComplemento::ON_VALOR_PAQUETE ): ?>
                                                                       <i class='fa fa-check-square-o' aria-hidden='true'></i>
                                                                       <p><?= $promocion_complemento->valor_paquete_aprox  ?></p>
                                                                 <?php else: ?>
                                                                        <i class='fa fa-times' aria-hidden='true'></i>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($promocion_complemento->promocionComplemento->is_lb_free) &&  $promocion_complemento->promocionComplemento->is_lb_free == PromocionComplemento::ON_LBFREE ): ?>
                                                                       <i class='fa fa-check-square-o' aria-hidden='true'></i>
                                                                       <p><?= $promocion_complemento->promocionComplemento->lb_free  ?></p>
                                                                 <?php else: ?>
                                                                        <i class='fa fa-times' aria-hidden='true'></i>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($promocion_complemento->promocionComplemento->is_envio_free) && $promocion_complemento->promocionComplemento->is_envio_free == PromocionComplemento::ON_ENVIO_FREE ): ?>
                                                                       <i class='fa fa-check-square-o' aria-hidden='true'></i>
                                                                 <?php else: ?>
                                                                        <i class='fa fa-times' aria-hidden='true'></i>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <?php if (isset($promocion_complemento->promocionComplemento->cobro_impuesto) && $promocion_complemento->promocionComplemento->cobro_impuesto == PromocionComplemento::ON_COBRO_IMPUESTO ): ?>
                                                                       <i class='fa fa-check-square-o' aria-hidden='true'></i>

                                                                 <?php else: ?>
                                                                        <i class='fa fa-times' aria-hidden='true'></i>
                                                                <?php endif ?>
                                                            </td>
                                                            <?php /* ?>
                                                            <td>
                                                                <?php if (isset($promocion_complemento->promocionComplemento->is_lbexcedente) && $promocion_complemento->promocionComplemento->is_lbexcedente ==  PromocionComplemento::ON_LBFREE ): ?>
                                                                       <i class='fa fa-check-square-o' aria-hidden='true'></i>
                                                                       <p><strong>Libras excedentes: </strong> <?= $promocion_complemento->promocionComplemento->lbexcedente  ?> / <strong>Costo de lb excendete</strong><?= $promocion_complemento->promocionComplemento->costo_libraexcedente  ?> </p>
                                                                 <?php else: ?>
                                                                        <i class='fa fa-times' aria-hidden='true'></i>
                                                                <?php endif ?>
                                                            </td>
                                                            */ ?>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"> <strong><?= $model->is_code_promocional == Promocion::IS_CODE_ON  ? "Aplica codigo "   : "No aplica codigo " ?></strong> <i class="fa fa-barcode"></i></h3>
                </div>
            </div>
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"> <strong> Servicio <?= Envio::$tipoList[$model->tipo_servicio]  ?></strong></h3>
                </div>
            </div>
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"> <strong> Promoción  <?= Promocion::$tipoList[$model->tipo]  ?></strong></h3>
                </div>
            </div>
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"> <strong> Promoción  <?= Promocion::$manualList[$model->is_manual]  ?></strong></h3>
                </div>
            </div>
        	<?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>


