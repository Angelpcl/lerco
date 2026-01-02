<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\descarga\DescargaBodega;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['descarga-bodega-json-btt']);
$bttUrlDelete = Url::to(['delete?id=']);

?>

<div class="descarga-bodega-oaxaca-index">
    <div class="btt-toolbar">
        <?= Html::hiddenInput('bodega_id', DescargaBodega::DESCARGA_OAXACA) ?>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var actions = function(value, row) { return [
                '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar" class="fa fa-trash" data-confirm="Confirma que deseas eliminar" data-method="post"></a>'
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
                    field: 'estado',
                    title: 'ESTADO',
                    sortable: true,
                },
                {
                    field: 'municipio',
                    title: 'MUNICIPIO',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'bodega_descarga',
                    title: 'Bodega Descarga',
                    align: 'center',
                    formatter: btf.status.descarga_bodega,
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
                id      : 'descarga_oaxaca',
                element : '.descarga-bodega-oaxaca-index',
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
