<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class TableExportAsset extends AssetBundle
{
    public $sourcePath = '@bower/tableexport.jquery.plugin';

    public $css = [
    ];

    public $js = [
        'tableExport.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}