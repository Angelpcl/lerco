<?php

use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use yii\helpers\Url;
use app\models\envio\Envio;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Viajes Mex';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['viajes-mex-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>

<p>
    <?= $can['create']?
            Html::a('Nueva viaje', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>


<div class="logistica-viajes-mex-index">
    <div class="btt-toolbar">
        <?= Html::hiddenInput('tipo_servicio', Envio::TIPO_ENVIO_MEX) ?>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver viaje" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar viaje" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar viaje" class="fa fa-trash" data-confirm="Confirma que deseas eliminar la viaje" data-method="post"></a>': '')
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
                    field: 'fecha_salida',
                    title: 'Fecha de salida',
                    sortable: true,
                    switchable:false,
                    formatter: btf.time.date,
                },
                {
                    field: 'nombre_chofer',
                    title: 'Conductor',
                    switchable: false,
                    sortable: true,

                },
                {
                    field: 'placas',
                    title: 'Clave / N° de unidad',
                    sortable: true,
                    visible: false,

                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    sortable: true,
                    formatter: btf.tipo.tipo_reparto,
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
                id      : 'viajeMex',
                element : '.logistica-viajes-mex-index',
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

