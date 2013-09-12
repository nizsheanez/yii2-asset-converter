Only for YII2 with the new Asset Manager, convert Less and Sass files to CSS whithout external tools and executable. The sass and less files are converted with PHP librairies
It replace the AssetConverter who use external tools.
The Less and Sass file are converted with time source files dependency.

##Requirements

YII 2.0

##Usage

1)Copy the source to your project in {app}/protected/extensions/asset_parser
2)Modify assetManager in your configuration file {app}/protected/config/main.php


~~~
[php]
        'assetManager' => array(
            'bundles' => require(__DIR__ . '/assets.php'),
            'converter'=>array(
                'class'=>'nizsheanez\assetConverter\Converter',
                'force'=>false
            )
        ),
~~~

'force'=>true : If you want convert your sass each time without time dependency

The sass files with extension .sass are converted to a .css file
The less files with extension .less are converted to a .css file
The scss file with extension .scss are converted to a .css file


Example of assets config file /protected/config/assets.php


~~~
[php]
<?php

return array(
	'app' => array(
		'basePath' => '@wwwroot',
		'baseUrl' => '@www',
		'css' => array(
			'css/bootstrap.min.css',
			'css/bootstrap-responsive.min.css',
			'css/site.css',
            'css/less_style.less',
            'css/sass_style.sass',
		),
		'js' => array(

		),
		'depends' => array(
			'yii',
		),
	),
);

~~~



##Resources

* github archives :  [https://github.com/nizsheanez/yii2-asset-converter/](https://github.com/nizsheanez/yii2-asset-converter/ "https://github.com/nizsheanez/yii2-asset-converter/")

##Q & A

###Where can find a installation example of this extension ?
Decompress the archive
[assetparser_example.zip](www.yiiframework.com/extension/assetparser/files/assetparser_example.zip "assetparser_example.zip")

copy to your yii/apps/assetparser

you need to change your apps/assetparser/.htaccess

Try it : http://localhost/yii2/apps/assetparser/

You have a sass file in yii2/apps/assetparser/css/sass_style.sass and less file in yii2/apps/assetparser/css/less_style.less

After running your application yii2/apps/assetparser/css/sass_style.css and yii2/apps/assetparser/css/less_style.css are generated

###Install with Composer

~~~

"require": {
    "nizsheanez/yii2-asset-converter": "dev-master",
},
"repositories":[
    {
        "type": "git",
        "url": "https://github.com/nizsheanez/yii2-asset-converter"
    }
],

php composer.phar update

~~~

1) Adapt the configuration file (main.php)
In my example : C:\www\xxx\app\config\main.php

~~~
[php]
<?php

'components' => array(

....

		'assetManager' => array(
            'bundles' => require(__DIR__ . '/assets.php'),
            'converter'=>array(
                'class'=>'nizsheanez\assetParser\Converter',
                'force'=>false,
                'dist' => 'compiled', //at which folder of @webroot put compiled files
                'parsers' => array(
                    'sass' => array( // file extension to parse
                        'class' => 'nizsheanez\assetParser\Sass',
                        'output' => 'css', // parsed output file type
                        'options' => array(
                            'cachePath' => '@app/runtime/cache/sass-parser' // optional options
                        ),
                    ),
                    'scss' => array( // file extension to parse
                        'class' => 'nizsheanez\assetParser\Sass',
                        'output' => 'css', // parsed output file type
                        'options' => array() // optional options
                    ),
                    'less' => array( // file extension to parse
                        'class' => 'nizsheanez\assetParser\Less',
                        'output' => 'css', // parsed output file type
                        'options' => array(
                            'auto' => true, // optional options
                        )
                    )
                )
            )
        ),
	),

.....

~~~
You need to update lessParserPath defintion to you specific environement
'lessParserPath' => ' __DIR__ . '/../../vendor/yii-ext/assetparser/vendors/lessphp/lessc.inc.php'


2) You need declare some classMap to YII, because your folder filepath are not standart with YII2 namespace

There are two possibilities

2a) Modifie your index.php  ( C:\www\xxx\web\index.php)


defined('YII_DEBUG') or define('YII_DEBUG', true);
~~~
[php]
<?php
require(__DIR__ . '/../vendor/yiisoft/yii2/framework/yii.php');

Yii::$classMap['app\extensions\assetparser\Converter'] =__DIR__ . '/../vendor/yii-ext/assetparser/Converter.php';
Yii::$classMap['app\extensions\assetparser\Parser'] =__DIR__ . '/../vendor/yii-ext/assetparser/Parser.php';
Yii::$classMap['app\extensions\assetparser\Less'] =__DIR__ . '/../vendor/yii-ext/assetparser/Less.php';
Yii::$classMap['app\extensions\assetparser\Sass'] =__DIR__ . '/../vendor/yii-ext/assetparser/Sass.php';

$config = require(__DIR__ . '/../app/config/main.php');
$application = new yii\web\Application($config);
$application->run();

~~~

2b) Modifie your main.php (  C:\www\xxx\app\config\main.php)

~~~
[php]
<?php
Yii::$classMap['app\extensions\assetparser\Converter'] =__DIR__ . '/../../vendor/yii-ext/assetparser/Converter.php';
Yii::$classMap['app\extensions\assetparser\Parser'] =__DIR__ . '/../../vendor/yii-ext/assetparser/Parser.php';
Yii::$classMap['app\extensions\assetparser\Less'] =__DIR__ . '/../../vendor/yii-ext/assetparser/Less.php';
Yii::$classMap['app\extensions\assetparser\Sass'] =__DIR__ . '/../../vendor/yii-ext/assetparser/Sass.php';

return array(
    'id' => 'hello',
    'basePath' => dirname(__DIR__),
    'components' => array(
.....

~~~
