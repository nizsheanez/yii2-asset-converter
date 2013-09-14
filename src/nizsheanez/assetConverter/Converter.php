<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2011 Michel Bobillier Aka Athos99
 * @license GNU General Public License v3 http://www.gnu.org/licenses/gpl.html
 * @version 1.0.0 (2011-05-05)
 */

namespace nizsheanez\assetConverter;
use Yii;
use yii\base\Component;
use yii\web\IAssetConverter;

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
        if (!$this->destinationDir) {
            throw new \Exception('$destinationDir property must be specify');
        }

        $extensionPos = strrpos($asset, '.');
        if ($extensionPos === false) {
            return $asset;
        }

        $ext = substr($asset, $extensionPos + 1);
        if (!isset($this->parsers[$ext])) {
            return parent::convert($asset, $basePath);
        }

        $parserConfig = $this->parsers[$ext];
        $resultFile = substr($asset, 0, $extensionPos + 1) . $parserConfig['output'];

        if ($this->force || (@filemtime("$basePath/$resultFile") < filemtime("$basePath/$asset"))) {
            $this->checkDestinationDir($resultFile);
            $parser = new $parserConfig['class']($parserConfig['options']);
            $parserOptions = isset($parserConfig['options']) ? $parserConfig['options'] : array();
            $parser->parse("$basePath/$asset", "{$this->destinationDir}/$resultFile", $parserOptions);

            if (YII_DEBUG) {
                Yii::info("Converted $asset into $resultFile ", __CLASS__);
            }
        }

        return $this->destinationDir . '/' . $resultFile;
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
