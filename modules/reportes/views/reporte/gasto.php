<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\esys\EsysListaDesplegable;


BootstrapTableAsset::register($this);

/* @var $this yii\web\View */

$this->title = 'Egresos';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['/pagos/pago-gasto/pagos-json-btt']);
?>



<div class="pagos-pago-index">
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
                        <?= Html::dropDownList('concepto_id', null,EsysListaDesplegable::getItems('concepto_pago'), ['class' => 'max-width-170px', 'prompt'=> 'Selecciona el concepto']) ?>

                        <?=
                            Html::a('<i class="fa fa-cloud-download mar-rgt-5px"></i> Reporte Egresos Excel', null, [
                                'class' => 'btn btn-lg btn-danger',
                                'id' => 'reporte_download',
                                'style'=> "display:none",
                            ])
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="bootstrap-table"></table>
</div>

<script type="text/javascript">
    $btn_toolbar        = '.pagos-pago-index .btt-toolbar';
    $reporte_download   = $('#reporte_download');
    $(document).ready(function(){


        $($btn_toolbar).change(function(){
            $reporte_download.show();
        });

        $reporte_download.click(function(event){
            event.preventDefault();
            params['filters']  = $($btn_toolbar + ' :input').serialize();


            window.location = '<?= Url::to('reporte-egresos-ajax') ?>?' + params['filters'] + "&is_reporte=true";


        });



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
                    field: 'concepto',
                    title: 'Concepto',
                    sortable: true,
                },
                {
                    field: 'monto',
                    title: 'Monto',
                    sortable: true,
                    formatter: btf.conta.money,
                },
                {
                    field: 'fecha_pago',
                    title: 'Fecha de pago',
                    sortable: true,
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
                    formatter: btf.user.created_by,
                },
            ],
            params = {
                id      : 'caja',
                element : '.pagos-pago-index',
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
