<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\viaje\Viaje;
use app\assets\BootboxAsset;
use app\models\movimiento\MovimientoPaquete;
BootboxAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Reporte de cuentas';
$this->params['breadcrumbs'][] = $this->title;

$bttUrlView   = Url::to(['/reportes/reporte/cuentas?id=']);

?>

<div class="reporte-cuenta-index">
    <div class="row">
        <div class="col-sm-12">
            <div class="btt-toolbar">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <h2 style="display: inline-block;">REPORTE DE CUENTA   : </h2>
                        <?= Html::dropDownList('viaje_id', isset($model->id) ? $model->id :null, Viaje::getViajeTranscursoTierra(), ['prompt' => 'SELECCIONA VIAJE', 'class' => 'form-control m-b', "style" => 'display: inline-block;', 'id' => 'select_viaje_id']) ?>
                        <?=
                            Html::a('GENERAR REPORTE', null, [
                                'class' => 'btn m-b btn-lg btn-danger',
                                'id' => 'btn_reporte_generar',
                            ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($model)): ?>

        <div class="row">
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>TOTAL PAQUETES</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h1 class="no-margins"><?= count($model->viajeDetalles)  ?></h1>
                        <small>TOTAL DE PAQUETES</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>PQ ENTREGADOS</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h1 class="no-margins"><?= Viaje::getPQEntregados($model->id) ?></h1>
                        <small>PAQUETES</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>PQ REPARTO</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h1 class="no-margins"><?= Viaje::getPQReparto($model->id) ?></h1>
                        <small>PAQUETES</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>PQ BODEGA</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h1 class="no-margins"><?= Viaje::getPQBodega($model->id) ?></h1>
                        <small>PAQUETES</small>
                    </div>
                </div>
            </div>
        </div>


        <div class="tabs-container">
            <ul class="nav nav-tabs" role="tablist">
                <li>
                    <a data-toggle="tab" class="nav-link active" href="#tab-sucursal-envia">SUCURSALES QUE ENVIA</a>
                </li>
                <li>
                    <a data-toggle="tab" class="nav-link" href="#tab-sucursal-recibe">SUCURSALES RECIBE</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel"  id="tab-sucursal-envia" class="tab-pane active ">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="ibox ">
                                <div class="ibox-content " style="overflow-y: scroll;height:  500px;">
                                    <?=
                                        Html::a('DESCARGAR REPORTE', null, [
                                            'class' => 'btn m-b btn-lg btn-primary float-right',
                                            'id' => 'btn_reporte_cuenta',
                                        ]);
                                    ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">SUCURSAL</th>
                                                <th style="text-align: center;">CANTIDAD PQ</th>
                                                <th style="text-align: center;">PESO TOTAL APROX.</th>
                                                <th style="text-align: center;">COMISIÓN ENVIO SUCURSAL</th>
                                                <th style="text-align: center;">COMISIÓN DE ASEGURANZA SUCURSAL</th>
                                                <th style="text-align: center;">TOTAL COMISIÓN SUCURSAL</th>
                                                <th style="text-align: center;">COMISIÓN ENVIO DIMAS</th>
                                                <th style="text-align: center;">COMISIÓN DE ASEGURANZA DIMAS</th>
                                                <th style="text-align: center;">COMISIÓN TOTAL DIMAS</th>
                                                <th style="text-align: center;">ACCION</th>
                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center; ">
                                            <?php $count = 0; ?>
                                            <?php foreach (Sucursal::getItemsUsa() as $sucursal_id => $sucursal): ?>
                                                <?php foreach (Viaje::getSucursalEnviaPaquete($model->id, $sucursal_id) as $key => $paquete): ?>
                                                    <?php  $count++  ?>
                                                    <tr>
                                                        <td><?= $count  ?></td>
                                                        <td><?= $paquete["sucursal_emisor"]  ?></td>
                                                        <td><?= $paquete["paquete_count"]  ?></td>
                                                        <td><?= $paquete["peso_unitario"]  ?></td>

                                                        <td><?= number_format($paquete["comision_envio"] ,2) ?></td>
                                                        <td><?= number_format($paquete["comision_aseguranza"] ,2) ?></td>
                                                        <td><?= number_format($paquete["total_comision"] ,2) ?></td>
                                                        <td><?= number_format($paquete["comision_envio_dimas"] ,2) ?></td>
                                                        <td><?= number_format($paquete["comision_aseguranza_dimas"] ,2) ?></td>
                                                        <td><?= number_format($paquete["total_comision_dimas"] ,2) ?></td>
                                                        <td>
                                                        <?=
                                                            Html::a('PAQUETES', null, [
                                                                'class' => 'btn_show_paquetes btn m-b btn-xs btn-primary ',
                                                                'onclick' => 'show_paquete('. $model->id .','. $sucursal_id .')'
                                                            ]);
                                                        ?>

                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div role="tabpanel"  id="tab-sucursal-recibe" class="tab-pane">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="ibox ">
                                <div class="ibox-content " style="overflow-y: scroll;height:  500px;">
                                    <?=
                                        Html::a('DESCARGAR REPORTE', null, [
                                            'class' => 'btn m-b btn-lg btn-primary float-right',
                                            'id' => 'btn_reporte_cuenta',
                                        ]);
                                    ?>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">#</th>
                                                <th style="text-align: center;">SUCURSAL</th>
                                                <th style="text-align: center;">CANTIDAD PQ</th>
                                                <th style="text-align: center;">PESO TOTAL APROX.</th>
                                                <th style="text-align: center;">TOTAL COBRADO</th>

                                            </tr>
                                        </thead>
                                        <tbody  style="text-align: center; ">
                                            <?php $count = 0; ?>
                                            <?php foreach (Sucursal::getItemsMexico() as $sucursal_id => $sucursal): ?>
                                                <?php foreach (Viaje::getSucursalPaquete($model->id, $sucursal_id) as $key => $paquete): ?>
                                                    <?php  $count++  ?>
                                                    <tr>
                                                        <td><?= $count  ?></td>
                                                        <td><?= $paquete["sucursal_receptor"]  ?></td>
                                                        <td><?= $paquete["paquete_count"]  ?></td>
                                                        <td><?= $paquete["peso_unitario"]  ?></td>
                                                        <td><?= number_format($paquete["monto_pagado"] ,2) ?></td>

                                                    </tr>
                                                <?php endforeach ?>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<div class="fade modal inmodal " id="modal-show-paquete"  tabindex="-1" role="dialog" aria-labelledby="modal-create-label" >
    <div class="modal-dialog modal-lg" style="width: 100%;max-width: 85%;">
        <div class="modal-content">
            <!--Modal header-->
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">PAQUETES</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table-striped table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">PAQUETE</th>
                                    <th class="text-center">PRODUCTO</th>
                                    <th class="text-center">PESO UNITARIO </th>
                                    <th class="text-center">TOTAL DE ENVIO</th>
                                    <th class="text-center">TOTAL UNITARIO APROX.</th>
                                    <th class="text-center">ESTATUS</th>
                                </tr>
                            </thead>
                            <tbody class="container_paquete">
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    var $reporte_generar        = $('#btn_reporte_generar'),
        tipo_movimiento_array   = JSON.parse('<?= json_encode(MovimientoPaquete::$tipoLaxTierList)  ?>'),
        $reporte_cuenta         = $('#btn_reporte_cuenta');

    $reporte_generar.click(function(){
        if ($('#select_viaje_id').val()) {
            window.location.href = '<?= $bttUrlView ?>' + $('#select_viaje_id').val();
        }else{
             bootbox.alert("¡ Debes seleccionar un viaje para poder continuar, intenta nuevamente !");
             return false;
        }
    });

    $reporte_cuenta.click(function(event){
        event.preventDefault();
        window.open('<?= Url::to(['reporte-cuentas-sucursal']) ?>?id=' + $('#select_viaje_id').val(),
            'imprimir',
            'width=600,height=500');
    });



    var show_paquete = function($viaje_id,$sucursal_id){
        $('#modal-show-paquete').modal('show');
        content_html = "";
        $(".container_paquete").html(null);
        $.get("<?= Url::to(["get-sucursal-paquete"]) ?>",{ viaje_id: $viaje_id , sucursal_id: $sucursal_id },function($response){
            if ($response.code == 202) {

                $.each($response.result,function(key,paquete){
                    content_html += "<tr>"+
                        "<td class='text-center'>"+ (key + 1) +"</td>" +
                        "<td class='text-center'>"+ paquete.tracked +"</td>" +
                        "<td class='text-center'>"+ paquete.producto +"</td>" +
                        "<td class='text-center'>"+ paquete.peso_unitario +"</td>" +
                        "<td class='text-center'>"+ paquete.total +"</td>" +
                        "<td class='text-center'>"+ paquete.total_unitario +"</td>" +
                        "<td class='text-center'><span class='label "+ ( paquete.tipo_movimiento_top == 60 ? 'label-success' : ( paquete.tipo_movimiento_top == 20 ? 'label-danger' : 'label-warning') ) +"'>"+ tipo_movimiento_array[paquete.tipo_movimiento_top] +"</span></td>" +
                    "</tr>"
                });
            }
            $(".container_paquete").html(content_html);
        });
    }
</script>