<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Zonas rojas';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['paises-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>

<p>
  <?= $can['create']?
    Html::a('Nueva zona roja', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="pagos-pago-index">
    
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input'),
            can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver envio" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Update" class="fa fa-pencil" ></a>': '')
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
                    field: 'pais_nombre',
                    title: 'País',
                    sortable: true,
                },
                {
                    field: 'code',
                    title: 'Código postal',
                    sortable: true,
                },
                
                {
                    field: 'estado',
                    title: 'Estado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                   // formatter: btf.time.date,
                },
                //{
                //    field: 'created_by_name',
                //    title: 'Creado por',
                //    sortable: true,
                //    //formatter: btf.user.created_by,
                //},
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
                id      : 'caja',
                element : '.pagos-pago-index',
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
