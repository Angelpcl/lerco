<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\esys\EsysListaDesplegable;
use app\models\envio\Envio;
use app\models\esys\EsysSetting;
?>

<div class="fade modal " id="modal-show-reenvio"  tabindex="-1" role="dialog" aria-labelledby="modal-show-label" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title"> <i id="icon_emisor" class="fa fa-truck mar-rgt-5px icon-lg"></i> Dirección de reenvío</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <div class="row">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <h3 class="text-main">Estado: <small id="estado_name" class="text_direccion"></small></h3>

                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Colonia: <small id="colonia_name" class="text_direccion"></small></h3>
                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Numero Interior: <small id="n_interior_name" class="text_direccion"></small></h3>
                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Codigo postal: <small id="codigo_postal_name" class="text_direccion"></small></h3>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <h3 class="text-main">Municipio: <small id="municipio_name" class="text_direccion"></small></h3>
                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Direccion: <small id="direccion_name" class="text_direccion"></small></h3>
                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Numero Exterior: <small id="n_exterior_name" class="text_direccion"></small></h3>
                                    </div>
                                    <div class="form-group">
                                        <h3 class="text-main">Referencia: <small id="referencia_name" class="text_direccion"></small></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Modal footer-->
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var $text_direccion = $('.text_direccion'),
        $estado_name    = $('#estado_name'),
        $colonia_name       = $('#colonia_name'),
        $n_interior_name    = $('#n_interior_name'),
        $codigo_postal_name = $('#codigo_postal_name'),
        $municipio_name     = $('#municipio_name'),
        $direccion_name     = $('#direccion_name'),
        $n_exterior_name    = $('#n_exterior_name'),
        $referencia_name    = $('#referencia_name');


    var init_reenvio = function($paquete_id){
        $text_direccion.html('');
        $.get("<?= Url::to(['/operacion/envio/show-direccion-paquete']) ?>",{ paquete_id : $paquete_id },function(direccionJson){
            if (direccionJson.code == 202) {
                $estado_name.html(direccionJson.data.estado );
                $colonia_name.html(direccionJson.data.colonia );
                $n_interior_name.html(direccionJson.data.n_interior );
                $codigo_postal_name.html(direccionJson.data.codigo_postal );
                $municipio_name.html(direccionJson.data.municipio );
                $direccion_name.html(direccionJson.data.direccion );
                $n_exterior_name.html(direccionJson.data.n_exterior );
                $referencia_name.html(direccionJson.data.referencia );

            }else{
                $.niftyNoty({
                    type: "danger",
                    container : "floating",
                    title : "Error",
                    message : json.message,
                    closeBtn : false,
                    timer : 5000
                });
            }

        },'json');
    }
</script>
