<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroRembolsoEnvio;
use app\models\movimiento\MovimientoPaquete;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Reporte de seguimiento';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['reporte-seguimiento-json-btt']);
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
?>

<p >
<?=
    Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte Excel', null, [
        'class' => 'btn btn-lg btn-danger',
        'id' => 'reporte_download',

    ])
?>
</p>


<div class="reportes-reporte-seguimiento-index">
    <div class="btt-toolbar">
        <div class="ibox ">
           <div class="ibox-content">
                <br>
                <strong class="pad-rgt">Filtrar [FECHA]:</strong>
                <div class="DateRangePicker   kv-drp-dropdown  ">
                    <?= DateRangePicker::widget([
                        'name'           => 'date_range',
                        //'presetDropdown' => true,
                        'hideInput'      => true,
                        'useWithAddon'   => true,
                        'convertFormat'  => true,
                        'pluginOptions'  => [
                             //'timePicker'=>true,
                            'locale'=>[
                                'format'=>'Y-m-d'
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
                <div class="panel-body pad-btm-15px">
                    <div>
                        <strong class="pad-rgt">Filtrar:</strong>
                        <?= Html::dropDownList('tipo_movimiento', null,MovimientoPaquete::$tipoLaxTierList, ['class' => 'max-width-170px', 'prompt'=> 'Movimiento']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table">

    </table>

<?php /* ?>
    <div class="pad-all" style="margin-bottom: 40px;">
        <div id="container-envio" style=" height: 355px; margin: 0 auto"></div>
    </div>
*/?>
</div>

<script type="text/javascript">

    var $reporte_download   = $('#reporte_download'),
        $btn_toolbar        = '.reportes-reporte-seguimiento-index',
        params              = [];
    $(document).ready(function(){

        var $filters = $('.btt-toolbar :input'),
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
                    field: 'tracked',
                    title: 'Tracked',
                    switchable: false,
                    sortable: true,

                },
                {
                    field: 'nombre_emisor',
                    title: 'Emisor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'nombre_receptor',
                    title: 'Receptor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'nombre_sucursal',
                    title: 'Sucursal Receptor',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'tipo_movimiento',
                    title: 'Movimiento',
                    align: 'center',
                    switchable: false,
                    formatter: btf.tipo.tipo_movimiento,
                    sortable: true,
                },
                {
                    field: 'producto',
                    title: 'Producto',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'peso_unitario',
                    title: 'Peso Unitario',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'total_unitario',
                    title: 'Valor unitario Aprox.',
                    align: 'center',
                    switchable: false,
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'created_at',
                    title: 'Fecha [MOVIMIENTO]',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                 {
                    field: 'created_by',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'reporte',
                element : '.reportes-reporte-seguimiento-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

$reporte_download.click(function(event){
    event.preventDefault();

    params['filters']  = $($btn_toolbar + ' :input').serialize();

    window.location.href = "<?= Url::to(['reporte-csv-seguimiento' ])  ?>?"+ params['filters'];
});
</script>
