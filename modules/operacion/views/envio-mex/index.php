<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use app\models\envio\Envio;
use app\models\user\User;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysSetting;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Envios MEX - USA';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['/operacion/envio-mex/envios-json-btt']);
$bttUrlView   = Url::to(['/operacion/envio-mex/view?id=']);
$bttUrlUpdate = Url::to(['/operacion/envio-mex/update?id=']);
$bttUrlDelete = Url::to(['/operacion/envio-mex/cancel?id=']);
?>

<p>
    <?= $can['create_mex']?
            Html::a('Nuevo envio', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="operacion-envio-mex-index">
    <div class="btt-toolbar">

        <?= Html::hiddenInput('tipo_envio', Envio::TIPO_ENVIO_MEX) ?>
        <?= Html::hiddenInput('tipo_envio_mex', true) ?>
        <div class="ibox mar-btm-5px">
            <div class="ibox-content ">
                <div>
                    <div class="DateRangePicker   kv-drp-dropdown ">
                        <dibv class="row">
                            <div class="col-sm-6">
                                <strong>FECHA: </strong>

                                <?= DateRangePicker::widget([
                                    'name'           => 'date_range',
                                    //'presetDropdown' => true,
                                    'hideInput'      => true,
                                    'useWithAddon'   => true,
                                    'convertFormat'  => true,
                                    'presetDropdown' => true,
                                    'pluginOptions'  => [
                                        'locale' => [
                                            'format'    => 'Y-m-d',
                                            'separator' => ' - ',
                                        ],

                                        "autoApply" => true,
                                    ],
                                    'options' => ['placeholder' => 'SELECCIONA RANGO DE FECHA ...']
                                ])
                                ?>
                            </div>

                        </dibv>


                    </div>
                    <br>
                    <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::input('text', "folio",false,["class" => " max-width-170px form-control" , "placeholder"  => "# Folio", "style" => "display: unset;" ]) ?>

                    <?= Html::dropDownList('status_id', null,[
                        Envio::STATUS_RECOLECTADO    => Envio::$statusList[Envio::STATUS_RECOLECTADO],
                        Envio::STATUS_EMPAQUETADO    => Envio::$statusList[Envio::STATUS_EMPAQUETADO],
                        Envio::STATUS_ENTREGADO    => Envio::$statusList[Envio::STATUS_ENTREGADO],
                        Envio::STATUS_NOAUTORIZADO => Envio::$statusList[Envio::STATUS_NOAUTORIZADO],
                        Envio::STATUS_PREAUTORIZADO => Envio::$statusList[Envio::STATUS_PREAUTORIZADO],
                        Envio::STATUS_AUTORIZADO => Envio::$statusList[Envio::STATUS_AUTORIZADO],
                        Envio::STATUS_PREPAGADO    => Envio::$statusList[Envio::STATUS_PREPAGADO],
                        Envio::STATUS_CANCELADO    => Envio::$statusList[Envio::STATUS_CANCELADO],

                    ], ['class' => 'max-width-170px', 'prompt'=> 'Tipo de estatus ']) ?>

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
                (can.update_mex ? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar envio" class="fa fa-pencil"></a>': ''),
                (can.delete_mex ? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar envio" class="fa fa-trash" data-confirm="Confirma que deseas cancelar el envio" data-method="post"></a>': '')
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
                    field: 'is_reenvio',
                    title: 'Reenvio',
                    align: 'center',
                    formatter: btf.status.opt_check,
                    sortable: true,
                    visible : false,
                },
                 {
                    field: 'is_efectivo',
                    title: 'Efectivo',
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
                    field: 'subtotal',
                    title: 'SubTotal',
                    sortable: true,
                    formatter: btf.conta.money,
                },

                {
                    field: 'peso_total',
                    title: 'Peso total',
                    sortable: true,
                    visible : false,
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
                    field: 'n_pz',
                    title: 'N° pz',
                    sortable: true,
                    align: 'center',
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
                    field: 'cobros_mex',
                    title: 'N° pagos MX',
                    sortable: true,
                    visible: false,
                    align: 'center',
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
                element : '.operacion-envio-mex-index',
                url     : '<?= $bttUrl ?>',
                colorAdeudo  : true,
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
