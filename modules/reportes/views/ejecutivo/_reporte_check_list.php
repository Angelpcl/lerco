<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use kartik\daterange\DateRangePicker;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['paquetes-check-json-btt']);

?>

<div class="reporte-check-list">
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">

    $(document).ready(function(){

        var  $filters = $('.btt-toolbar :input'),
            columns = [
                {
                    field: 'viaje_id',
                    title: 'Viaje ID',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'viaje_nombre',
                    title: 'Viaje',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'tracked',
                    title: 'Tracked',
                    switchable: false,
                    sortable: true,

                },
                {
                    field: 'folio',
                    title: 'Folio',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo_movimiento',
                    title: 'Movimiento',
                    align: 'center',
                    switchable: false,
                    formatter: btf.tipo.tipo_movimiento,
                    sortable: true,
                },
                {
                    field: 'producto',
                    title: 'Producto',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'is_denegado',
                    title: 'Denegado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.status.opt_check,
                },
                {
                    field: 'total',
                    title: 'Total envio',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'total_pagado',
                    title: 'Total pagado',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'fecha_pago',
                    title: 'Pago',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },

            ],
            params = {
                id      : 'reporte',
                element : '.reporte-check-list',
                url     : '<?= $bttUrl ?>',
                colorCheckTrailer  : true,
                bootstrapTable : {
                    columns : columns,
                    showPaginationSwitch : false,
                    pagination 	: false,
                    search 		: false,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
