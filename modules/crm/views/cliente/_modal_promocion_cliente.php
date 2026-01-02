<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\esys\EsysSetting;
use kartik\daterange\DateRangePicker;
use app\models\cliente\ClienteCodigoPromocion;
?>

<div class="fade modal " id="modal-promocion"  tabindex="-1" role="dialog" aria-labelledby="modal-promocion-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Promociones y Descuentos especiales</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-4 col-md-4">
                        <div class="alert alert-purple alert-sin-promocion" style="display: none" >
                                <strong> Información :</strong>  No existe  una promoción basica vigente, no puede generar codigos promocionales
                        </div>

                        <?= Html::Button('<i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i>  Promocion basica', ['class' =>  'btn btn-purple btn-lg btn-block', 'id' => 'form-promocion-basica']) ?>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <?= Html::Button('<i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Promocion especial', ['class' =>  'btn btn-warning btn-lg btn-block', 'id' => 'form-promocion-especial']) ?>
                    </div>

                    <div class="col-xs-4 col-md-4">
                        <div class="alert alert-mint alert-sin-sucursal" style="display: none" >
                                <strong> Información :</strong>  No existe  una promoción Sucursales vigente, no puede generar codigos promocionales
                        </div>
                        <?= Html::Button('<i id="icon_emisor" class="fa fa-gift mar-rgt-5px icon-lg"></i> Promocion Sucursales', ['class' =>  'btn btn-mint btn-lg btn-block', 'id' => 'form-promocion-sucursal']) ?>
                    </div>




                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert-promocion-basica" style="margin-top: 2%; display: none">
                            <div class="alert alert-purple " >
                                <strong> Información :</strong>  La promoción basica, otorga un descuento en la libra en la promoción vigente
                            </div>
                            <div class="promo_basica" style=" background: #bcc7b17a;margin-top: 2%;padding: 2px;text-align:center; background-image: url(<?= Url::to(['@web/img/code_promocion.png'])  ?>);">
                                <h3 id="code-basica"></h3>
                            </div>
                        </div>
                        <div class="alert  alert-info-basic" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert-promocion-sucursal" style="margin-top: 2%; display: none">
                            <div class="alert alert-mint " >
                                <strong> Información :</strong>  La promoción sucursal, aplica a la promoción para sucursales Especiales
                            </div>
                            <div class="promo_basica" style=" background: #bcc7b17a;margin-top: 2%;padding: 2px;text-align:center; background-image: url(<?= Url::to(['@web/img/code_promocion.png'])  ?>);">
                                <h3 id="code-sucursal"></h3>
                            </div>
                        </div>
                        <div class="alert  alert-info-sucursal" style="display: none">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert-promocion-especial" style="margin-top: 2%; display: none">
                            <div class="alert alert-warning " >
                                <strong> Información :</strong>  La promoción especial, otorga un descuento directo al precio total del envio.
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?= Html::tag('p', "Ingresa un aproximado de libras a enviar",["class" => "text-main" ]) ?>
                                    <?= Html::input('number', 'requiered_libras',null,['class' => 'form-control','placeholder' => 'Libras a enviar']) ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <?= Html::tag('p', "Descuento a solicitar",["class" => "text-main" ]) ?>
                                            <?= Html::input('number', 'descuento',null,['class' => 'form-control','placeholder' => 'Descuento']) ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <?= Html::tag('p', "Tipo de condonación",["class" => "text-main" ]) ?>
                                            <?=  Html::dropDownList('tipo_condonacion', null, ClienteCodigoPromocion::$condonacionList, [ 'class' => 'form-control max-width-170px']) ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">

                                <div class="DateRangePicker   kv-drp-dropdown  col-sm-6">
                                    <?= Html::tag('p', "Selecciona un rango de fecha el que enviara su paquete ",["class" => "text-main" ]) ?>
                                    <?= DateRangePicker::widget([
                                        'name'           => 'date_range_promo',
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
                                <div class="col-sm-6">
                                    <br>
                                    <?= Html::Button('Solicitar promoción', ['class' =>  'btn btn-warning btn-lg btn-block', 'id' => 'form-promocion-especial-send']) ?>
                                </div>
                            </div>
                            <br>
                            <div class="alert  alert-info-especial" style="display: none">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID </th>
                                <th>Tipo promocion / descuento </th>
                                <th>Promocion vigente</th>
                                <th>Clave</th>
                                <th>Libras a enviar</th>
                                <th>Descuento solicitado</th>
                                <th>Fecha inicio</th>
                                <th>Fecha fin</th>
                                <th>Estatus </th>
                            </tr>
                        </thead>
                        <tbody class="table_complemento_promocion" style="text-align: center;">

                        </tbody>
                    </table>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="display-none">
     <table>

        <tbody class="template_complemento">
            <tr id = "complemento_id_{{promocion_id}}">
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-id"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-tipo"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-nombre"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-clave"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-libra_envia"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-descuento"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-fecha_ini"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-fecha_fin"]) ?></td>
                <td ><?= Html::tag('p', "0",["class" => "text-main" , "id"  => "table-status"]) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<script>
    var $formPromocionBasica    = $('#form-promocion-basica'),
        $formPromocionSucursal  = $('#form-promocion-sucursal'),
        $formPromocionEspecial  = $('#form-promocion-especial'),
        $formPromocionEspecialSen  = $('#form-promocion-especial-send'),
        $alertPromocionBasica   = $('.alert-promocion-basica'),
        $alertPromocionSucursal   = $('.alert-promocion-sucursal'),
        $alertInfoBasic         = $('.alert-info-basic'),
        $alertInfoSucursal         = $('.alert-info-sucursal'),
        $alertInfoEspecial      = $('.alert-info-especial'),
        $alertPromocionEspecial = $('.alert-promocion-especial');
        $template_complemento   = $('.template_complemento');
        $code_basica            = $('#code-basica');
        $code_sucursal          = $('#code-sucursal');

    $formPromocionBasica.click(function(){
        $alertPromocionBasica.show();
        $alertPromocionEspecial.hide();
        $alertPromocionSucursal.hide();

        $.post('promocion-create-basic-ajax',{ id : <?= $model->id  ?>, promocion_id : promocionVigente.id },function(json){
            if (json.code == 10) {
                $code_basica.html(json.message);
                $alertInfoBasic.show();
                $alertInfoBasic.addClass('alert-purple').removeClass('alert-danger');
                $alertInfoBasic.html('<strong> Success :</strong> Se genero correctamente el codigo promocional.');
                load_promocion_descuento();
            }else{
                $alertInfoBasic.show();
                $alertInfoBasic.addClass('alert-danger').removeClass('alert-purple');
                $alertInfoBasic.html('<strong> Error :</strong>' + json.message);
            }
        });
    });

    $formPromocionSucursal.click(function(){
        $alertPromocionBasica.hide();
        $alertPromocionEspecial.hide();
        $alertPromocionSucursal.show();

        $.post('promocion-create-sucursal-ajax',{ id : <?= $model->id  ?>, promocion_id : promocionVigenteSucursal.id },function(json){
            if (json.code == 10) {
                $code_sucursal.html(json.message);
                $alertInfoSucursal.show();
                $alertInfoSucursal.addClass('alert-mint').removeClass('alert-danger');
                $alertInfoSucursal.html('<strong> Success :</strong> Se genero correctamente el codigo promocional.');
                load_promocion_descuento();
            }else{
                $alertInfoSucursal.show();
                $alertInfoSucursal.addClass('alert-danger').removeClass('alert-mint');
                $alertInfoSucursal.html('<strong> Error :</strong>' + json.message);
            }
        });

    });

    $formPromocionEspecial.click(function(){
        $alertPromocionEspecial.show();
        $alertPromocionBasica.hide();
        $alertPromocionSucursal.hide();
        $alertInfoBasic.hide();
        $alertInfoSucursal.hide();
    });

    $formPromocionEspecialSen.click(function(){
        $requiered_libras   =  $('input[name="requiered_libras"]');
        $descuento          =  $('input[name="descuento"]');
        $date_range         =  $('input[name="date_range_promo"]');
        $tipo_condonacion   =  $('select[name="tipo_condonacion"]');

        if ($requiered_libras.val()  && $descuento.val() && $date_range.val()) {
            $.post('promocion-create-especial-ajax',{
                id : <?= $model->id ?>,
                requiered_libras    : $requiered_libras.val(),
                descuento           : $descuento.val(),
                date_range          : $date_range.val(),
                tipo_condonacion    : $tipo_condonacion.val(),
                 },function(json){

                if (json.code == 10) {
                    //$code.html(json.message);
                    $alertInfoEspecial.show();
                    $alertInfoEspecial.addClass('alert-warning').removeClass('alert-danger');
                    $alertInfoEspecial.html('<strong> Success :</strong> Se genero correctamente la solicitud.');
                    $requiered_libras.val(''); $descuento.val('');  $date_range.val('');
                    load_promocion_descuento();
                }else{
                    $alertInfoEspecial.show();
                    $alertInfoEspecial.addClass('alert-danger').removeClass('alert-warning');
                    $alertInfoEspecial.html('<strong> Error :</strong>' + json.message);
                }
            });
        }else{
            $alertInfoEspecial.show();
            $alertInfoEspecial.addClass('alert-danger').removeClass('alert-warning');
            $alertInfoEspecial.html('<strong> Error :</strong> Todos los valores son requeridos');
        }
    });



</script>

