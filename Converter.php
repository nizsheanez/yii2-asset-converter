<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2011 Michel Bobillier Aka Athos99
 * @license GNU General Public License v3 http://www.gnu.org/licenses/gpl.html
 * @version 1.0.0 (2011-05-05)
 */

namespace nizsheanez\assetConverter;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Converter extends \yii\web\AssetConverter
{
    /**
     * @var array parsers
     */
    protected $defaultParsersOptions = [
        'sass' => [ // file extension to parse
            'class' => 'nizsheanez\assetConverter\Sass',
            'output' => 'css', // parsed output file type
            'options' => [
                'cachePath' => '@app/runtime/cache/sass-parser' // optional options
            ],
        ],
        'scss' => [ // file extension to parse
            'class' => 'nizsheanez\assetConverter\Sass',
            'output' => 'css', // parsed output file type
            'options' => [] // optional options
        ],
        'less' => [ // file extension to parse
            'class' => 'nizsheanez\assetConverter\Less',
            'output' => 'css', // parsed output file type
            'options' => [
                'auto' => true // optional options
            ]
        ]
    ];

    public $parsers = [];

    /**
     * @var boolean if true the asset will always be published
     */
    public $force = false;

    /**
     * @var string some directory in @webroot for compiled files. Will using like Yii::getAlias('@webroot/' . $this->destinationDir)
     */
    public $destinationDir = 'compiled';

    /**
     * @var string permissions to assign to $destinationDir.
     */
    public $destinationDirPerms = 0755;

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

        $parserConfig = ArrayHelper::merge($this->defaultParsersOptions[$ext], $this->parsers[$ext]);

        $this->destinationDir = $this->destinationDir ? trim($this->destinationDir, '/') : '';
        $resultFile = $this->destinationDir . '/' . ltrim(substr($asset, 0, $pos + 1), '/') . $parserConfig['output'];

        $from = $basePath . '/' . ltrim($asset, '/');
        $to = $basePath . '/' . $resultFile;

        if (!$this->needRecompile($from, $to)) {
            return $resultFile;
        }

        $this->checkDestinationDir($basePath, $resultFile);

        $asConsoleCommand = isset($parserConfig['asConsoleCommand']) && $parserConfig['asConsoleCommand'];
        if ($asConsoleCommand) { //can't use parent function because it not support destination directory
            if (isset($this->commands[$ext])) {
                list ($distExt, $command) = $this->commands[$ext];
                $this->runCommand($command, $basePath, $asset, $resultFile);
            }
        } else {
            $parser = new $parserConfig['class']($parserConfig['options']);
            $parserOptions = isset($parserConfig['options']) ? $parserConfig['options'] : array();
            $parser->parse($from, $to, $parserOptions);
        }

        if (YII_DEBUG) {
            Yii::info("Converted $asset into $resultFile ", __CLASS__);
        }

        return $resultFile;
    }

    public function needRecompile($from, $to)
    {
        return $this->force || (@filemtime($to) < filemtime($from));
    }

    public function checkDestinationDir($basePath, $file)
    {
        $dstDir = dirname($basePath . '/' . $file);
        if (!is_dir($dstDir)) {
            mkdir($dstDir, $this->destinationDirPerms, true);
            $assetManager = \Yii::$app->assetManager;
            if ($assetManager->dirMode)
                @chmod($dstDir, $assetManager->dirMode);
        }
    }
}
