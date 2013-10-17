<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2011 Michel Bobillier Aka Athos99
 * @license GNU General Public License v3 http://www.gnu.org/licenses/gpl.html
 * @version 1.0.0 (2011-05-05)
 */

namespace philippfrenzel\assetConverter;

use Yii;

class Converter extends \yii\web\AssetConverter
{
    /**
     * @var array parsers
     */
    public $parsers = array(
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
                'auto' => true // optional options
            )
        )
    );

    /**
     * @var array the commands that are used to perform the asset conversion.
     * The keys are the asset file extension names, and the values are the corresponding
     * target script types (either "css" or "js") and the commands used for the conversion.
     */
    public $commands = array(
        'less' => array('css', 'lessc {from} {to}'),
        'scss' => array('css', 'sass {from} {to}'),
        'sass' => array('css', 'sass {from} {to}'),
        'styl' => array('js', 'stylus < {from} > {to}'),
    );


    /**
     * @var boolean if true the asset will always be published
     */
    public $force = false;

    /**
     * @var string some directory in @webroot for compiled files. Will using like Yii::getAlias('@webroot/' . $this->destinationDir)
     */
    public $destinationDir = 'compiled';

    /**
     * Converts a given asset file into a CSS or JS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to.
     * @return string the converted asset file path, relative to $basePath.
     */
    public function convert($asset, $basePath)
    {
        $pos = strrpos($asset, '.');
        if ($pos === false) {
            return parent::convert($asset, $basePath);
        }

        $ext = substr($asset, $pos + 1);
        if (!isset($this->parsers[$ext])) {
            return parent::convert($asset, $basePath);
        }

        $parserConfig = $this->parsers[$ext];
        $resultFile = substr($asset, 0, $pos + 1) . $parserConfig['output'];

        if ($this->force || (@filemtime("{$this->destinationDir}/$resultFile") < filemtime("$basePath/$asset"))) {
            $this->checkDestinationDir($resultFile);
            $parser = new $parserConfig['class']($parserConfig['options']);
            $parserOptions = isset($parserConfig['options']) ? $parserConfig['options'] : array();
            $parser->parse("$basePath/$asset", "{$this->destinationDir}/$resultFile", $parserOptions);

            if (YII_DEBUG) {
                Yii::trace("Converted $asset into $resultFile ", __CLASS__);
            }

            return $this->destinationDir . '/' . $resultFile;
        }

        return $asset;
    }

    public function checkDestinationDir($resultFile)
    {
        $dist = Yii::getAlias('@webroot/' . $this->destinationDir);
        $distDir  = dirname("{$dist}/$resultFile");
        if (!is_dir($distDir)) {
            mkdir($distDir, '0755', true);
        }
    }
}
