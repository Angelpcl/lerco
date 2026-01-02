<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use kartik\daterange\DateRangePicker;
BootstrapTableAsset::register($this);

/* @var $this yii\web\View */


$bttExport      = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl         = Url::to(['/operacion/envio/envios-json-btt']);
$bttUrlReceptor = Url::to(['/operacion/envio/envios-recibidos-json-btt']);
$bttUrlView   = Url::to(['/operacion/envio/view?id=']);
?>

<div class="row">
    <div class="col-sm-10">
        <div class="btt-toolbar filter-top">
            <?= Html::hiddenInput('historial_cliente_id', $model->id) ?>
            <div class="panel mar-btm-5px">
                <div class="panel-body pad-btm-15px">
                    <div>
                        <div class="DateRangePicker   kv-drp-dropdown  col-sm-5">
                            <?= DateRangePicker::widget([
                                'name'           => 'date_range',
                                //'presetDropdown' => true,
                                'hideInput'      => true,
                                'useWithAddon'   => true,
                                'convertFormat'  => true,
                                'startAttribute' => 'from_date',
                                'endAttribute' => 'to_date',
                                'startInputOptions' => ['value' => '2019-01-01'],
                                'endInputOptions' => ['value' => '2019-12-31'],
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

                        <?= Html::input('text', "folio",false,["class" => " max-width-170px form-control" , "placeholder"  => "# Folio", "style" => "display: unset;" ]) ?>

                        <?= Html::dropDownList('sucursal_emisor', null,Sucursal::getItems(), ['class' => 'max-width-170px', 'prompt'=> 'Sucursal emisor']) ?>
                        <?= Html::dropDownList('sucursal_receptor', null,Sucursal::getItems(), ['class' => 'max-width-170px', 'prompt'=> 'Sucursal receptor']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="envio-emisor-index">
            <h3>Historial de paquetes enviados</h3>
            <table class="bootstrap-table"></table>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="envio-receptor-index">
            <h3>Historial de paquetes recibidos</h3>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
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
                    field: 'folio',
                    title: 'Folio',
                    sortable: true,
                },
                {
                    field: 'sucursal_emisor_nombre',
                    title: 'Sucursal Emisor',
                    sortable: true,
                },
                {
                    field: 'nombre_emisor',
                    title: 'Cliente Emisor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo_envio',
                    title: 'Tipo de envio',
                    align: 'center',
                    formatter: btf.tipo.tipo_envio,
                    sortable: true,
                },
                {
                    field: 'peso_total',
                    title: 'Peso total',
                    sortable: true,
                },
                {
                    field: 'subtotal',
                    title: 'SubTotal',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'impuesto',
                    title: 'Impuesto',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'total',
                    title: 'Total',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_envio,
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

            ],
            params = {
                id      : 'envio',
                element : '.envio-emisor-index',
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

<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
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
                    field: 'folio',
                    title: 'Folio',
                    sortable: true,
                },
                {
                    field: 'sucursal_emisor_nombre',
                    title: 'Sucursal Emisor',
                    sortable: true,
                },
                 {
                    field: 'sucursal_receptor_nombre',
                    title: 'Sucursal Receptor',
                    sortable: true,
                },
                {
                    field: 'nombre_emisor',
                    title: 'Cliente Emisor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'nombre_receptor',
                    title: 'Cliente Receptor',
                    sortable: true,
                },
                {
                    field: 'tipo_envio',
                    title: 'Tipo de envio',
                    align: 'center',
                    formatter: btf.tipo.tipo_envio,
                    sortable: true,
                },
                {
                    field: 'peso_total',
                    title: 'Peso total',
                    sortable: true,
                },
                {
                    field: 'subtotal',
                    title: 'SubTotal',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'impuesto',
                    title: 'Impuesto',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'total',
                    title: 'Total',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_envio,
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

            ],
            params = {
                id      : 'envioReceptor',
                element : '.envio-receptor-index',
                url     : '<?= $bttUrlReceptor ?>',
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
