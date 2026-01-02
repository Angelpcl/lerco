<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use app\models\envio\Envio;
use app\models\user\User;
use kartik\daterange\DateRangePicker;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['/operacion/envio/envios-json-btt']);
$bttUrlView   = Url::to(['/view?id=']);
$bttUrlUpdate = Url::to(['/update?id=']);

?>

<br/>

<div class="operacion-pre-envio-lax-index">
    <div class="btt-toolbar">

        <?= Html::hiddenInput('tipo_envio', Envio::TIPO_ENVIO_LAX) ?>
        <?= Html::hiddenInput('status_id', Envio::STATUS_SOLICITADO) ?>

        <div class="ibox mar-btm-5px">
            <div class="ibox-content pad-btm-15px">
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
                    <?= Html::dropDownList('sucursal_emisor', null,User::getSucursalesTierraLax(), ['class' => 'max-width-170px', 'prompt'=> 'Sucursal emisor']) ?>

                </div>
            </div>
        </div>
    </div>

    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input'),
            can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver envio" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar envio" class="fa fa-pencil"></a>': ''),
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
                    field: 'is_reenvio',
                    title: 'Reenvio',
                    align: 'center',
                    formatter: btf.status.opt_check,
                    sortable: true,
                    visible : false,
                },
                {
                    field: 'costo_reenvio',
                    title: 'Costo de reenvio',
                    sortable: true,
                    visible : false,
                    formatter: btf.conta.money,
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
                    field: 'monto_pagado',
                    title: 'Cobro realizado',
                    sortable: true,
                    formatter: btf.conta.money,

                },
                {
                    field: 'monto_deuda',
                    title: 'Salda con',
                    sortable: true,
                    formatter: btf.conta.moneyDeuda,

                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_envio,
                },
                {
                    field: 'agente',
                    title: 'Agente',
                    sortable: true,
                    visible : false,
                },
                {
                    field: 'pre_created_at',
                    title: 'Precaptura Creada',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
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
                id      : 'envio',
                element : '.operacion-pre-envio-lax-index',
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
