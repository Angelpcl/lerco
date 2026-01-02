CREATE INDEX idx_viaje_detalle_id
ON viaje_detalle (id);
CREATE INDEX idx_viaje_detalle_viaje_id
ON viaje_detalle (viaje_id);
CREATE INDEX idx_viaje_detalle_paquete_id
ON viaje_detalle (paquete_id);
CREATE INDEX idx_viaje_detalle_viaje_paquete
ON viaje_detalle (viaje_id, paquete_id);