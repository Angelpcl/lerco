<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\esys\EsysListaDesplegable;
use app\models\cliente\Cliente;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['clientes-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);
?>


<div class="clientes-cliente-index">
    <div class="btt-toolbar">
        <div class="panel mar-btm-5px">
            <div class="panel-body pad-btm-15px">
                <div>
                   <div class="DateRangePicker   kv-drp-dropdown">
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
                    <?= Html::dropDownList('origen', null, Cliente::$origenList, ['prompt' => 'Tipo de origen', 'class' => 'max-width-170px form-control m-b']) ?>

                    <?=  Html::dropDownList('tipo_cliente', null, EsysListaDesplegable::getItems('tipo_cliente'), ['prompt' => 'Tipo de cliente', 'class' => 'max-width-170px form-control m-b'])  ?>

                    <?=  Html::dropDownList('status_respuesta_call', null, EsysListaDesplegable::getItems('status_respuesta_call'), ['prompt' => 'Tipo de llamada', 'class' => 'max-width-170px'])  ?>

                    <?=  Html::dropDownList('asignado_id', null, Cliente::getAsiganadoA(), ['prompt' => 'Tipo de asignado', 'class' => 'max-width-170px'])  ?>
                </div>
                <!--
                 <div class="mar-top">
                    <strong class="pad-rgt">Agrupar:</strong>

                        <?= Html::dropDownList('agrupar[fecha]', null, ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes', 'ano' => 'Año'], ['prompt' => 'Por fecha' ,'class' => 'max-width-170px mar-rgt-5px']) ?>

                        <?= Html::checkbox("agrupar[cliente]", false, ["id" => "agrupar-cliente", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por cliente", "agrupar-cliente", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[vendedor]", false, ["id" => "agrupar-vendedor", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por vendedor", "agrupar-vendedor", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[almacen]", false, ["id" => "agrupar-almacen", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por almacen", "agrupar-almacen", ["style" => "display:inline"]) ?>

                        <?= Html::checkbox("agrupar[formapago]", false, ["id" => "agrupar-formapago", "class" => "magic-checkbox"]) ?>
                        <?= Html::label("Agrupar por forma de pago", "agrupar-formapago", ["style" => "display:inline"]) ?>
                </div>
                -->
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
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver cliente" class="fa fa-eye"></a>',
                (can.update? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar cliente" class="fa fa-pencil"></a>': ''),
                (can.delete? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar cliente" class="fa fa-trash" data-confirm="Confirma que deseas eliminar el cliente" data-method="post"></a>': '')
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
                    field: 'email',
                    title: 'Correo electrónico',
                    sortable: true,
                },
                {
                    field: 'nombre_completo',
                    title: 'Nombre completo',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'sexo',
                    title: 'sexo',
                    align: 'center',
                    sortable: true,
                    formatter: btf.user.sexo,
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
                    sortable: true,
                },
                {
                    field: 'origen',
                    title: 'Origen',
                    sortable: true,
                    formatter: btf.status.origen,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
                },
                {
                    field: 'tipo_cliente',
                    title: 'Tipo de cliente',
                    align: 'center',
                    visible: false
                },
                {
                    field: 'status_call',
                    title: 'Estatus de la llamada',
                    align: 'center',
                    visible: false
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
                id      : 'cliente',
                element : '.clientes-cliente-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    //showExport : can.exportCliente ? true: false,
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
