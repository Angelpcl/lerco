<?php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class FullCalendarAsset extends AssetBundle
{
    public $sourcePath = '@bower/fullcalendar';

    public $css = [
        'dist/fullcalendar.min.css',

    ];

    public $cssOptions = [
        'type' => 'text/css',
    ];

    public $js = [
        'lib/moment.min.js',
        'lib/jquery-ui.custom.min.js',
        'dist/fullcalendar.min.js',
        'dist/lang/es.js',

    ];

}
