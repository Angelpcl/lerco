<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use kartik\daterange\DateRangePicker;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['documentado-json-btt']);

?>

<div class="reporte-ejecutivo-documentado">
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
                    field: 'nombre_emisor',
                    title: 'Emisor',
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
                    field: 'nombre_sucursal',
                    title: 'Sucursal Receptor',
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
                    field: 'peso_unitario',
                    title: 'Peso Unitario',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'dia',
                    title: 'Dia',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'mes',
                    title: 'Mes',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'total_unitario',
                    title: 'Total unitario Aprox.',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'created_at',
                    title: 'Documentado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                 {
                    field: 'created_by',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'reporte',
                element : '.reporte-ejecutivo-documentado',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        load_ini();
    });
</script>
