<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\HighchartsAsset;
use app\models\Esys;
use app\models\envio\Envio;
use app\models\envio\EnvioDetalle;
use app\assets\BootstrapTableAsset;


BootstrapTableAsset::register($this);
HighchartsAsset::register($this);

$this->title = $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Historico de ventas  - Sucursal', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nombre;

$bttUrl       = Url::to(['sucursal-historico-json-btt']);

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$envioDetalles = $model->envioDetalles;

$sucursalEnvia  = [];
$envioTipoLax   = 0;
$envioTipoTie   = 0;
$totalEnvios    = 0;
$totalPeso      = 0;

$result_actual = [];

foreach ($model->envioMesGroup as $key => $group) {
    $ano_actual = date('Y');
    $item = [
        "mes" => $group["mes"],
        "cantidad"  => $group["num_paquetes"],
    ];
    if ( $ano_actual == $group["ano"])
        array_push($result_actual, $item);
}



foreach ($envioDetalles as $key => $item) {
    $is_add = true;
    $add_sucursal = [
        "id" => $item->envio->sucursalEmisor->id,
        "nombre" => $item->envio->sucursalEmisor->nombre,
        "pz" => $item->cantidad,
    ];

    foreach ($sucursalEnvia as $key => $sucursal) {
        if ($item->envio->sucursalEmisor->id == $sucursal["id"]) {
            $is_add = false;
            $sucursalEnvia[$key]["pz"] = intval($sucursal["pz"]) + intval($item->cantidad);
        }
    }
    if ($is_add)
        array_push($sucursalEnvia, $add_sucursal);

    if ($item->envio->tipo_envio == Envio::TIPO_ENVIO_TIERRA )
        $envioTipoTie = $envioTipoTie + 1;

    if ($item->envio->tipo_envio == Envio::TIPO_ENVIO_LAX )
        $envioTipoLax = $envioTipoLax + 1;

    $totalPeso = $totalPeso + $item->peso;

    $totalEnvios++;
}


?>
<div class="cliente-historico-ventas-sucursal-view">
    <div class="row">
        <div class="col-lg-7">
            <!--Network Line Chart-->
            <!--===================================================-->
            <div id="demo-panel-network" class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Rendimiento</h3>
                </div>
                <!--chart placeholder-->
                <div class="pad-all">
                    <div class="pad-all" style="margin-bottom: 40px;">
                        <div id="container-historico-envios" style=" height: 355px; margin: 0 auto"></div>

                    </div>
                </div>

                <!--Chart information-->
                <div class="panel-body">

                    <div class="row">
                        <div class="col-lg-8">
                            <p class="text-semibold text-uppercase text-main">N° de Paquetes <i class="fa fa-cubes"></i></p>
                            <div class="row">
                                <div class="col-xs-5">
                                    <div class="media">
                                        <div class="media-left">
                                            <span class="text-3x text-thin text-main"><?= $model->numpzEntregado ?  $model->numpzEntregado : 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="pad-rgt">
                                <p class="text-semibold text-uppercase text-main">NOTA</p>
                                <p class="text-muted mar-top">La información obtenida se relaciona a la cantidad de envios generados hacia la sucursal</p>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <p class="text-uppercase text-semibold text-main">Sucursal que envía</p>

                            <ul class="list-unstyled">
                                <?php foreach ($sucursalEnvia as $key => $sucursal): ?>
                                    <li class="pad-btm">
                                        <div class="clearfix">
                                            <p class="pull-left mar-no"><?= $sucursal["nombre"] ?> / <?= $sucursal["pz"] ?> <i class="fa fa-cubes"></i> </p>
                                            <p class="pull-right mar-no"><?= round(($sucursal["pz"] *  100) / $model->numpzEntregado,2)  ?>%</p>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar progress-bar-info" style="width: <?= round(($sucursal["pz"] *  100) / $model->numpzEntregado,2)   ?>%;">
                                                <span class="sr-only"><?= round(($sucursal["pz"] *  100) / $model->numpzEntregado,2)  ?>% Complete</span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach ?>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--===================================================-->
            <!--End network line chart-->
        </div>
        <div class="col-lg-5">
            <div class="row">
                <div class="col-sm-12">
                    <figure class="highcharts-figure">
                        <div id="container-sucursal"></div>
                    </figure>
                </div>
            </div>
            <!--Extra Small Weather Widget-->
            <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
            <div class="panel">
                <div class="panel-body text-center clearfix">
                    <div class="col-sm-4 pad-top">
                        <div class="text-lg">
                            <p class="text-5x text-thin text-main"><?= $totalPeso ?></p>
                        </div>
                        <p class="text-sm text-bold text-uppercase">TOTAL (LB)</p>
                    </div>
                    <div class="col-sm-8">
                        <p class="text-xs">Total de envis relacionados con la sucursal.</p>
                        <ul class="list-unstyled text-center bord-top pad-top mar-no row">
                            <li class="col-xs-4">
                                <span class="text-lg text-semibold text-main"><?= $envioTipoLax ?></span>
                                <p class="text-sm text-muted mar-no">Envios Lax</p>
                            </li>
                            <li class="col-xs-4">
                                <span class="text-lg text-semibold text-main"><?= $envioTipoTie ?></span>
                                <p class="text-sm text-muted mar-no">Envio Tierra</p>
                            </li>
                            <li class="col-xs-4">
                                <span class="text-lg text-semibold text-main"><?= $totalEnvios ?></span>
                                <p class="text-sm text-muted mar-no">Total de envios</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Paquetes relacionados</h3>
                </div>
                <div class="btt-toolbar">
                    <?= Html::hiddenInput('sucursal_id', $model->id) ?>
                </div>
                <table class="bootstrap-table"></table>
                <!--Data Table-->
                <!--===================================================-->

                <?php /* ?>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Folio</th>
                                    <th class="text-center">Trackend</th>
                                    <th class="text-center">Cliente E.</th>
                                    <th class="text-center">Peso</th>
                                    <th class="text-center">Cantidad (PZ)</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Estatus del envio</th>
                                    <th class="text-center">Estatus del paquete</th>
                                    <th class="text-center">Fecha</th>
                                    <?php if ($model->is_reenvio == 10): ?>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Municipio</th>
                                        <th class="text-center">Direccion</th>
                                    <?php endif ?>
                                </tr>
                            </thead>
                            <?php foreach ($envioDetalles as $key => $paquete): ?>
                                <tbody>
                                    <tr>
                                        <td class="text-center"><a href="#" class="btn-link"> <?=  $paquete->envio->folio ?></a></td>
                                        <td class="text-center"><?= $paquete->tracked ?></td>
                                        <td class="text-center"><?= isset($paquete->envio->clienteEmisor->nombreCompleto) ? $paquete->envio->clienteEmisor->nombreCompleto : ''?></td>
                                        <td class="text-center"><span class="text-muted"><?= $paquete->peso ?></span></td>
                                        <td class="text-center"><?= $paquete->cantidad ?></td>
                                        <td class="text-center"><?= number_format($paquete->envio->total,2) ?></td>
                                        <td class="text-center"><strong class="text-mint"><?= Envio::$statusList[$paquete->envio->status] ?></strong></td>
                                        <td class="text-center"><strong class="text-mint"><?= EnvioDetalle::$statusList[$paquete->status] ?></strong></td>
                                        <td class="text-center"><?= Esys::fecha_en_texto($paquete->envio->created_at) ?></td>
                                        <td class="text-center"><?= isset($paquete->direccion->estado->singular) ? $paquete->direccion->estado->singular : 'N/A'  ?></td>
                                        <td class="text-center"><?= isset($paquete->direccion->municipio->singular) ? $paquete->direccion->municipio->singular : 'N/A' ?></td>
                                        <td class="text-center"><?= isset($paquete->direccion->direccion) ? $paquete->direccion->direccion : 'N/A' ?></td>
                                    </tr>
                                </tbody>
                            <?php endforeach ?>
                        </table>
                    </div>
                    <hr class="new-section-xs">
                </div>
                */?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){

         var  $filters = $('.btt-toolbar :input'),
            columns = [
                {
                    field: 'folio',
                    title: 'Folio',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'tracked',
                    title: 'Trackend',
                    sortable: true,

                },
                {
                    field: 'nombre_emisor',
                    title: 'Cliente E.',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'peso',
                    title: 'Peso',
                    align: 'center',
                    sortable: true,
                    switchable:false,


                },
                {
                    field: 'cantidad',
                    title: 'Cantidad (PZ)',
                    align: 'center',
                    sortable: true,
                    switchable:false,


                },
                {
                    field: 'total',
                    title: 'Total',
                    sortable: true,
                    switchable:false,
                    formatter: btf.conta.money,
                },
                {
                    field: 'status_envio',
                    title: 'Estatus del envio',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                    formatter: btf.status.opt_envio,

                },
                {
                    field: 'status_paquete',
                    title: 'Estatus del paquete',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                    formatter: btf.status.opt_o,

                },
                {
                    field: 'fecha',
                    title: 'Fecha',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                    formatter: btf.time.date,

                },
                {
                    field: 'estado',
                    title: 'Estado',
                    align: 'center',
                    visible: false
                },
                {
                    field: 'municipio',
                    title: 'Municipio',
                    align: 'center',
                    visible: false
                },
                {
                    field: 'direccion',
                    title: 'Dirección',
                    visible: false
                },


            ],
            params = {
                id      : 'ventas-sucursal',
                element : '.cliente-historico-ventas-sucursal-view',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    cookie  : false,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

</script>

<script>
    // Make monochrome colors

    var num_envio_lax = <?= $envioTipoLax ?>,
        num_envio_tie = <?= $envioTipoTie ?>,
        is_array_paquetes = JSON.parse('<?= json_encode($result_actual)  ?>'),
        year             = new Date().getFullYear(),
        listpaquetes  = [];


    $(document).ready(function(){

        load_data();
        // Build the chart
        Highcharts.chart('container-sucursal', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'TIPO SE SERVICIO'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',

                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                        distance: -50,
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 4
                        }
                    }
                }
            },
            series: [{
                name: 'Porcentaje',
                data: [
                    { name: 'TIERRA', y: num_envio_tie },
                    { name: 'LAX', y: num_envio_lax },

                ]
            }]
        });

        Highcharts.chart('container-historico-envios', {
            chart: {
                type: 'areaspline'
            },
            title: {
                text: 'Envios Tierra y Lax'
            },
            subtitle: {
                text: 'Numero de envios recibidos por los servios'
            },

            legend: {
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'top',
                x: 150,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF'
            },
            xAxis: {
                categories: ["Ene " + year,"Feb " + year,"Mar " + year,"Apr " + year,"May " + year,"Jun " + year,"Jul " + year,"Agos " + year,"Sep " + year,"Oct " + year,"Nov " + year,"Dec " + year],
                 plotBands: [{ // visualize the weekend
                    from: 4.5,
                    to: 6.5,
                    color: 'rgba(68, 170, 213, .2)'
                }]
            },
            yAxis: {
                title: {
                  text: 'Cantidad de envios'
                },

            },
            tooltip: {
              pointFormat: '{series.name} envios realizados:  <b>{point.y:,.0f}</b>'
            },

            credits: {
                enabled: false
            },
            plotOptions: {
                areaspline: {
                    fillOpacity: 0.5
                }
            },

            series: [{
                name: 'Paquetes',
                data: listpaquetes,
            }]
        });
  });

    var load_data = function(){
        $.each(is_array_paquetes,function(key, item){
            listpaquetes.push(parseInt(item.cantidad));
        });
    }
</script>
