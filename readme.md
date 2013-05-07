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
                'class'=>'app\extensions\assetparser\Converter',
                'force'=>false
            )
        ),
~~~

'Force'=>true : If you want convert your sass each time without time dependency

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


 * Project page : [https://github.com/athos99/assetparser](https://github.com/athos99/assetparser "https://github.com/athos99/assetparser")


##Q & A
It's possible to chose a another folder that {app}/protected/extensions/assetparser ?
Yes It Is !
It's possible to adapt assetparser to your spcific environement, it's little bit complex (You need version 1.0.1)

For exeample :
Yii Framework : C:\www\xxx\vendor\yiisoft\yii2\framework
Web application: C:\www\xxx\web\index.php
assertparser extension: C:\www\xxx\vendor\yii-ext\assetparser
Config file : C:\www\xxx\app\config\main.php

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
                'class'=>'app\extensions\assetparser\Converter',
                'force'=>false,
                 'parsers' => array(
                    'sass' => array( // file extension to parse
                        'class' => 'app\extensions\assetparser\Sass',
                        'output' => 'css', // parsed output file type
                        'options' => array(
                            'cachePath' => '@app/runtime/cache/sass-parser' // optional options
                        ),
                    ),
                    'scss' => array( // file extension to parse
                        'class' => 'app\extensions\assetparser\Sass',
                        'output' => 'css', // parsed output file type
                        'options' => array() // optional options
                    ),
                    'less' => array( // file extension to parse
                        'class' => 'app\extensions\assetparser\Less',
                        'output' => 'css', // parsed output file type
                        'options' => array(
                            'auto' => true, // optional options
                            'lessParserPath' => __DIR__ . '/../../vendor/yii-ext/assetparser/vendors/lessphp/lessc.inc.php'
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


