<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use app\models\envio\Envio;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use yii\widgets\ActiveForm;

HighchartsAsset::register($this);

/* @var $this yii\web\View */
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['historico-ventas-json-btt']);
?>


<div class="clientes-historial-ventas">
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-envio-cliente" style=" height: 355px; margin: 0 auto"></div>
    </div>
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
           	<div class="panel-heading">
                <div class="panel-control">
                    <button class="btn reset-form" ><i class="demo-pli-repeat-2"></i></button>
                    <button class="btn collapsed" data-target="#toolbar-panel-collapse" data-toggle="collapse" aria-expanded="false"><i class="demo-pli-arrow-down"></i></button>
                </div>
                <br>
            </div>
            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
                <div class="panel-body pad-btm-15px">
                    <div>
                        <strong class="pad-rgt">Filtrar:</strong>

                        <?= Html::dropDownList('tipo_servicio', null, Envio::$tipoList, ['prompt' => 'Tipo de servicio', 'class' => 'max-width-170px']) ?>

                        <?=  Html::dropDownList('tipo_cliente', null, EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => 'Tipo de cliente', 'class' => 'max-width-170px'])  ?>

                        <?=  Html::dropDownList('asignado_id', null, Cliente::getAsiganadoA(), ['prompt' => 'Tipo de asignado', 'class' => 'max-width-170px'])  ?>
                    </div>
                </div>
            </div>
        </div>
	</div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">

    $item_cliente = [];
    var item_add = function($params){

        $item_cliente =  [];
        $item_cliente_local = [];
        $.each($('.clientes-historial-ventas .bootstrap-table').bootstrapTable('getData'), function(key, value) {
            if ($item_cliente_local.length < 10 ) {
                cliente = [value.nombre_completo, value.monto_total ? parseFloat(value.monto_total) : 0];
                $item_cliente_local[$item_cliente_local.length] = cliente;
            }
        });

        $item_cliente.push($item_cliente_local);
        load();
    }



    $(document).ready(function(){
        var  $filters = $('.btt-toolbar :input'),
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'nombre_completo',
                    title: 'Nombre completo',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'sexo',
                    title: 'sexo',
                    align: 'center',
                    sortable: true,
                    formatter: btf.user.sexo,
                },
                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    sortable: true,
                },
                {
                    field: 'envios_count',
                    title: 'N° de envios',
                    sortable: true,
                },
                {
                    field: 'monto_total',
                    title: 'Total de envio',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'origen',
                    title: 'Origen',
                    sortable: true,
                    formatter: btf.status.origen,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
                },
                {
                    field: 'tipo_cliente',
                    title: 'Tipo de cliente',
                    align: 'center',
                    visible: false
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'updated_at',
                    title: 'Modificado',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'updated_by',
                    title: 'Modificado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.updated_by,
                },
            ],
            params = {
                id      : 'clienteHistorico',
                element : '.clientes-historial-ventas',

                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    sortName    : 'envios_count',
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onLoadSuccess : function(params){
                        item_add(params);
                    },
                }
            };


        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });


</script>


<script>
    var load = function(){
        Highcharts.chart('container-envio-cliente', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Historial de envios realizados'
            },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total en envios'
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Total  <b>{point.y:.1f} USD</b>'
            },
            series: [{
                name: 'Population',
                data: $item_cliente[0],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.1f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
    }

</script>
