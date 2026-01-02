<?php
use yii\helpers\Url;
use app\models\envio\Envio;
 ?>
<script>

$(function () {

  $form_esysdireccion_envio.$inputCodigoSearch.change(function() {
      $form_esysdireccion_envio.$inputColonia.html('');
      $form_esysdireccion_envio.$inputEstado.val(null).trigger("change");

      var codigo_search = $form_esysdireccion_envio.$inputCodigoSearch.val();

      $.get('<?= Url::to('@web/municipio/codigo-postal-ajax') ?>', {'codigo_postal' : codigo_search}, function(json) {
          if(json.length > 0){
              $modal.$error_codigo.hide();
              $form_esysdireccion_envio.$inputEstado.val(json[0].estado_id); // Select the option with a value of '1'
              $form_esysdireccion_envio.$inputEstado.trigger('change');
              municipioSelected = json[0].municipio_id;

              $.each(json, function(key, value){
                  $form_esysdireccion_envio.$inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
              });
          }
          else{
              municipioSelected  = null;
              $modal.$error_codigo.show();
          }

          if(userInfo.municipio_id)
              $form_esysdireccion_envio.$inputColonia.val(userInfo.codigo_postal_id).trigger("change");
          else
              $form_esysdireccion_envio.$inputColonia.val(null).trigger("change");

      }, 'json');
  });

  $form_esysdireccion_envio.$inputMunicipio.change(function(){
      if ($form_esysdireccion_envio.$inputEstado.val() != 0 && $form_esysdireccion_envio.$inputMunicipio.val() != 0 && $form_esysdireccion_envio.$inputCodigoSearch.val() == "" ) {
          $form_esysdireccion_envio.$inputColonia.html('');
          $.get('<?= Url::to('@web/municipio/colonia-ajax') ?>', {'estado_id' : $form_esysdireccion_envio.$inputEstado.val(), "municipio_id": $form_esysdireccion_envio.$inputMunicipio.val(), 'codigo_postal' : $form_esysdireccion_envio.$inputColonia.val()}, function(json) {
              if(json.length > 0){
                  $.each(json, function(key, value){
                      $form_esysdireccion_envio.$inputColonia.append("<option value='" + value.id + "'>" + value.colonia + "</option>\n");
                  });
              }
              else
                  municipioSelected  = null;

              if(userInfo.municipio_id)
                  $form_esysdireccion_envio.$inputColonia.val(userInfo.codigo_postal_id).trigger("change");
              else
                  $form_esysdireccion_envio.$inputColonia.val(null).trigger("change");

          }, 'json');
      }
  });


  /*====================================================
  *               CARGA DATOS EMISOR
  *====================================================*/
  $cliente_emisor.change(function(){

      if($(this).val() == '' || $(this).val() == null){
          $form_emisor.$btn_icon.removeClass('btn-danger').addClass('btn-primary');
          $('#icon_emisor').removeClass('fa-pencil-square-o').addClass('fa-users');

      }else{
          $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');
          $('#icon_emisor').removeClass('fa-pencil-square-o').addClass('fa-users');
      }

      if($(this).val() == '' || $(this).val() == null){ clear_form($form_emisor); clear_form($div_info_emisor.inputText); return false; }
      $('#icon_emisor').removeClass('fa-users').addClass('fa-pencil-square-o');
      $form_emisor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');

      key = search_item($(this).val(),clienteEmisor);

      $form_emisor.$nombre.val(clienteEmisor[key].nombre);
      $form_emisor.$apellidos.val(clienteEmisor[key].apellidos);
      $form_emisor.$email.val(clienteEmisor[key].email);
      $form_emisor.$telefono.val(clienteEmisor[key].telefono);
      $form_emisor.$telefono_movil.val(clienteEmisor[key].telefono_movil);

      $div_info_emisor.inputText.$estado.val(
          clienteEmisor[key].origen == 1 ? clienteEmisor[key].estado_usa : estadoList[clienteEmisor[key].estado_id]);

      $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : clienteEmisor[key].estado_id}, function(json) {
          municipioList = json;
          $div_info_emisor.inputText.$municipio.val(
          clienteEmisor[key].origen == 1 ? clienteEmisor[key].municipio_usa : municipioList[clienteEmisor[key].municipio_id]);
      }, 'json');

      $div_info_emisor.inputText.$colonia.val(
          clienteEmisor[key].origen == 1 ? clienteEmisor[key].colonia_usa : clienteEmisor[key].colonia );

      $div_info_emisor.inputText.$direccion.val(clienteEmisor[key].direccion);
      $div_info_emisor.inputText.$num_exterior.val(clienteEmisor[key].num_ext);
      $div_info_emisor.inputText.$num_interior.val(clienteEmisor[key].num_int);
  });
  /*====================================================
  *               CARGA DATOS RECEPTOR
  *====================================================*/
  $cliente_receptor.change(function(){

      $content_info_cliente.html('');
      if ($(this).val().length > 0 ) {
        clienteSelect = [];
        for (var i = 0; i < $(this).val().length; i++) {
          $.get('<?= Url::to(['cliente-info-ajax']) ?>', { q  : $(this).val()[i] },function(json){
            if (json) {

              clienteSelect.push(json);

              template_info_cliente = $template_info_cliente.html();
              template_info_cliente = template_info_cliente.replace('{{cliente_info_id}}', json.id);
              $content_info_cliente.append(template_info_cliente);
              $div_receptor        =  $("#cliente_info_id_" + json.id, $content_info_cliente );

              $('.link_info-receptor', $div_receptor).attr("data-id", json.id);
              $(".link_info-receptor",$div_receptor).attr("onclick","show_info_receptor(this)");
              $('button', $div_receptor).attr("data-id", json.id);
              $("button",$div_receptor).attr("onclick","load_cliente_receptor(this)");


              /*if($(this).val() == '' || $(this).val() == null){
                  $form_receptor.$btn_icon.removeClass('btn-danger').addClass('btn-primary');
                  $('#icon_receptor').removeClass('fa-pencil-square-o').addClass('fa-users');
                  $('.next', $content_tab).hide();
              }else{
                  $form_receptor.$btn_icon.removeClass('btn-primary').addClass('btn-danger');
                  $('#icon_receptor').removeClass('fa-pencil-square-o').addClass('fa-users');
                  $('.next', $content_tab).show();

              }*/

              //if($(this).val() == '' || $(this).val() == null){ clear_form($form_receptor); clear_form($div_info_receptor.inputText); return false; }


              //$('#icon_receptor').removeClass('fa-users').addClass('fa-pencil-square-o');

              //key = search_item($(this).val(),clienteReceptor);

              $('#cliente-nombre'   , $div_receptor ).val(json.nombre);
              $('#cliente-apellidos', $div_receptor ).val(json.apellidos);
              $('#cliente-email'    , $div_receptor ).val(json.email);
              $('#cliente-telefono', $div_receptor ).val(json.telefono);
              $('#cliente-telefono_movil', $div_receptor ).val(json.telefono_movil);

              $("input[name = 'estado_id']", $div_receptor).val(
                  json.origen == 1 ? json.estado_usa : estadoList[json.estado_id]);


              $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : json.estado_id}, function(json) {
                  municipioList = json;
                  $("input[name = 'municipio_id']", $div_receptor).val(
                  json.origen == 1 ? json.municipio_usa : municipioList[json.municipio_id]);
              }, 'json');

              $("input[name = 'colonia_id']", $div_receptor).val(
                  json.origen == 1 ? json.colonia_usa : json.colonia );

              $("input[name = 'direccion_id']", $div_receptor).val(json.direccion);
              $("input[name = 'num_exterior']", $div_receptor).val(json.num_ext);
              $("input[name = 'num_interior']", $div_receptor).val(json.num_int);
              $("input[name = 'referencia']", $div_receptor).val(json.referencia);


              load_cliente_emisor_paquete();
            }
          });
        }
      }
  });


  var load_cliente_emisor_paquete = function(){
      $paquete_cliente_id.html('');
      $.each(clienteSelect, function(key, value){
          $paquete_cliente_id.append("<option value='" + value.id + "'>" + value.nombre + " " + value.apellidos + "</option>\n");
      });

  }
  /*====================================================
  *          HIDE / SHOW DIVS CON INFORMACION DE USUARIOS
  *====================================================*/
  $link_info_emisor.click(function(){
      if ($is_div_info_emisor) {
          $(this).html("Ver más + ");
          $div_info_emisor.hide(1000);
          $is_div_info_emisor = false;
      }else{
          $(this).html("Ver menos - ");
          $div_info_emisor.show(1000);
          $is_div_info_emisor = true;
      }
  });




  /*====================================================
  *               OPEN MODAL
  *====================================================*/

  $(".modal-create").click(function(){
      $("#modal-title-cliente").html($(this).data("cliente"));

      userInfo = [];
      clear_form($modal);
      clear_form($form_cliente);
      clear_form($form_esysdireccion);
      $form_esysdireccion.$inputEstado.val(null).trigger('change');
      $form_esysdireccion.$inputMunicipio.html('');
      $form_esysdireccion.$inputColonia.html('');

      $('#form-cliente').html("Crear cliente");
      if ($.trim($(this).data("cliente")) == 'Emisor' ){
          isEmisorCreate = true ;
          isReceptorCreate = false;
          if ($cliente_emisor.val()) {
              $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $cliente_emisor.val() }, function(cliente_json){
                  if (cliente_json) {
                      userInfo = cliente_json.results;
                     $form_cliente.$nombre.val(cliente_json.results.nombre);
                     $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                     $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                     $form_cliente.$telefono.val(cliente_json.results.telefono);
                     $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                     $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                     $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                     $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                     $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                     $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                     $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                     $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                     $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                     $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                     if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                          if (!cliente_json.results.codigo_postal)
                             $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                          else
                              $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');
                      }

                     $('#form-cliente').html("Guardar cambios");
                  }
              });
          }
      }else if( $.trim($(this).data("cliente")) == 'Receptor'){
        isReceptorCreate = true;
        isEmisorCreate = false;
      }
  });


});

var load_cliente_receptor = function(elem){
  $("#modal-title-cliente").html($(elem).data("cliente"));
  clear_form($modal);
  clear_form($form_cliente);
  clear_form($form_esysdireccion);
  $form_esysdireccion.$inputEstado.val(null).trigger('change');
  $form_esysdireccion.$inputMunicipio.html('');
  $form_esysdireccion.$inputColonia.html('');
  $('#form-cliente').html("Crear cliente");

   if( $.trim($(elem).data("cliente")) == 'Receptor'){

      isReceptorCreate = true;
      isEmisorCreate   = false;
      isEmisorEdit   = false;
      $is_action = $.trim($(elem).data("action"));
        if ($cliente_receptor.val().length > 0 && $is_action == 'Update' ) {
            $is_id = $.trim($(elem).data("id"));
            $.get("<?= Url::to(['/crm/cliente/cliente-ajax'])  ?>",{ cliente_id: $is_id }, function(cliente_json){
                if (cliente_json) {
                  isEmisorEdit   = true;
                  userInfo = cliente_json.results;
                  $form_cliente.$id.val(cliente_json.results.id);
                  $form_cliente.$nombre.val(cliente_json.results.nombre);
                  $form_cliente.$apellidos.val(cliente_json.results.apellidos);
                  $form_cliente.$inputOrigen.val(cliente_json.results.origen).trigger('change');
                  $form_cliente.$telefono.val(cliente_json.results.telefono);
                  $form_cliente.$telefono_movil.val(cliente_json.results.telefono_movil);
                  $form_esysdireccion.$inputDireccion.val(cliente_json.results.direccion);
                  $form_esysdireccion.$inputNumeroExt.val(cliente_json.results.num_ext);
                  $form_esysdireccion.$inputNumeroInt.val(cliente_json.results.num_int);
                  $form_esysdireccion.$inputReferencia.val(cliente_json.results.referencia);
                  $form_esysdireccion.$inputCodigoPostalUsa.val(cliente_json.results.codigo_postal_usa);
                  $form_esysdireccion.$inputEstadoUsa.val(cliente_json.results.estado_usa);
                  $form_esysdireccion.$inputMunicipioUsa.val(cliente_json.results.municipio_usa);
                  $form_esysdireccion.$inputColoniaUsa.val(cliente_json.results.colonia_usa);
                  $form_esysdireccion.$inputColonia.val(cliente_json.results.colonia);

                   if (cliente_json.results.origen == <?= Envio::ORIGEN_MX  ?> ) {
                       if (!cliente_json.results.codigo_postal)
                           $form_esysdireccion.$inputEstado.val(cliente_json.results.estado_id ? cliente_json.results.estado_id  : 0).trigger('change');
                       else
                            $form_esysdireccion.$inputCodigoSearch.val(cliente_json.results.codigo_postal).trigger('change');

                       //$form_esysdireccion.$inputColonia.val(cliente_json.results.codigo_postal_id);
                   }
                   $('#form-cliente').html("Guardar cambios");
                }
            });
        }
    }

}

/************************************
/ Estados y municipios
/***********************************/
function onEstadoReenvioChange() {
    var estado_id = $form_esysdireccion_envio.$inputEstado.val();
    municipioSelected = estado_id == 0 ? null : municipioSelected;

    $form_esysdireccion_envio.$inputMunicipio.html('');

    if (estado_id ||  municipioSelected) {
        $.get('<?= Url::to('@web/municipio/municipios-ajax') ?>', {'estado_id' : estado_id}, function(json) {
            $.each(json, function(key, value){
                $form_esysdireccion_envio.$inputMunicipio.append("<option value='" + key + "'>" + value + "</option>\n");
            });

            if( userInfo.municipio_id )
                $form_esysdireccion_envio.$inputMunicipio.val(userInfo.municipio_id).trigger("change");
            else{
                $form_esysdireccion_envio.$inputMunicipio.val(municipioSelected); // Select the option with a value of '1'
                $form_esysdireccion_envio.$inputMunicipio.trigger('change');
            }

        }, 'json');
    }
}

var show_info_receptor = function($ele){
  $div_receptor_id   = $($ele).data("id");
  $('.info-receptor').hide();
  $div_receptor        =  $("#cliente_info_id_" + $div_receptor_id);
  if ($is_div_info_receptor) {
      $('.link_info-receptor' ,$div_receptor).html("Ver más + ");
      $('.info-receptor' ,$div_receptor).hide(1000);
      $is_div_info_receptor = false;
  }else{

      $('.link_info-receptor' ,$div_receptor).html("Ver menos - ");
      $('.info-receptor' ,$div_receptor).show(1000);
      $is_div_info_receptor = true;
  }
};
</script>
