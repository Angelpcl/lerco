<?php

use yii\helpers\Url;
use Da\QrCode\QrCode;

$qrCode = (new QrCode($model->folio))
            ->setSize(150)
            ->setMargin(0)
            ->setErrorCorrectionLevel('medium');
$code = [];
$code['qrBase64'] =  $qrCode->writeDataUri();
?>
<table width="100%"  style="font-family: “Helvetica Neue”, Arial, sans-serif; font-size: 14px;margin: 0 auto;">
	<tr>
	 	<td  align="center"  style="font-size: 12px; font-weight: bold;">
	 		Folio: <?= $model->folio ?>
	 	</td>
    </tr>
	<tr >
		<td align="center" valign="top" width="50%"><img src="<?= $code['qrBase64'] ?>" alt='QR' class="qr-code" /></td>
	</tr>
</table>









