<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Paises';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['paises-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

$bttImagePath =  Yii::getAlias('@web/uploads/flags/');
?>

<p>
    <?= $can['create'] ?
        Html::a('Nuevo pais', ['create'], ['class' => 'btn btn-success add']) : '' ?>
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
                    '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver envío" class="fa fa-eye"></a>',
                    (can.update ? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="ver pais" class="fa fa-pencil"></a>' : '')
                ].join('');
            },
            imageFormatter = function(value, row) {
                return '<img src="<?= $bttImagePath ?>' + row.imagen + '" alt="Imagen" style="width:60px; height:40px; border: 1px solid #ccc; box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); display: block; margin: 0 auto;">';
            };

        columns = [{
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'image',
                    title: 'Imagen',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    formatter: imageFormatter,
                },
                {
                    field: 'nombre',
                    title: 'Nombre',
                    sortable: true,
                },
                //{
                //    field: 'created_at',
                //    title: 'Creado',
                //    align: 'center',
                //    sortable: true,
                //    switchable: false,
                //    formatter: btf.time.date,
                //},
                //{
                //    field: 'created_by_name',
                //    title: 'Creado por',
                //    sortable: true,
                //},

                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay: 'none',
                },
            ],
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