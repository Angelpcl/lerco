<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\assets\HighchartsAsset;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\ruta\Ruta;

BootstrapTableAsset::register($this);
HighchartsAsset::register($this);


/* @var $this yii\web\View */

$this->title = 'Reporte de comision';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['reporte-comision-json-btt']);
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
?>

<div class="reportes-cobros-index">
    <div class="btt-toolbar">

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
                                 'timePicker'=>true,
                                'locale'=>[
                                    'format'=>'Y-m-d h:i A'
                                ],
                                /*'locale' => [
                                    'format'    => 'Y-m-d',
                                    'separator' => ' - ',
                                ],*/
                                'opens' => 'left',
                                "autoApply" => true,
                            ],
                        ])
                        ?>
                    </div>
                    <div>
                        <strong class="pad-rgt">Filtrar:</strong>

                        <?= Html::dropDownList('sucursal_emisor_id', null,Sucursal::getItemsUsa(), ['class' => 'max-width-270px', 'prompt'=> 'Sucursal que envía']) ?>

                        <?= Html::dropDownList('sucursal_receptor_id', null,Sucursal::getItemsMexico(), ['class' => 'max-width-270px', 'prompt'=> 'Sucursal que recibe']) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <table class="bootstrap-table">

    </table>
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
                    switchable: false,
                },
                {
                    field: 'sucursal_receptor_nombre',
                    title: 'Sucursal Receptor',
                    sortable: true,
                },
                {
                    field: 'nombre_receptor',
                    title: 'Receptor',
                    sortable: true,
                },
                {
                    field: 'sucursal_emisor_nombre',
                    title: 'Sucursal Emisor',
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
                    field: 'total',
                    title: 'TOTAL DE ENVIO',
                    align: 'right',
                    sortable: true,
                    switchable: false,
                    formatter: btf.conta.money,

                },
                {
                    field: 'cantidad',
                    title: 'TOTAL PAGADO',
                    align: 'right',
                    sortable: true,
                    switchable: false,
                    formatter: btf.conta.money,

                },
                {
                    field: 'peso_total',
                    title: 'Peso total ENVIO',
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'peso_paquetes',
                    title: 'Peso total PAQUETES',
                    align: 'right',
                    sortable: true,
                },
                {
                    field: 'total_aseguranza',
                    title: 'TOTAL DE ASEGURANZA',
                    align: 'right',
                    formatter: btf.conta.money,
                    sortable: true,
                },

                {
                    field: 'precio_libra_actual',
                    title: 'LIBRA OTORGADA',
                    align: 'right',
                    sortable: true,
                    switchable: false,
                    formatter: btf.conta.money,
                },
                {
                    field: 'comision_envio',
                    title: 'COMISION ENVIO',
                    align: 'center',
                    sortable: true,
                    formatter: btf.conta.money,
                    switchable: false,
                },

                {
                    field: 'comision_aseguranza',
                    title: 'COMISION ASEGURANZA',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.conta.money,
                },

                 {
                    field: 'total_comision',
                    title: 'TOTAL DE COMISION',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.conta.money,
                },

                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.status.opt_envio,
                },
                {
                    field: 'created_at',
                    title: 'Cobrado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'Cobrado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'reporte',
                element : '.reportes-cobros-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},

                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
        //load();
    });


</script>
