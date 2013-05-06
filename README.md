/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2011 Michel Bobillier Aka Athos99
 * @license GNU General Public License v3 http://www.gnu.org/licenses/gpl.html
 * @version 1.0.0 (2011-05-05)
 */


Only for YII2 with the new Asset Manager, convert Less and Sass files to CSS whithout external tools and executable. The sass and less files are converted with PHP librairies
It replace the AssetConverter who use external tools.
The Less and Sass file are converted with time source files dependency.

##Requirements

YII 2.0

##Usage

1)Copy the source to your project in /protected/extensions/asset_parser

2)Modify assetManager in your configuration file /protected/config/main.php


~~~
[php]
        'assetManager' => array(
            'bundles' => require(__DIR__ . '/assets.php'),
            'converter'=>array(
                'class'=>'app\extensions\assetparser\Converter',
                'force'=>false
            )
        ),
~~~

'Force'=>true : If you want convert your sass each time without time dependency

The sass files with extension .sass are converted to a .css file
The less files with extension .less are converted to a .css file
The scss file with extension .scss are converted to a .css file



