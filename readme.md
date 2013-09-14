Only for YII2 with the new Asset Manager, convert Less and Sass files to CSS whithout external tools and executable. The sass and less files are converted with PHP librairies
It replace the AssetConverter who use external tools.
The Less and Sass file are converted with time source files dependency.

###Requirements

YII 2.0

###Usage

1) Install with Composer

~~~
[php]

"require": {
    "nizsheanez/yii2-asset-converter": "dev-master",
},

php composer.phar update

~~~

2) Modify assetManager in your configuration file {app}/protected/config/main.php

~~~
[php]
    'assetManager' => array(
        'bundles' => require(__DIR__ . '/assets.php'),
        'converter'=>array(
            'class'=>'nizsheanez\assetConverter\Converter',
        )
    ),
~~~

3) Enjoy!

Files with extension .sass are converted to a .css file
Files with extension .less are converted to a .css file
Files with extension .scss are converted to a .css file



###Example of assets config file /protected/config/assets.php

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

### Full configuration

~~~
[php]
<?php

'components' => array(

....

		'assetManager' => array(
            'bundles' => require(__DIR__ . '/assets.php'),
            'converter'=>array(
                'class'=>'nizsheanez\assetParser\Converter',
                'force'=>false, //'force'=>true : If you want convert your sass each time without time dependency
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
