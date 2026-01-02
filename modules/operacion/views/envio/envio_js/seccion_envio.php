<?php

use yii\helpers\Url;
use app\models\esys\EsysSetting;
?>

<script>
    $(document).on('input', '.detalle_producto_class', function() {
        // Obtener el valor del atributo 'p_id'
        var pId = $(this).attr('p_id');
        // Obtener el valor del input
        var inputValue = $(this).val();

        //console.log('p_id:', pId);
        let ids = pId.split('_');
        let id_array_main = Number(ids[0]);
        let id_array_sub = Number(ids[1]);

        // Buscar el objeto principal en paquete_array
        let mainObject = paquete_array.find(obj => obj.paquete_id === id_array_main);

        if (mainObject) {
            console.log(mainObject, 'objeto con productos principales');
            // Buscar el subobjeto en paquete_detalle
            let detailObject = mainObject.paquete_detalle.find(detail => detail.id === pId);

            if (detailObject) {
                // Actualizar el valor del subobjeto
                detailObject.peso_max = parseFloat(inputValue); // Asegúrate de que inputValue es un número

                let sum = 0;
                let cont = 0;
                mainObject.paquete_detalle.forEach(detail => {
                    sum += parseFloat(detail.peso_max) || 0; // Manejar valores no numéricos
                    cont++;
                });

                //if (mainObject.peso < sum) { // Comprobar si el peso total supera el límite
                //alert('Sobre pasa el peso configurado, peso ingresado: ' + sum + ' lb y peso maximo: ' + mainObject.peso + ' lb');
                //detailObject.peso_max = 0; // Restablecer el valor del peso
                //$(this).val(0);
                //sum -= parseFloat(inputValue); // Restablecer la suma
                //}

                mainObject.peso = Number(sum.toFixed(2));


                // Aquí puedes hacer cualquier otra acción necesaria, como actualizar la vista
                // console.log('Updated Object:', mainObject);
            } else {
                //console.log('Detalle no encontrado para id:', pId);
            }
        } else {
            //console.log('Objeto principal no encontrado para paquete_id:', id_array_main);
        }

        $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
        console.log('paquete array', paquete_array);
        renderrizarDetalleFinal();
        //render_paquete_template();
        //
        //
        //clear_form($form_paquete);
        //
        //$selectProducto.val(false).trigger('change');
        ////$reenvio_select_id.val(false).trigger('change');
        //$form_paquete.$producto_tipo_enviar.val(10).trigger('change');
        //$form_paquete.$producto_tipo.val(tipoProducto.nuevo).trigger('change');

    });





    function renderizarPaquetes() {
        const tbody = document.getElementById('contenidoProductos');
        const tbodyall = document.getElementById('contenidoProductosTotales');
        tbody.innerHTML = ''; // Limpiar el contenido existente
        tbodyall.innerHTML = '';

        //let paseo_unitario = 0;

        arrayPaqueteDeatlle.forEach(paquete => {
            const tr = document.createElement('tr');

            let pesoValue = paquete.tipo_producto === 30 ? 'N/A' : paquete.peso_max;
            let disabledAttribute = paquete.tipo_producto === 30 ? 'disabled' : '';

            tr.innerHTML = `
                        <td>${paquete.nombre}</td>
                        <td>
                            ${paquete.tipo_producto === 30 ? 
                                `<input type="text" class="form-control detalle_producto_class" value="NA" style="width: 90%; text-align: right;" p_id="${paquete.id}" disabled />` : 
                                `<input type="number" class="form-control detalle_producto_class" name="peso[]" value="${pesoValue}"   p_id="${paquete.id}" style="width: 90%; text-align: right;" ${disabledAttribute} />`}
                        </td> 
                        
                `;
            //<td>$${paquete.costo}</td>

            tbody.appendChild(tr);
        });
        renderrizarDetalleFinal();

    }

    function renderrizarDetalleFinal() {
        const tbodyall = document.getElementById('contenidoProductosTotales');

        tbodyall.innerHTML = '';
        paquete_array.forEach(paquete => {
            const tr = document.createElement('tr');
            let pesoValue = paquete.tipo_producto === 30 ? 'N/A' : paquete.peso + " lbs";
            let disabledAttribute = paquete.tipo_producto === 30 ? 'disabled' : 'disabled';
            tr.innerHTML = `
                        <td>${paquete.producto_text}</td>
                        <td>
                            ${paquete.tipo_producto === 30 ? 
                                `<input type="text" class="form-control detalle_producto_class" value="NA" style="width: 90%; text-align: right;" p_id="${paquete.id}" disabled />` : 
                                `<input type="text" class="form-control detalle_producto_class" name="peso[]" value="${pesoValue}"   p_id="${paquete.id}" style="width: 90%; text-align: right;" ${disabledAttribute} />`}
                        </td> 
                        
                `;
            tbodyall.appendChild(tr);

        });


    }
    $(function() {
        /*====================================================
         *               AGREGA UN ITEM A ARRAY
         *====================================================*/
        $btnAgregarPaquete.click(function() {
            if (validation_form_envio()) {
                return false;
            }
            $('#productoModal').modal('show');
            //console.log("Select producto array", selectProducto_array);
            //return ;

            let tipoProducto = Number($form_paquete.$producto_tipo_enviar.val());
            let nombre = "sdbsdfb";
            let categoria_id = null;
            let costo = 0; //$form_paquete.$producto_caja.data("precio");
            switch (tipoProducto) {
                case 10: //CASO PARA PRODUCTO 
                    nombre = selectProducto_array.text;
                    costo = Number($('#precio_libra_id').val());
                    break;
                case 20: //CASO PARA CAJA
                    nombre = $("#select_caja_id option:selected").text();
                    costo = Number($('#precio_libra_id').val());

                    break;
                case 30: // CASO PARA CAJA SIN LIMITE
                    nombre = $("#select_caja_id option:selected").text();
                    costo = $form_paquete.$producto_caja.data("precio");
                    break;
                default:
                    nombre = 'N/A'
                    costo = 0;
                    break;
            }

            paquete = {
                "costo_libra": costo,
                'tipo_producto': tipoProducto,
                "paquete_id": paquete_array.length + 1,
                "sucursal_id": $paquete_sucursal_id.val(),
                "cliente_id": $paquete_cliente_id.val(),
                "reenvio_id": $reenvio_select_id.val() && $reenvio_select_id.val() != 0 ? $reenvio_select_id.val() : null,
                //"categoria_id"          : selectProducto_array.categoria_id,
                "categoria_id": $form_paquete.$producto_tipo_enviar.val() == 10 ? selectProducto_array.categoria_id : null,
                "categoria_text": $form_paquete.$producto_tipo_enviar.val() == 10 ? selectProducto_array.categoria : null,
                "cantidad": $form_paquete.$cantidad.val(),
                "peso": 0, //$.trim($form_paquete.$peso.val()) ? $.trim($form_paquete.$peso.val()) : 0,
                "valor_declarado": $form_paquete.$valor_declarado.val(),
                "producto_id": selectProducto_array.id,
                "producto_id": $form_paquete.$producto_tipo_enviar.val() == 10 ? selectProducto_array.id : $form_paquete.$producto_caja.val(),
                "producto_text": $form_paquete.$producto_tipo_enviar.val() == 10 ? selectProducto_array.text : $("#select_caja_id option:selected").text(),
                //"producto_text": nombre,
                "precio_caja_unitario": $form_paquete.$producto_caja.data("precio") ? parseFloat($form_paquete.$producto_caja.data("precio")) : 0,
                "tipo_producto_enviar": $form_paquete.$producto_tipo_enviar.val(),
                "producto_tipo": $form_paquete.$producto_tipo.val(),
                "valoracion_paquete": $form_paquete.$valoracion_paquete.val(),
                "costo_neto_extraordinario": is_costo_extraordinario ? $form_paquete.$costo_extraordinario.val() : 0,
                "is_costo_extraordinario": is_costo_extraordinario ? true : false,

                "observaciones": $form_paquete.$observacion.val(),
                "seguro": $form_paquete.$seguro.prop('checked') ? true : false,
                "costo_seguro": $form_paquete.$seguro.prop('checked') && !is_costo_extraordinario ? (costo_seguro_select * parseFloat($form_paquete.$valor_declarado.val())) / 100 : 0,
                "status": 10,
                "update": $envioID.val() ? 10 : 1,
                "origen": 1,
                'paquete_detalle': null,
            };

            arrayPaqueteDeatlle.push(...create_function_paquete_detalle(paquete.producto_text, paquete.costo_libra, paquete.cantidad, paquete.peso, paquete.paquete_id, tipoProducto));
            //console.log('paquetes', arrayPaqueteDeatlle);

            let paquetesbyId = arrayPaqueteDeatlle.filter(paquete_ => Number(paquete_.paquete_id) == Number(paquete.paquete_id));
            paquete.paquete_detalle = paquetesbyId;
            //console.log(arrayPaqueteDeatlle);



            paquete_array.push(paquete);

            render_paquete_template();


            clear_form($form_paquete);
            $selectProducto.val(false).trigger('change');
            //$reenvio_select_id.val(false).trigger('change');
            $form_paquete.$producto_tipo_enviar.val(10).trigger('change');
            $form_paquete.$producto_tipo.val(tipoProducto.nuevo).trigger('change');


        });

        /**
         * ====================================================
         *  FUNCION PARA CREAR EL PAQUETE DETALLADO SEMI TICKET
         * ====================================================
         */
        var create_function_paquete_detalle = function(nombre, costo, cantidad, peso_max, paq_id, tipo_producto = null) {
            let arrayProducto = [];

            for (let i = 0; i < cantidad; i++) {
                // Crear una nueva instancia de product para cada iteración
                let product = {
                    id: `${paq_id}_${i}`, // Asignar un id único para cada producto
                    tipo_producto: tipo_producto,
                    paquete_id: Number(paq_id),
                    nombre: nombre,
                    costo: costo,
                    cantidad: 1, // Suponiendo que la cantidad por producto es 1
                    peso_max: 0, // (peso_max / cantidad).toFixed(2),
                };
                arrayProducto.push(product);
            }

            return arrayProducto;
        }


        /**
         * =====================================================
         *              RENDERIZA LOS PRODUCTOS
         * =====================================================
         */


        var validation_form_envio = function() {
            $error_add_paquete.html('');
            $error_add_paquete.hide();

            switch (true) {
                case !$paquete_sucursal_id.val():
                    $error_add_paquete.append('<div class="help-block">* Selecciona una sucursal receptor</div>');
                    $error_add_paquete.show();
                    return true;
                    break;
                case !$paquete_cliente_id.val():
                    $error_add_paquete.append('<div class="help-block">* Selecciona un cliente receptor</div>');
                    $error_add_paquete.show();
                    return true;
                    break;
                case !$selectProducto.val() && $form_paquete.$producto_tipo_enviar.val() == 10:
                    $error_add_paquete.append('<div class="help-block">* Selecciona un producto</div>');
                    $error_add_paquete.show();
                    return true;
                    break;

                case !$form_paquete.$producto_caja.val() && $form_paquete.$producto_tipo_enviar.val() == 20:
                    $error_add_paquete.append('<div class="help-block">* Selecciona un Caja </div>');
                    $error_add_paquete.show();
                    return true;
                    break;

                case !$form_paquete.$cantidad.val() || (parseInt($form_paquete.$cantidad.val()) == 0 || parseInt($form_paquete.$cantidad.val()) < 0):
                    $error_add_paquete.append('<div class="help-block">* N° de piezas no puede ser nulo, debes ingresar 1 cantidad</div>');
                    $error_add_paquete.show();
                    return true;
                    break;

                case !$reenvio_select_id.val() || $reenvio_select_id.val() == 0:
                    $error_add_paquete.append('<div class="help-block">* Debes seleccionar una dirección destino al paquete</div>');
                    $error_add_paquete.show();
                    return true;
                    break;

                case is_costo_extraordinario:
                    if (!$form_paquete.$costo_extraordinario.val()) {
                        $error_add_paquete.append('<div class="help-block">COSTO NETO es requerido para este paquete</div>');
                        $error_add_paquete.show();
                        return true;
                    }
                    break;

                case !$form_paquete.$valor_declarado.val():
                    $error_add_paquete.append('<div class="help-block">* Valor declarado no puede ser nulo</div>');
                    $error_add_paquete.show();
                    return true;
                    break;
            }

        }

        /*====================================================
         *               MODIFICA EL PRECIO ACTUAL
         *====================================================*/


        /*====================================================
         *               MODIFICA EL PRECIO ACTUAL
         *====================================================*/

        $('#peso_total').change(function() {
            //console.log("==================================================================\n ADD PESOS \n ==================================================================");
            precio_libra_actual = Number(precio_libra_actual);
            let SUBTOTAL_ = 0;
            // let PAQUETE_BEFORE_UPDATE = 0;
            // Obtiene el valor del peso total
            let peso_total_sum = parseFloat($(this).val().replace(',', '.')) || 0;

            let peso_restar = 0;
            let costo_extraordinario_total = 0;
            let is_update = false;


            // Calcula el peso a restar y el costo extraordinario total
            paquete_array.forEach(paquete => {
                if (paquete.paquete_id && paquete.status === 10 && paquete.is_costo_extraordinario) {
                    peso_restar += parseFloat(paquete.peso) || 0;
                    costo_extraordinario_total += parseFloat(paquete.costo_neto_extraordinario) || 0;
                }

                //PAQUETE_BEFORE_UPDATE += parseFloat(paquete.peso) * 1 * parseFloat(paquete.cantidad) || 0;
            });
            //console.log(PAQUETE_BEFORE_UPDATE, 'PAQUETE_BEFORE_UPDATE');
            console.log('peso_total_sum', paquete_array);


            let costo_update_10 = 0;
            let costo_update_20 = 0;
            let costo_update_30 = 0;

            let data10 = [];
            let data20 = [];
            let data30 = [];

            // Actualiza la variable is_update
            paquete_array.forEach(paquete => {
                let can10, peso10, costo10 = 0;
                let can20, peso20, costo20 = 0;
                let can30, peso30, costo30 = 0;
                //console.log("PAQ", paquete);
                is_update = Number(paquete.update) === 10;

                if (paquete.paquete_detalle) {
                    //console.log('ento al paquete detalkle');

                    let contador = 0;
                    paquete.paquete_detalle.forEach(paq => {


                        let key = Number(paq.tipo_producto);
                        switch (key) {
                            case 10:
                                data10.push(paq);
                                console.log('hola PAG ADD', paq);
                                if (contador === 0) {
                                    cant10 = Number(paquete.cantidad) || 0;
                                    costo10 = Number(paquete.costo_libra) || 0;
                                    peso10 = Number(paquete.peso) || 0;

                                    //SUBTOTAL_ += peso10 * cant10 * costo10;
                                    SUBTOTAL_ += peso10 * costo10;
                                    //SUBTOTAL_ = peso_total_sum * costo10;
                                }
                                contador++;

                                break;
                            case 20:
                                data20.push(paq);
                                cant20 = Number(paquete.cantidad) || 0;
                                costo20 = Number(paquete.costo_libra) || 0;
                                peso20 = Number(paquete.peso) || 0;

                                SUBTOTAL_ += peso20 * costo20;
                                break;
                            case 30:

                                data30.push(paq);
                                if (contador === 0) {
                                    cant30 = Number(paquete.cantidad) || 0;
                                    costo30 = Number(paquete.costo_libra) || 0;
                                    //console.log('costo 30 ', costo30);
                                    //console.log('cant 30 ', cant30);
                                    //console.log('total 30 ', cant30 * costo30);

                                    //peso30 = Number(paquete.peso) || 0;
                                    SUBTOTAL_ += cant30 * costo30;
                                }

                                contador++
                                break;

                            default:
                                break;
                        }
                    });
                }
            });

            //console.log('SUBTOTAL_', SUBTOTAL_);



            if (is_update) {
                let cant10 = 0,
                    cant20 = 0,
                    cant30 = 0;
                let peso10 = 0,
                    peso20 = 0,
                    peso30 = 0;
                let costo10 = 0,
                    costo20 = 0,
                    costo30 = 0;

                data20.forEach(data_2 => {
                    peso20 += Number(data_2.peso_max) || 0;
                    cant20++;
                    costo20 = Number(data_2.costo) || 0;
                });

                data10.forEach(data_2 => {
                    peso10 += Number(data_2.peso_max) || 0;
                    cant10++;
                    costo10 = Number(data_2.costo) || 0;
                });
                let sub30 = 0;
                data30.forEach(data_2 => {
                    // console.log('cantidaes ', data_2);

                    //cant30++;
                    costo30 = Number(data_2.costo) || 0;
                    cant30 = Number(data_2.cantidad) || 0;
                    sub30 += costo30 * cant30;


                });
                // let sub10 = peso10 * cant10 * costo10;
                let sub10 = peso_total_sum * costo10;
                //let sub10 = peso10 *  costo10;
                let sub20 = peso20 * costo20;


                let subtotal_up = sub10 + sub20 + sub30;
                console.log('subtotal update', sub10);

                SUBTOTAL_ = subtotal_up;

                //if (data10.length === 0 && data20.length === 0 && data30.length === 0) {
                //    SUBTOTAL_ = PAQUETE_BEFORE_UPDATE;
                //    SUBTOTAL_ = SUBTOTAL_ENVIO;
                //}
                $('#envio-subtotal').val(subtotal_up);
                // console.log('subtotal update', subtotal_up);
            }


            console.log('SUBTOTAL_3', SUBTOTAL_);

            $('#envio-subtotal').val(SUBTOTAL_);

            // Calcula el peso total sumado y el subtotal
            peso_total_sum -= peso_restar;
            peso_total_sum = Math.abs(peso_total_sum); // Convierte a positivo si es negativo

            // Extrae variables de promo
            let existe_promo = Number(exist_promo.val()) || 0;
            let model = JSON.parse(exist_promo_json.val());
            let costo_libra;
            //$('#envio-subtotal').val(SUBTOTAL_);
            // Inicializa sub__ fuera del switch
            let sub__ = Number($('#envio-subtotal').val()) || 0;
            //sub__ = is_update ? sub__ : sub__;

            let seguro_total = parseFloat($('#envio-seguro_total').val()) || 0;
            console.log(peso_total_sum, 'peso_total_sum');

            //console.log(is_update, 'IS UPDATE');

            //is_update = false;
            // Calcula el subtotal basado en el tipo de producto
            switch (tipo_producto_select) {
                case 10:
                    $("#div_peso").hide();

                    costo_libra = precio_libra_actual; //parseFloat($("#precio_libra_id").val().replace(',', '.')) || 1.1;
                    costo_libra = existe_promo !== 10 ? parseFloat(model.costo_libra_peso_cli) || 0 : costo_libra;
                    costo_libra = is_update ? costo_update_10 : costo_libra;

                    subtotal = ((peso_total_sum - parseFloat(peso_caja_total || 0)) * costo_libra) + costo_extraordinario_total + sub__;
                    subtotal = Math.abs(subtotal);

                    //$('#envio-subtotal').val(subtotal.toFixed(2));
                    //console.log('hola entro al cso 10 con subtotal ', SUBTOTAL_);
                    //$('#envio-subtotal').val(SUBTOTAL_);
                    //$('#envio-subtotal-label').html(btf.conta.money(SUBTOTAL_));
                    console.log('subtotal xxx ', SUBTOTAL_);

                    console.log('HOLA xx', subtotal);
                    console.log('costo _libra ', precio_libra_actual);


                    $('#envio-subtotal').val(SUBTOTAL_);
                    $('#envio-subtotal-label').html(btf.conta.money(SUBTOTAL_));
                    break;

                case 20:
                    $("#div_peso").show();

                    costo_libra = precio_libra_actual; // parseFloat($("#precio_libra_id").val().replace(',', '.')) || 1.1;
                    costo_libra = existe_promo !== 10 ? parseFloat(model.costo_libra_caja_cli) || 0 : costo_libra;
                    costo_libra = is_update ? costo_update_20 : costo_libra;

                    subtotal = (parseFloat(peso_caja_total || 0) * costo_libra) + costo_extraordinario_total + sub__;
                    subtotal = Math.abs(subtotal);
                    //console.log('hola entro al cso 20 con subtotal ', SUBTOTAL_);

                    $('#envio-subtotal').val(SUBTOTAL_);
                    $('#envio-subtotal-label').html(btf.conta.money(SUBTOTAL_));

                    //$('#envio-subtotal').val(subtotal.toFixed(2));
                    //$('#envio-subtotal-label').html(btf.conta.money(subtotal));
                    break;

                case 30:
                    let costo = parseFloat($form_paquete.$producto_caja.data("precio")) || 0;
                    costo = existe_promo !== 10 ? parseFloat(model.costo_caja_limite_cli) || 0 : costo;
                    let cantidad = parseFloat($("#enviodetalle-cantidad").val()) || 0;
                    //costo = is_update ? costo_update_30 : costo;

                    subtotal = (costo * cantidad) + sub__;
                    subtotal = Math.abs(subtotal);
                    $('#envio-subtotal').val(SUBTOTAL_);
                    $('#envio-subtotal-label').html(btf.conta.money(SUBTOTAL_));

                    //$('#envio-subtotal').val(subtotal.toFixed(2));
                    //$('#envio-subtotal-label').html(btf.conta.money(subtotal));
                    break;

                default:
                    $("#div_peso").hide();
                    return;
            }

            // Calcula el total del envío
            let subtotal_value = parseFloat($('#envio-subtotal').val()) || 0;
            //console.log(subtotal_value);

            total_envio = seguro_total + subtotal_value;
            total_envio = Math.abs(total_envio);

            $('#envio-total').val(total_envio.toFixed(2));
            $('#envio-total-label').html(btf.conta.money(total_envio.toFixed(2)));

            // Renderiza el método de plantilla
            //console.log('array final ', paquete_array);
            $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
            render_metodo_template();
        });







        $('#envio-total').change(function() {
            render_metodo_template();
        });


        var cal_costo_reenvio = function() {
            peso_reenvio_total = $peso_reenvio.val() ? $peso_reenvio.val() : 0;
            if ($peso_reenvio.val() > 100) {
                opera_costo_reenvio = ((parseInt(precio_base_reenvio) / 100) * peso_reenvio_total);
                $inputcosto_reenvio.val(opera_costo_reenvio.toFixed(2));
            } else if ($peso_reenvio.val() > 0)
                $inputcosto_reenvio.val(precio_base_reenvio);
            else
                $inputcosto_reenvio.val(0);

            $('#lbl_peso').html(peso_reenvio_total + " lb");
            $('#lbl_costo_reenvio').html($inputcosto_reenvio.val() + " USD");
        }

        $peso_reenvio.change(function() {
            $('#peso_total').trigger('change');
        });
    });

    /*====================================================
     *               RENDERIZA TODO LOS PAQUETE
     *====================================================*/
    var render_paquete_template = function() {
        $content_paquete.html("");
        sum_peso_total = 0;
        seguro_total = 0;
        declarado_total = 0;
        peso_caja_total = 0;
        precio_caja_total = 0;
        peso_paquete_array = [];

        $.each(paquete_array, function(key, paquete) {
            if (paquete.paquete_id) {
                if (paquete.status == 10) {

                    if (paquete.peso) {
                        is_paquetePeso = false;
                        $.each(peso_paquete_array, function(key, paquetePeso) {
                            if (paquetePeso.categoria_id == paquete.categoria_id) {
                                is_paquetePeso = true;
                                paquetePeso.peso = parseFloat(paquetePeso.peso) + parseFloat(paquete.peso);
                            }
                        });

                        if (!is_paquetePeso) {
                            peso_paquete = {
                                categoria_id: paquete.categoria_id,
                                peso: parseFloat(paquete.peso),
                            };
                            peso_paquete_array.push(peso_paquete);
                        }
                    }

                    template_sucursal = $template_paquete.html();
                    template_sucursal = template_sucursal.replace("{{paquete_id}}", paquete.paquete_id);

                    $content_paquete.append(template_sucursal);

                    $tr = $("#paquete_id_" + paquete.paquete_id, $content_paquete);
                    $tr.attr("data-paquete_id", paquete.paquete_id);
                    $tr.attr("data-origen", paquete.origen);



                    $("#table_categoria_id", $tr).html(paquete.producto_text);
                    $("#table_cantidad", $tr).val(paquete.cantidad);
                    $("#table_cantidad", $tr).prop("disabled", true); 
                    $("#table_cantidad", $tr).attr("onchange", "refresh_paquete_change(this,'PAQUETE_CANTIDAD')");




                    $.each(renvio_array, function(key, value) {
                        $("#table_reenvio_id", $tr).append("<option value='" + value.reenvio_id + "'> Estado:  " + (value.estado_id ? value.estado_text : 'N/A') + ", Municipio: " + (value.municipio_id ? value.municipio_text : 'N/A') + ", Colonia: " + (value.colonia_id ? value.colonia_text : 'N/A') + "</option>\n");
                    });

                    $("#table_reenvio_id  option[value=" + paquete.reenvio_id + "]", $tr).prop('selected', true);
                    $("#table_reenvio_id", $tr).attr("onchange", "refresh_paquete_change(this,'PAQUETE_REENVIO')");


                    $("#table_peso", $tr).val(paquete.peso);
                    $("#table_peso", $tr).prop("disabled", true); 
                    $("#table_peso", $tr).attr("onchange", "refresh_paquete_change(this,'PAQUETE_PESO')");

                    //$("#table_impuesto",$tr).html(parseFloat(paquete.producto_detalle_impuesto));


                    //$("#table_seguro",$tr).html(paquete.seguro ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-times' aria-hidden='true'></i>");
                    $("#table_seguro", $tr).html(paquete.seguro ? '<input type="checkbox" checked="true" onchange="refresh_paquete_change(this,' + "'PAQUETE_SEGURO'" + ')" >' : '<input  type="checkbox" onchange="refresh_paquete_change(this, ' + "'PAQUETE_SEGURO'" + ')">');


                    $("#table_costo_seguro", $tr).html(paquete.costo_seguro);
                    $("#table_valor_declarado", $tr).val(paquete.valor_declarado);
                    $("#table_valor_declarado", $tr).attr("onchange", "refresh_paquete_change(this,'PAQUETE_VALOR_DECLARADO')");

                    $("#table_observacion", $tr).html(paquete.observaciones);



                    sum_peso_total = sum_peso_total + parseFloat(paquete.peso);

                    seguro_total = seguro_total + parseFloat(paquete.costo_seguro);
                    declarado_total = declarado_total + parseFloat(paquete.valor_declarado);

                    if (parseInt(paquete.tipo_producto_enviar) == 20)
                        peso_caja_total = peso_caja_total + parseFloat(paquete.peso);

                    if (parseInt(paquete.tipo_producto_enviar) == 20)
                        precio_caja_total = precio_caja_total + (parseFloat(paquete.precio_caja_unitario) * paquete.cantidad);

                    //if (parseInt(paquete.tipo_producto_enviar) == 30)
                    //    precio_caja_total = precio_caja_total + (parseFloat(paquete.precio_caja_unitario) * paquete.cantidad);

                    $tr.append("<td><button type='button' class='btn btn-warning btn-circle' onclick='refresh_paquete(this)'><i class='fa fa-trash'></i></button></td>");
                }
            }
        });

        $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));


        $('#envio-seguro_total').val(parseFloat(seguro_total).toFixed(2));
        $('#envio-seguro_total-label').html(btf.conta.money(seguro_total));
        //$("#peso_total").prop("disabled", true); 
        $('#peso_total').val(Number(Number(sum_peso_total).toFixed(2))).trigger('change');
        //console.log('total_________________',sum_peso_total);

        $total_v_declarado.val(declarado_total);
        renderizarPaquetes();

        //$('#peso_total').trigger('change');
    };


    /*===============================================
     * Actualiza la lista de paquetes
     *===============================================*/
    var eliminarProductosPorId = function(producto_id) {
        arrayPaqueteDeatlle = arrayPaqueteDeatlle.filter(paquete => Number(paquete.paquete_id) !== Number(producto_id));
    }




    var refresh_paquete = function(ele) {
        $ele_paquete_val = $(ele).closest('tr');

        $ele_paquete_id = $ele_paquete_val.attr("data-paquete_id");
        $ele_origen_id = $ele_paquete_val.attr("data-origen");



        eliminarProductosPorId($ele_paquete_id);
        //console.log("PAQUETE ANTES ", paquete_array);
        //return;
        $.each(paquete_array, function(key, paquete) {
            if (paquete) {
                if (Number(paquete.paquete_id) == Number($ele_paquete_id) && Number(paquete.origen) == Number($ele_origen_id)) {
                    if (paquete.origen == 1)
                        paquete_array.splice(key, 1);

                    if (paquete.origen == 2)
                        paquete_array.splice(key, 1);
                    paquete.status = 1;
                }
            }
        });
        //console.log("PAQUETE DES ", paquete_array);

        $(ele).closest('tr').remove();
        $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
        render_paquete_template();

    };

    var refresh_paquete_change = function(ele, inputChange) {

        $ele_paquete_val = $(ele);
        $ele_paquete = $(ele).closest('tr');
        $ele_paquete_detalle_id = $ele_paquete.attr("data-paquete_id");
        $ele_paquete_origen_id = $ele_paquete.attr("data-origen");

        $.each(paquete_array, function(key, paquete) {
            if (paquete.paquete_id == $ele_paquete_detalle_id && paquete.origen == $ele_paquete_origen_id) {

                switch (inputChange) {
                    case 'PAQUETE_CANTIDAD':
                        paquete.cantidad = $ele_paquete_val.val();
                        //paquete.impuesto_total = paquete.producto_detalle_impuesto ? paquete.cantidad * paquete.producto_detalle_impuesto : null;
                        break;
                    case 'PAQUETE_PESO':
                        paquete.peso = $ele_paquete_val.val();
                        //paquete.impuesto_total = paquete.producto_detalle_impuesto ? paquete.cantidad * paquete.producto_detalle_impuesto : null;
                        break;
                    case 'PAQUETE_VALOR_DECLARADO':
                        paquete.valor_declarado = $ele_paquete_val.val();
                        paquete.costo_seguro = paquete.seguro ? (costo_seguro_select * paquete.valor_declarado) / 100 : 0;
                        break;
                    case 'PAQUETE_SEGURO':
                        paquete.seguro = $ele_paquete_val.prop('checked') ? true : false;

                        $ele_paquete_val.prop('checked') ? paquete.costo_seguro = (costo_seguro_select * paquete.valor_declarado) / 100 : paquete.costo_seguro = 0;
                        break;

                    case 'PAQUETE_REENVIO':
                        paquete.reenvio_id = $ele_paquete_val.val() && $ele_paquete_val.val() != 0 ? $ele_paquete_val.val() : null;
                        break;
                    case 'VALOR_PAQUETE':


                        paquete.valoracion_paquete = parseFloat($ele_paquete_val.val());

                        break;
                }
            }
        });

        $inputEnvioDetalleArray.val(JSON.stringify(paquete_array));
        render_paquete_template();
    }
    /*====================================================
     *               FUNCION QUE CARGA TODO EL ARRAY
     *====================================================*/
    var init_paquete_list = function() {

        paquete_array = [];
        metodoPago_array = [];
        if ($envioID.val()) {

            $.get('<?= Url::to('envio-detalle-ajax') ?>', {
                'envio': $envioID.val()
            }, function(json) {
                console.log(json, 'scsdvsdvsdfbdfbd');

                $.each(json.rows, function(key, item) {
                    if (item.id) {
                        paquete = {
                            //"costo_libra" : 1000000,
                            "paquete_id": item.id,
                            "sucursal_id": item.sucursal_receptor_id,
                            "cliente_id": item.cliente_receptor_id,
                            "categoria_id": item.categoria_id,
                            "categoria_text": item.categoria,
                            "cantidad": item.cantidad,
                            "reenvio_id": item.reenvio_id,
                            "peso": item.peso ? item.peso : 0,
                            "valor_declarado": parseFloat(item.valor_declarado),
                            "producto_id": item.producto_id,
                            "producto_text": item.producto,
                            "costo_neto_extraordinario": item.costo_neto_extraordinario,
                            "is_costo_extraordinario": item.is_costo_extraordinario == 10 ? true : false,
                            "precio_caja_unitario": parseFloat(item.precio_caja_unitario),
                            "tipo_producto_enviar": item.tipo_producto_enviar,
                            //"producto_tipo"       : item.producto_tipo,
                            "observaciones": item.observaciones,
                            "seguro": item.seguro ? true : false,
                            "costo_seguro": item.costo_seguro,
                            "status": item.status,
                            "update": $envioID.val() ? 10 : 1,
                            "origen": 2,
                            'paquete_detalle': JSON.parse(item.paquete_detalle),
                        };
                    }
                    paquete_array.push(paquete);
                    //console.log(paquete_array, 'PAQUETE ARRAY');

                });

                $cliente_receptor.trigger('change');
                $sucursal_receptor_id.trigger('change');

                if ($isAplicaReenvio.val() == "10" || $isAplicaReenvio.val() == 10) {
                    $isAplicaReenvio.val(null);
                    $btnAplicaReenvio.trigger('click');
                }

                render_paquete_template();
            }, 'json');


            $.get('<?= Url::to('cobro-envio-ajax') ?>', {
                'envio_id': $envioID.val()
            }, function(metodo) {
                $.each(metodo.results, function(key, item) {
                    if (item.id) {
                        metodo = {
                            "metodo_id": metodoPago_array.length + 1,
                            "metodo_pago_id": item.metodo_pago,
                            "metodo_pago_text": metodoPagoList[item.metodo_pago],
                            "cantidad": item.cantidad,
                            "origen": 2,
                        };

                        metodoPago_array.push(metodo);
                        render_metodo_template();
                    }
                });
            });


            $.get('<?= Url::to('esys-direccion-ajax') ?>', {
                'envio_id': $envioID.val()
            }, function(esysDireccionJson) {
                if (esysDireccionJson.rows) {
                    $.each(esysDireccionJson.rows, function(key, item) {
                        reenvio = {
                            "reenvio_id": parseInt(item.id),
                            "cp": item.codigo_postal,
                            "estado_id": item.estado_id,
                            "estado_text": item.estado,
                            "municipio_id": item.municipio_id,
                            "municipio_text": item.municipio,
                            "colonia_id": item.colonia_id,
                            "colonia_text": item.colonia,
                            "direccion": item.direccion,
                            "n_interior": item.num_int,
                            "n_exterior": item.num_ext,
                            "referencia": item.referencia,
                            "status": 10,
                            "update": $envioID.val() ? 10 : 1,
                            "origen": 2

                        }

                        renvio_array.push(reenvio);
                        render_reenvio_template();
                        render_paquete_template();
                        $isAplicaReenvio.val(10);
                    });
                } else {
                    renvio_array = [];
                }
            });


            $.each(edit_load_sucursal, function(key, sucursal) {
                $is_sucursal = true;
                $.each(sucursalSelect, function(key2, sucursal_Select) {
                    if (sucursal_Select.id == sucursal.id)
                        $is_sucursal = $is_sucursal == false ? false : false;

                })

                if ($is_sucursal) {
                    var newOption = new Option(sucursal.nombre, sucursal.id, false, true);
                    $sucursal_receptor_id.append(newOption);
                    sucursalSelect.push(sucursal);
                }
            });



            $.each(edit_load_cliente, function(key, cliente) {
                $is_cliente = true;
                $.each(clienteSelect, function(key2, cliente_select) {
                    if (cliente_select.id == cliente.id)
                        $is_cliente = $is_cliente == false ? false : false;
                });

                if ($is_cliente) {
                    var newOption = new Option(cliente.nombre, cliente.id, false, true);
                    $cliente_receptor.append(newOption);
                    clienteSelect.push(cliente);
                }
            });

        }
    };
</script>