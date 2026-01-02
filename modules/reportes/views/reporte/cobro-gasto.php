<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
HighchartsAsset::register($this);


/* @var $this yii\web\View */

$this->title = 'Reporte de INGRESOS / EGRESOS';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['cobros-json-btt']);
$bttUrlPago   = Url::to(['/pagos/pago-gasto/pagos-json-btt']);

?>

<div class="row">
    <div class="col-sm-3">
        <div class="btt-toolbar filter-top">
            <div class="panel mar-btm-5px">
               <div class="panel-heading">
                    <div class="DateRangePicker   kv-drp-dropdown ">
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
            </div>
        </div>
    </div>
    <div class="col-sm-9">
        <div class="panel">
            <div class="panel-body text-center clearfix">
                <div class="col-sm-4 pad-top">
                    <div class="text-lg">
                        <p class="text-5x text-thin text-main lbl_utilidad_text">$ 0</p>
                    </div>
                    <p class="text-sm text-bold text-uppercase">UTILIDAD BRUTA</p>
                </div>
                <div class="col-sm-8">
                    <ul class="list-unstyled text-center bord-top pad-top mar-no row">
                        <li class="col-xs-4">
                            <span class="text-lg text-semibold text-main lbl_ingreso_text">$ 0</span>
                            <p class="text-sm text-muted mar-no">INGRESOS</p>
                        </li>
                        <li class="col-xs-4">
                            <span class="text-lg text-semibold text-main lbl_egreso_text">$ 0</span>
                            <p class="text-sm text-muted mar-no">EGRESOS</p>
                        </li>
                        <li class="col-xs-4">
                            <span class="text-lg text-semibold text-main lbl_reembolso_text">$ 0</span>
                            <p class="text-sm text-muted mar-no">REEMBOLSOS</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="reportes-cobros-index">
            <div class="btt-toolbar">
                <?= Html::hiddenInput('tipo', CobroRembolsoEnvio::TIPO_COBRO) ?>
            </div>
            <h3>Ingresos</h3>
            <table class="bootstrap-table">
            </table>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="pagos-pago-index">
            <h3>Egresos</h3>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="reportes-reembolso-index">
            <div class="btt-toolbar">
                <?= Html::hiddenInput('tipo', CobroRembolsoEnvio::TIPO_DEVOLUCION) ?>
            </div>
            <h3>Reembolso</h3>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>



<script type="text/javascript">
    var total_cobro_realizado = 0;
    var total_gasto_realizado = 0;
    var total_reembolso_realizado = 0;
    var $lbl_utilidad_text  = $('.lbl_utilidad_text'),
        $lbl_egreso_text    = $('.lbl_egreso_text'),
        $lbl_reembolso_text = $('.lbl_reembolso_text'),
        $lbl_ingreso_text   = $('.lbl_ingreso_text');
    var sum_ingreso_total   = function(){
        total_cobro_realizado = 0;
        $.each($('.reportes-cobros-index .bootstrap-table').bootstrapTable('getData'), function(key, value) {
            total_cobro_realizado =  total_cobro_realizado + parseFloat(value.cantidad);
            //$('.bootstrap-table').bootstrapTable('remove', {field: "state",values : "97327"});
        });
        load_price_panel();
    }

    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
            fistLoadCobro = true,
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
                id      : 'cobro-gasto_cobro',
                element : '.reportes-cobros-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    search  : false,
                    showExport  : false,
                    showToggle  : false,
                    showRefresh  : false,
                    onLoadSuccess : function(params){
                        //if(fistLoadCobro == true){

                            sum_ingreso_total();
                            //fistLoadCobro = false;
                        //}

                    },

                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        //load();
    });

</script>

<script type="text/javascript">
    var sum_gasto_total = function(){
        total_gasto_realizado = 0;
        $.each($('.pagos-pago-index .bootstrap-table').bootstrapTable('getData'), function(key, value) {
            total_gasto_realizado = total_gasto_realizado + parseFloat(value.monto);
        });
        load_price_panel();
    }
    $(document).ready(function(){

        var $table_pagos_filters    = $('.pagos-pago-index .btt-toolbar ');
        var $table_cobros_filters   = $('.reportes-cobros-index .btt-toolbar ');

        var  $filters = $(' .btt-toolbar :input'),
        fistLoadGasto = true;

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
                    field: 'concepto',
                    title: 'Concepto',
                    sortable: true,
                },
                {
                    field: 'monto',
                    title: 'Monto',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'fecha_pago',
                    title: 'Fecha de pago',
                    sortable: true,
                    formatter: btf.time.date,
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
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'cobro-gasto_gasto',
                element : '.pagos-pago-index',
                url     : '<?= $bttUrlPago ?>',

                bootstrapTable : {
                    columns : columns,
                    search  : false,
                    showExport  : false,
                    showToggle  : false,
                    showRefresh  : false,
                    onLoadSuccess : function(params){
                        //if(fistLoadGasto == true){

                            sum_gasto_total();
                            //fistLoadGasto = false;
                        //}

                    },

                }
            };

        bttBuilder2 = new MyBttBuilder(params);
        bttBuilder2.refresh();


        $table_cobros_filters.change(function(){
            bttBuilder2.refresh();
        });
    });


var load_price_panel = function(){
    $lbl_egreso_text.html(btf.conta.money(total_gasto_realizado));
    $lbl_ingreso_text.html(btf.conta.money(total_cobro_realizado));
    $lbl_reembolso_text.html(btf.conta.money(total_reembolso_realizado));
    $lbl_utilidad_text.html(btf.conta.money((total_cobro_realizado - total_gasto_realizado) - total_reembolso_realizado ));

}
</script>

<script>
    var sum_reembolso_total   = function(){
        total_reembolso_realizado = 0;
        $.each($('.reportes-reembolso-index .bootstrap-table').bootstrapTable('getData'), function(key, value) {
            total_reembolso_realizado =  total_reembolso_realizado + parseFloat(value.cantidad);
            //$('.bootstrap-table').bootstrapTable('remove', {field: "state",values : "97327"});
        });
        load_price_panel();
    }

    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
            fistLoadCobro = true,
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
                id      : 'reembolso-gasto_cobro',
                element : '.reportes-reembolso-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    search  : false,
                    showExport  : false,
                    showToggle  : false,
                    showRefresh  : false,
                    onLoadSuccess : function(params){
                        //if(fistLoadCobro == true){

                            sum_reembolso_total();
                            //fistLoadCobro = false;
                        //}

                    },

                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        //load();
    });
</script>

