<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\envio\Envio;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */
$this->title = 'Comisión agente Tierra - LAX';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['comision-agente-json-btt']);
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');

?>

<div class="reportes-reporte-index">
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
           <div class="panel-heading">
                <div class="panel-control">
                    <button class="btn reset-form" ><i class="demo-pli-repeat-2"></i></button>
                    <button class="btn collapsed" data-target="#toolbar-panel-collapse" data-toggle="collapse" aria-expanded="false"><i class="demo-pli-arrow-down"></i></button>
                </div>
                <br>
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
            </div>
            <div id="toolbar-panel-collapse" class="collapse in" aria-expanded="false">
                <div class="panel-body pad-btm-15px">
                    <div>
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::dropDownList('status_id', null,[
                            Envio::STATUS_ENTREGADO    => Envio::$statusList[Envio::STATUS_ENTREGADO],
                            Envio::STATUS_HABILITADO   => Envio::$statusList[Envio::STATUS_HABILITADO],
                            Envio::STATUS_NOAUTORIZADO => Envio::$statusList[Envio::STATUS_NOAUTORIZADO],
                            Envio::STATUS_CANCELADO    => Envio::$statusList[Envio::STATUS_CANCELADO],
                        ], ['class' => 'max-width-170px', 'prompt'=> 'Tipo de estatus ']) ?>

                        <?= Html::dropDownList('tipo_envio', null,[
                            Envio::TIPO_ENVIO_TIERRA    => Envio::$tipoList[Envio::TIPO_ENVIO_TIERRA],
                            Envio::TIPO_ENVIO_LAX       => Envio::$tipoList[Envio::TIPO_ENVIO_LAX],
                        ], ['class' => 'max-width-170px']) ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input'),

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
                    field: 'agente',
                    title: 'Agente',
                    sortable: true,
                    switchable: false,
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
                    field: 'cantidad',
                    title: 'Cobro realizado',
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
                    title: 'Cobrado',
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

            ],
            params = {
                id      : 'comision',
                element : '.reportes-reporte-index',
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

