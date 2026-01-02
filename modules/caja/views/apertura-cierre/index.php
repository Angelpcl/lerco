<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Apertura y Cierre de cajas';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['aperturas-cierres-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
?>

<div class="operaciones-apertura-cierre-index">
    <div class="btt-toolbar">
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver sucursal" class="fa fa-eye"></a>',
            ].join(''); },
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
                    field: 'fecha_apertura',
                    title: 'Fecha de apertura',
                    sortable: true,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'cantidad_apertura',
                    title: 'Cantidad de apertura',
                    switchable: false,
                    sortable: true,
                    align: 'right',
                    formatter: btf.conta.money,
                },

                {
                    field: 'fecha_cierre',
                    title: 'Fecha de cierre',
                    sortable: true,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'cantidad_cierre',
                    title: 'Cantidad de cierre',
                    align: 'right',
                    sortable: true,
                    formatter: btf.conta.money,
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
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ],
            params = {
                id      : 'aperturaCierres',
                element : '.operaciones-apertura-cierre-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>
