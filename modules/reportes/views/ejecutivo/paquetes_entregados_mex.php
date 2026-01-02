<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\user\User;
use app\models\envio\Envio;
use kartik\daterange\DateRangePicker;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['envio-mex-json-btt']);
?>
<div class="reporte-descarga-index">
	<div class="btt-toolbar">
        <?= Html::hiddenInput('status_id', Envio::STATUS_ENTREGADO) ?>
        <h3 class="panel-title">Filtros</h3>
	    <div class="panel   mar-btm-5px">
            <div class="panel-heading">
               <div class="DateRangePicker   kv-drp-dropdown  ">
                    <?= DateRangePicker::widget([
                        'name'           => 'date_range',
                        'presetDropdown' => true,
                        'hideInput'      => true,
                        'value'=> date('Y-m').'-01 - '. date('Y') .'-'.date('m').'-' . date("d",(mktime(0,0,0,date('m') + 1,1,date('Y'))-1)),
                        'useWithAddon'   => true,
                        'convertFormat'  => true,
                        'pluginOptions'  => [
                            'locale' => [
                                'format'    => 'Y-m-d',
                                'separator' => ' - ',
                            ],
                            'opens' => 'left',
                            "autoApply" => true,
                        ],
                    ])
                    ?>
                </div>
            </div>
        	<div class="panel-body pad-btm-15px ">
                <div>
                    <strong class="pad-rgt">Filtrar:</strong>

                    <?=  Html::dropDownList('sucursal_id', null, User::getSucursalesMex() , ['prompt' => 'Sucursal Receptor', 'class' => 'max-width-170px'])  ?>
                </div>
	        </div>
	    </div>
	</div>

	<table class="bootstrap-table"></table>

</div>

<script type="text/javascript">

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
                    field: 'is_reenvio',
                    title: 'Reenvio',
                    align: 'center',
                    formatter: btf.status.opt_check,
                    sortable: true,
                    visible : false,
                },
                 {
                    field: 'is_efectivo',
                    title: 'Efectivo',
                    align: 'center',
                    formatter: btf.status.opt_check,
                    sortable: true,
                    visible : false,
                },
                {
                    field: 'costo_reenvio',
                    title: 'Costo de reenvio',
                    sortable: true,
                    visible : false,
                    formatter: btf.conta.money,
                },
                {
                    field: 'peso_mex_con_empaque',
                    title: 'Peso Bodega MX',
                    sortable: true,
                    switchable:false,
                    align: 'center',
                },
                {
                    field: 'peso_total',
                    title: 'Peso Entrega USA',
                    sortable: true,
                    switchable:false,
                    align: 'center',
                },
                {
                    field: 'total',
                    title: 'Total',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'n_pz',
                    title: 'N° pz',
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'monto_pagado',
                    title: 'Cobro realizado',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'monto_deuda',
                    title: 'Salda con',
                    sortable: true,
                    formatter: btf.conta.moneyDeuda,
                },
                {
                    field: 'cobros_mex',
                    title: 'N° pagos MX',
                    sortable: true,
                    visible: false,
                    align: 'center',
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_envio,
                },
                {
                    field: 'agente',
                    title: 'Agente',
                    sortable: true,
                    visible : false,
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
                id      : 'reporte',
                element : '.reporte-descarga-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
