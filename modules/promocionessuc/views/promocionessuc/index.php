<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = '    Promiciones';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['paises-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>

<p>
    <?= $can['create'] ?
        Html::a('Nueva promoción', ['create'], ['class' => 'btn btn-success add']) : '' ?>
</p>

<div class="pagos-pago-index">

    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var $filters = $('.btt-toolbar :input'),
            can = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) {
                return [
                    '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver envio" class="fa fa-eye"></a>',
                    (can.update ? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar" class="fa fa-pencil"></a>' : '')
                ].join('');
            },
            columns = [{
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'sucursal_nombre',
                    title: 'Sucursal',
                    sortable: true,
                },
                {
                    field: 'fecha_inicio',
                    title: 'Inicio',
                    sortable: true,
                },
                {
                    field: 'fecha_fin',
                    title: 'Fin',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: function(value) {
                        if (value === "10") {
                            return 'Activo';
                        } else if (value === "20") {
                            return 'Inactivo';
                        } else {
                            return 'Desconocido'; // Puedes ajustar esto según sea necesario
                        }
                    }
                },
                {
                    field: 'nombre',
                    title: 'Creado por',
                    sortable: true,
                },
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay: 'none',
                }
            ];

        params = {
            id: 'caja',
            element: '.pagos-pago-index',
            url: '<?= $bttUrl ?>',
            bootstrapTable: {
                columns: columns,
                exportOptions: {
                    "fileName": "<?= $bttExport ?>"
                },
                onDblClickRow: function(row, $element) {
                    window.location.href = '<?= $bttUrlView ?>' + row.id;
                },
            }
        };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>