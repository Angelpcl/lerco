<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>


<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td align="left" style="font-weight: bold; font-size: 18px; ">
	 		#PAQUETE
	 	</td>
	</tr>
	<tr>
		<td>
			<h1 style="font-size: 35px"><?= $model["tracked"]?></h1>
		</td>
		<td align="right">
			<?= Html::img('@web/img/sea-black.png', ["height"=>"100px", "style" => "margin-top: -50px" ]) ?>
		</td>
	</tr>
	<tr>
		<td>
			<strong>RUTA: </strong> <small><strong><?= $model["ruta_nombre"]  ?></strong></small>
		</td>
		<td>
			<strong>SUCURSAL: </strong> <small style="font-size: 10px"><strong><?= $model["sucursal_receptor"]  ?></strong></small>
		</td>
	</tr>
</table>
<hr style="color: black">
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td>
			<h4>ENVIA: <small><?= $model["emisor_nombre"]  ?></small></h4>
			<h4>TELEFONO: <small><?= $model["emisor_telefono"]  ?> / <?= $model["emisor_telefono_movil"]  ?></small></h4>
			<h4>RECIBE: <small><?= $model["receptor_nombre"]  ?></small></h4>
			<h4>TELEFONO: <small><?= $model["receptor_telefono"]  ?> / <?= $model["receptor_telefono_movil"]  ?></small></h4>
		</td>
		<td align="center">
			<h2>PIEZAS</h2>
			<h4><?= $model["cantidad_piezas"]  ?></h4>
		</td>
	</tr>
</table>
<hr style="color: black">
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
	<tr>
		<td>
			<h4>DESCRIPCIÓN: </h4>
		</td>
		<td align="center">
			<p style="font-size: 9px"><?= $model["observaciones"]  ?> </p>
		</td>
	</tr>
</table>





