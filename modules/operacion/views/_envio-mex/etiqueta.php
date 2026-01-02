<?php
use yii\helpers\Url;
use Da\QrCode\QrCode;


?>
<?php for ($i=0; $i < $model->cantidad; $i++) { ?>

<?php
$tracked_generado = $model->tracked ."/". ($i + 1);
$qrCode = (new QrCode( $tracked_generado ))
            ->setSize(50)
            ->setMargin(0)
            ->setErrorCorrectionLevel('medium');

$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();

 ?>
	<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; ">
		<tr>
			<td align="center" style="font-weight: bold; font-size: 18px; ">
		 		<?= substr($model->tracked,  4)  ?>/<?= $i + 1 ?>
		 	</td>
		</tr>
		<tr>
			<td align="center" valign="top" ><img  src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
	    </tr>
	    <tr >
	    	<td align="center" style="font-weight: bold; font-size: 10px; padding-left:  10px; padding-right: 15px;"><?= $model->producto->nombre  ?></td>
	    </tr>
	</table>
<?php } ?>




