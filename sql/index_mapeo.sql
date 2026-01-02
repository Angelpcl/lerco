-- CREATE INDEX idx_viaje_detalle_viaje_id ON viaje_detalle(viaje_id);
-- CREATE INDEX idx_viaje_detalle_paquete_id ON viaje_detalle(paquete_id);
CREATE INDEX idx_viaje_detalle_tracked ON viaje_detalle(tracked);


CREATE INDEX idx_envio_detalle_id ON envio_detalle(id);
CREATE INDEX idx_envio_detalle_sucursal_receptor_id ON envio_detalle(sucursal_receptor_id);


CREATE INDEX idx_esys_direccion_cuenta_id ON esys_direccion(cuenta_id, cuenta);
CREATE INDEX idx_esys_direccion_estado_id ON esys_direccion(estado_id);
CREATE INDEX idx_esys_direccion_municipio_id ON esys_direccion(municipio_id);

CREATE INDEX idx_esys_lista_desplegable_estado ON esys_lista_desplegable(id_2, label);
CREATE INDEX idx_esys_lista_desplegable_municipio ON esys_lista_desplegable(id_2, param1, label);


CREATE INDEX idx_ruta_sucursal_sucursal_id ON ruta_sucursal(sucursal_id);
CREATE INDEX idx_ruta_sucursal_ruta_id ON ruta_sucursal(ruta_id);



CREATE INDEX idx_ruta_id ON ruta(id);
CREATE INDEX idx_ruta_orden ON ruta(orden);
