<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\promocion\PromocionComplemento;
use app\models\promocion\PromocionAnexoCategoria;
use app\models\promocion\PromocionDetalleComplemento;
?>

<div class="fade modal " id="modal-promocion-show"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg" style="width: 85%">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-edit mar-rgt-5px icon-lg"></i> Detalles de la promoción</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Libras requeridas</th>
                                <th style="text-align: center;">Anexos</th>
                                <th style="text-align: center;">Con/ID</th>
                                <th style="text-align: center;">Sin/ID</th>
                                <th style="text-align: center;">Complementos</th>
                            </tr>
                        </thead>
                        <tbody  style="text-align: center;">
                            <?php foreach ($model->promocion->promocionDetalles as $key => $item): ?>
                                <tr class="<?= $model->promocion_detalle_id == $item->id ? 'danger' :''  ?>">
                                    <td><?= $item->lb_requerida ?></td>
                                    <td>
                                        <table class="table">
                                            <tbody  style="text-align: center;">
                                                <tr>
                                                <?php foreach ($item->promocionDetalleAnexos as $key => $anexo): ?>
                                                    <td>
                                                        <?php foreach ($anexo->promocionAnexoCategorias as $key => $promocionAnexoCategorias): ?>
                                                            <small>
                                                                <strong>
                                                                <ul>
                                                                    <li><?= $promocionAnexoCategorias->is_categoria == PromocionAnexoCategoria::IS_CATEGORIA_ON ?  $promocionAnexoCategorias->categoria->singular : 'Todas las categorias' ?></li>
                                                                </ul>
                                                                </strong>
                                                            </small>
                                                        <?php endforeach ?>
                                                        <small style="display: block;">
                                                            (<?= $anexo->lb_free  ?> lb)
                                                        </small>
                                                    </td>
                                                <?php endforeach ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td><?=$item->costo_libra_code ?></td>
                                    <td><?= $item->costo_libra_sin_code ?></td>
                                    <td>
                                        <table class="table">
                                            <thead>
                                                <th style="text-align: center;">N° PROD.</th>
                                                <th style="text-align: center;">COMPLEMENTO</th>
                                                <th style="text-align: center;">CATEGORIA</th>
                                                <th style="text-align: center;">C. NOMBRE</th>
                                                <th style="text-align: center;">PRODCTO</th>
                                                <th style="text-align: center;">P. NOMBRE</th>
                                                <th style="text-align: center;">V. ARTICULO</th>
                                                <th style="text-align: center;">LB/FREE</th>
                                                <th style="text-align: center;">ENVIO/FREE</th>
                                                <th style="text-align: center;">IMPUESTO/FREE</th>
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
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

