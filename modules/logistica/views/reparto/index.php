<?php

use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;
use app\models\reparto\Reparto;
BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Reparto';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['repartos-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>
<div class="ibox">
    <div class="ibox-content">
        <p>
            <?= $can['create']?
                    Html::a(' <i class="fa fa-plus"></i> Nuevo reparto', ['create'], ['class' => 'btn btn-success add']): '' ?>
        </p>
        <div class="logistica-reparto-index">
            <div class="btt-toolbar" style="border-style: solid;border-width: 1px;box-shadow: 2px 2px 5px #8d8d8d;">
                <div class="panel mar-btm-5px">
                    <div class="panel-body pad-btm-15px">
                        <div>
                            <strong class="pad-rgt">Filtrar [FECHA]:</strong>
                            <div class="DateRangePicker   kv-drp-dropdown">
                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    //'presetDropdown' => true,
                                    'hideInput'      => true,
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
                            <strong class="pad-rgt">Filtrar:</strong>
                            <?= Html::dropDownList('chofer_id', null, EsysListaDesplegable::getItems('chofer_unidad_reparto'), ['prompt' => 'Chofer', 'class' => 'max-width-170px form-control m-b']) ?>

                            <?=  Html::dropDownList('unidad_id', null,  EsysListaDesplegable::getItems('clave_unidad_reparto'), ['prompt' => 'Unidad', 'class' => 'max-width-170px form-control m-b'])  ?>

                            <?=  Html::dropDownList('status', null,  Reparto::$statusList , ['prompt' => 'Estatus', 'class' => 'max-width-170px form-control m-b'])  ?>

                            <?= Html::button("DESCARGAR REPORTE", ["class" => "btn btn-success btn-block btn-lg", "id" => "reporte_download" ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>


<script type="text/javascript">
    var $reporte_download = $('#reporte_download'),
        $btn_toolbar    = '.logistica-reparto-index';
        params          = [];
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver reparto" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar reparto" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar reparto" class="fa fa-trash" data-confirm="Confirma que deseas eliminar la reparto" data-method="post"></a>': '')
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
                    field: 'chofer',
                    title: 'Chofer',
                    sortable: true,
                    switchable:false,
                },

                {
                    field: 'unidad',
                    title: 'Clave / N° de unidad ID',
                    switchable: false,
                    sortable: true,

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
                id      : 'reparto',
                element : '.logistica-reparto-index',
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



$reporte_download.click(function(event){
    event.preventDefault();
    params['filters']  = $($btn_toolbar + ' :input').serialize();
    window.location =  "<?= Url::to(['reporte-dowloand-reparto' ])  ?>?"+ params['filters']
});
</script>

