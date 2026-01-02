<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\envio\Envio;
use app\models\sucursal\Sucursal;
use app\models\cobro\CobroRembolsoEnvio;

BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Estado de cuenta';
$this->params['breadcrumbs'][] = $this->title;

$bttUrl       = Url::to(['estado-cuenta-json-btt']);
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrlView   = Url::to(['estado-cuenta-view?id=']);

?>

<div class="reportes-cobros-index">
    <div class="btt-toolbar">
        <?= Html::hiddenInput('origen', Sucursal::ORIGEN_MX) ?>
        <div class="panel mar-btm-5px">
           <div class="panel-heading">
                <div class="panel-control">
                    <button class="btn reset-form" ><i class="demo-pli-repeat-2"></i></button>
                    <button class="btn collapsed" data-target="#toolbar-panel-collapse" data-toggle="collapse" aria-expanded="false"><i class="demo-pli-arrow-down"></i></button>
                </div>
                <br>
                <div class="DateRangePicker   kv-drp-dropdown ">
                    <?= DateRangePicker::widget([
                        'id'             => 'date_range',
                        'name'           => 'date_range',
                        //'presetDropdown' => true,
                        'hideInput'      => true,
                        'useWithAddon'   => true,
                        'convertFormat'  => true,
                        'pluginOptions'  => [
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
            </div>
        </div>
    </div>
    <table class="bootstrap-table">
    </table>
</div>

<script type="text/javascript">

    $(document).ready(function(){
         var  $filters      = $('.btt-toolbar :input')
              $date_range   = $('#date_range');

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
                    field: 'nombre',
                    title: 'Nombre sucursal',
                    sortable: true,
                },
                {
                    field: 'encargado',
                    title: 'Encargado',
                    switchable: false,
                    sortable: true,
                },

                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    align: 'center',
                    sortable: true,
                },
                 {
                    field: 'estado',
                    title: 'Estado',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'municipio',
                    title: 'Municipio',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'paquete_tierra',
                    title: 'Paquetes TIERRA',
                    align: 'center',

                },
                {
                    field: 'paquete_lax',
                    title: 'Paquetes LAX',
                    align: 'center',

                },
                {
                    field: 'paquete_mex',
                    title: 'Paquetes MEX',
                    align: 'center',

                },


                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
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
            id      : 'reporte',
            element : '.reportes-cobros-index',
            url     : '<?= $bttUrl ?>',
            bootstrapTable : {
                columns : columns,
                exportOptions : {"fileName":"<?= $bttExport ?>"},
                onDblClickRow : function(row, $element){
                    window.location.href = '<?= $bttUrlView ?>' + row.id + ( $date_range.val() ? '&date_range=' + $date_range.val() : '' );
                },

            }
        };
        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });

</script>
