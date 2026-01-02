<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroRembolsoEnvio;

BootstrapTableAsset::register($this);
HighchartsAsset::register($this);


/* @var $this yii\web\View */

$this->title = 'Reporte de rembolsos';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['cobros-json-btt']);
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
?>

<div class="reportes-reembolso-index">
    <div class="btt-toolbar">
       <?= Html::hiddenInput('tipo', CobroRembolsoEnvio::TIPO_DEVOLUCION) ?>
        <div class="row">
            <div class="col-sm-9">
                <div class="panel mar-btm-5px">
                   <div class="panel-heading">
                        <div class="panel-control">
                            <button class="btn reset-form" ><i class="demo-pli-repeat-2"></i></button>
                            <button class="btn collapsed" data-target="#toolbar-panel-collapse" data-toggle="collapse" aria-expanded="false"><i class="demo-pli-arrow-down"></i></button>
                        </div>
                        <br>
                        <div class="DateRangePicker   kv-drp-dropdown  col-sm-5">
                            <?= DateRangePicker::widget([
                                'name'           => 'date_range',
                                //'presetDropdown' => true,
                                'hideInput'      => true,
                                'useWithAddon'   => true,
                                'convertFormat'  => true,
                                'startAttribute' => 'from_date',
                                'endAttribute' => 'to_date',
                                'startInputOptions' => ['value' => '2019-01-01'],
                                'endInputOptions' => ['value' => '2019-12-31'],
                                'pluginOptions'  => [
                                     'timePicker'=>true,
                                    'locale'=>[
                                        'format'=>'Y-m-d h:i A'
                                    ],
                                    /*'locale' => [
                                        'format'    => 'Y-m-d',
                                        'separator' => ' - ',
                                    ],*/
                                    'opens' => 'left',
                                    "autoApply" => true,
                                ],
                            ])
                            ?>
                        </div>
                    </div>
                    <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
                        <div class="panel-body pad-btm-15px">
                            <div>
                                <strong class="pad-rgt">Filtrar:</strong>

                                <?= Html::dropDownList('tipo_envio', null,Envio::$tipoList, ['class' => 'max-width-170px', 'prompt'=> 'Tipo de servicio']) ?>

                                <?= Html::dropDownList('sucursal_id', null,Sucursal::getItems(), ['class' => 'max-width-170px', 'prompt'=> 'Sucursal']) ?>

                            </div>
                             <div class="mar-top">
                                <strong class="pad-rgt">Agrupar:</strong>
                            </div>

                            <?= Html::checkbox("agrupar[sucursal]", false, ["id" => "agrupar-sucursal", "class" => "magic-checkbox"]) ?>
                            <?= Html::label("Agrupar por sucursal", "agrupar-sucursal", ["style" => "display:inline"]) ?>

                            <?= Html::checkbox("agrupar[tipo_envio]", false, ["id" => "agrupar-tipo_envio", "class" => "magic-checkbox"]) ?>
                            <?= Html::label("Agrupar por tipo de servicio", "agrupar-tipo_envio", ["style" => "display:inline"]) ?>

                            <?= Html::checkbox("agrupar[metodo_pago]", false, ["id" => "agrupar-metodo_pago", "class" => "magic-checkbox"]) ?>
                            <?= Html::label("Agrupar por metodo de pago", "agrupar-metodo_pago", ["style" => "display:inline"]) ?>

                            <?= Html::checkbox("agrupar[viaje]", false, ["id" => "agrupar-viaje", "class" => "magic-checkbox"]) ?>
                            <?= Html::label("Agrupar por viaje", "agrupar-viaje", ["style" => "display:inline"]) ?>

                            <?= Html::checkbox("show[viaje]", false, ["id" => "show-viaje", "class" => "magic-checkbox"]) ?>
                            <?= Html::label("Mostrar viaje", "show-viaje", ["style" => "display:inline"]) ?>



                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 ">
                <div class="panel panel-mint panel-colorful">
                    <div class="pad-all text-center">
                        <small><span class="text-3x text-thin lbl_total_cobro_realizado">$ 0</span></small>
                        <p>Total reembolso</p>
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>




    <table class="bootstrap-table">

    </table>
<?php /* ?>
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-envio" style=" height: 355px; margin: 0 auto"></div>
    </div>
*/?>
</div>

<script type="text/javascript">
    var $lbl_total_cobro_realizado   = $('.lbl_total_cobro_realizado'),
        total_gasto_realizado       = 0;

    var sum_cobro_total = function(){
        total_gasto_realizado = 0;
        $.each($('.bootstrap-table').bootstrapTable('getData'), function(key, value) {
            total_gasto_realizado = total_gasto_realizado + parseFloat(value.cantidad);
        });
        load_price_panel();
    }

    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
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
                    field: 'envio',
                    title: 'Envios',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'folio',
                    title: 'Folio',
                    sortable: true,
                },
                {
                    field: 'sucursal_emisor_nombre',
                    title: 'Sucursal Emisor',
                    sortable: true,
                },
                {
                    field: 'nombre_emisor',
                    title: 'Cliente Emisor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo_envio',
                    title: 'Tipo de envio',
                    align: 'center',
                    formatter: btf.tipo.tipo_envio,
                    sortable: true,
                },
                {
                    field: 'subtotal',
                    title: 'SubTotal',
                    align: 'right',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'impuesto',
                    title: 'Impuesto',
                    align: 'right',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'total',
                    title: 'Total',
                    align: 'right',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'cantidad',
                    title: 'Cobro realizado',
                    align: 'right',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'metodo_pago',
                    title: 'Metodo de pago',
                    sortable: true,
                    align: 'center',
                    formatter: btf.tipo.tipo_metodo_pago,

                },
                {
                    field: 'tipo',
                    title: 'Tipo',
                    sortable: true,
                    align: 'center',
                    formatter: btf.tipo.tipo_pago,

                },
                {
                    field: 'viaje_id',
                    title: 'Viaje N#',
                    align: 'center',
                    sortable: true,
                    visible: false,
                },
                {
                    field: 'viaje_fecha_salida',
                    title: 'Fecha de viaje',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'viaje_tipo',
                    title: 'Tipo de viaje',
                    align: 'center',
                    formatter: btf.tipo.tipo_envio,
                    sortable: true,
                    visible: false,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    visible: false
                },
                {
                    field: 'created_at',
                    title: 'Cobrado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'Cobrado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'reporte',
                element : '.reportes-reembolso-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onLoadSuccess : function(params){
                        sum_cobro_total();
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        //load();
    });

var load = function(){
    Highcharts.chart('container-envio', {
      chart: {
        type: 'area'
      },
      title: {
        text: 'Envios Tierra, Lax y Mex'
      },
      subtitle: {
        text: 'Numero de envios realizados por los servios'
      },
      xAxis: {
        categories: ["Ene","Feb","Mar","Apr","May","Jun","Jul","Agos","Sep","Oct","Nov","Dec"],
      },
      yAxis: {
        title: {
          text: 'Cantidad de envios'
        },

      },
      tooltip: {
        pointFormat: '{series.name} envios realizados:  <b>{point.y:,.0f}</b>'
      },
      plotOptions: {
        area: {
          marker: {
            enabled: false,
            symbol: 'circle',
            radius: 2,
            states: {
              hover: {
                enabled: true
              }
            }
          }
        }
      },
      series: [{
        name: 'Envio Tierra',
        data: [
          800, 600, 755, 550, 751, 752, 300, 450, 523, 632,782,495

        ]
      }, {
        name: 'Envio Lax',
        data: [950, 750, 652, 852, 962, 753, 882, 1120, 951, 752,122,956

        ]
      },{
        name: 'Envio Mex',
        data: [0,0, 963, 1200, 1800, 753, 1666, 1455, 2101,1556,800,1500

        ]
      }]
    });
}

var load_price_panel = function(){
    $lbl_total_cobro_realizado.html(btf.conta.money(total_gasto_realizado));
}
</script>
