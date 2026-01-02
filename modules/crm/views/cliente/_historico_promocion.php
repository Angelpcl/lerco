<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use app\models\envio\Envio;
use kartik\daterange\DateRangePicker;
use yii\widgets\ActiveForm;
use app\models\cliente\ClienteCodigoPromocion;


/* @var $this yii\web\View */
$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['historico-promocion-json-btt']);
?>

<div class="clientes-historial-promocion">
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
            <div class="panel-body pad-btm-15px">
                <div>
                    <strong class="pad-rgt">Filtrar:</strong>

                    <?=  Html::dropDownList('tipo_cliente', null, EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => 'Tipo de cliente', 'class' => 'max-width-170px'])  ?>

                    <?=  Html::dropDownList('tipo', null, ClienteCodigoPromocion::$tipoList, ['prompt' => 'Tipo de promoción', 'class' => 'max-width-170px'])  ?>

                    <?=  Html::dropDownList('asignado_id', null, Cliente::getAsiganadoA(), ['prompt' => 'Tipo de asignado', 'class' => 'max-width-170px'])  ?>
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
                    field: 'nombre_completo',
                    title: 'Nombre completo',
                    switchable: false,
                    sortable: true,
                },

                {
                    field: 'telefono_movil',
                    title: 'Teléfono movil',
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus Cliente',
                    align: 'center',
                    formatter: btf.status.opt_o,
                },
                {
                    field: 'promocion_nombre',
                    title: 'Promocion',
                    sortable: true,
                    switchable: false,
                },
                {
                    field: 'tipo',
                    title: 'Tipo codigo',
                    sortable: true,
                    formatter: btf.tipo.tipo_code,
                },
                {
                    field: 'tipo_condonacion',
                    title: 'Tipo condonacion',
                    sortable: true,
                    formatter: btf.tipo.tipo_condonacion,
                },
                {
                    field: 'descuento',
                    title: 'Descuento',
                    sortable: true,
                },


                {
                    field: 'status_code',
                    title: 'Estatus de codigo',
                    sortable: true,
                    formatter: btf.status.code,
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
                id      : 'clienteHistorico',
                element : '.clientes-historial-promocion',
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



