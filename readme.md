Only for YII2 with the new Asset Manager, convert Less and Sass files to CSS whithout external tools and executable.
The sass and less files are converted with PHP librairies
It replace the AssetConverter who use external tools.
The Less and Sass file are converted with time source files dependency.

###Requirements

YII 2.0

###Usage

1) Install with Composer

~~~php

"require": {
    "nizsheanez/yii2-asset-converter": "1.*",
},

php composer.phar update

~~~

2) Modify assetManager in your configuration file {app}/protected/config/main.php

~~~php
    'assetManager' => array(
        'bundles' => require(__DIR__ . '/assets.php'),
        'converter'=>array(
            'class'=>'nizsheanez\assetConverter\Converter',
        )
    ),
~~~

3) Create .gitignore in
4) Enjoy!

- Files with extension .sass are converted to a .css file
- Files with extension .less are converted to a .css file
- Files with extension .scss are converted to a .css file

###Example of assets config file /protected/config/assets.php

~~~php

return array(
	'app' => array(
		'basePath' => '@webroot',
		'baseUrl' => '@web',
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

###Where is compiled files?

By default it present at @webroot/compiled
But you can change it by destinationDir property from config


### Full configuration

~~~php

'components' => array(
	'assetManager' => array(
        'converter'=>array(
            'class'=>'nizsheanez\assetConverter\Converter',
            'force'=>false, // true : If you want convert your sass each time without time dependency
            'destinationDir' => 'compiled', //at which folder of @webroot put compiled files
            'parsers' => array(
                'sass' => array( // file extension to parse
                    'class' => 'nizsheanez\assetConverter\Sass',
                    'output' => 'css', // parsed output file type
                    'options' => array(
                        'cachePath' => '@app/runtime/cache/sass-parser' // optional options
                    ),
                ),
                'scss' => array( // file extension to parse
                    'class' => 'nizsheanez\assetConverter\Sass',
                    'output' => 'css', // parsed output file type
                    'options' => array() // optional options
                ),
                'less' => array( // file extension to parse
                    'class' => 'nizsheanez\assetConverter\Less',
                    'output' => 'css', // parsed output file type
                    'options' => array(
                        'auto' => true, // optional options
                    )
                )
            )
        )
    ),
),

~~~

Also, for SCSS files you can use alternate configuration:

~~~php
'components' => array(
    'assetManager' => array(
            'converter'=>array(
                // ...
                'parsers' => array(
                    // ...
                    'scss' => array( // file extension to parse
                        'class' => 'nizsheanez\assetConverter\Scss',
                        'output' => 'css', // parsed output file type
                        'options' => array( // optional options
                            'enableCompass' => true, // default is true
                            'importPaths' => array(), // import paths, you may use path alias here, 
                                // e.g., `['@path/to/dir', '@path/to/dir1', ...]`
                            'lineComments' => false, // if true — compiler will place line numbers in your compiled output
                            'outputStyle' => 'nested', // May be `compressed`, `crunched`, `expanded` or `nested`,
                                // see more at http://sass-lang.com/documentation/file.SASS_REFERENCE.html#output_style
                        ),
                    ),
                ),
            ),
        ),
    // ...
~~~
