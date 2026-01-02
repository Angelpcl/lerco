<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use kartik\daterange\DateRangePicker;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['paquetes-adeudo-json-btt']);
?>

<div class="reporte-adeuda">
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
                    field: 'paquetes',
                    title: '# Paquetes',
                    align: 'center',
                    switchable: false,
                    sortable: true,

                },
                {
                    field: 'tracked',
                    title: 'Tracked',
                    switchable: false,
                    sortable: true,

                },
                {
                    field: 'nombre_receptor',
                    title: 'Receptor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'sucursal_receptor',
                    title: 'Sucursal Receptor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo_movimiento_top',
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
                    field: 'peso_unitario',
                    title: 'Peso Unitario USA',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'peso_mx',
                    title: 'Peso Unitario MX',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'total',
                    title: 'Total Envio',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'monto_pagado',
                    title: 'Pagado',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'monto_deuda',
                    title: 'Adeudo Envio',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.moneyDeuda,
                },
                {
                    field: 'total_unitario',
                    title: 'Valor unitario Aprox.',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'created_at',
                    title: 'Transcurso',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },

            ],
            params = {
                id      : 'reporte',
                element : '.reporte-adeuda',
                url     : '<?= $bttUrl ?>',
                colorAdeudo  : true,
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
