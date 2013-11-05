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

    /**
     * @var array the commands that are used to perform the asset conversion.
     * The keys are the asset file extension names, and the values are the corresponding
     * target script types (either "css" or "js") and the commands used for the conversion.
     */
    public $commands = [
        'less' => ['css', 'lessc {from} {to} 2>&1 --no-color'],
        'scss' => ['css', 'sass {from} {to}'],
        'sass' => ['css', 'sass {from} {to}'],
        'styl' => ['js', 'stylus < {from} > {to}'],
        'coffee' => ['js', 'coffee -p {from} > {to}'],
        'ts' => ['js', 'tsc --out {to} {from}'],
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
     * Converts a given asset file into a CSS or JS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to.
     * @return string the converted asset file path, relative to $basePath.
     */
    public function convert($asset, $basePath)
    {
        $extensionPos = strrpos($asset, '.');
        if ($extensionPos === false) {
            return parent::convert($asset, $basePath);
        }

        $ext = substr($asset, $extensionPos + 1);
        if (!isset($this->parsers[$ext])) {
            return parent::convert($asset, $basePath);
        }

        $parserConfig = ArrayHelper::merge($this->defaultParsersOptions[$ext], $this->parsers[$ext]);
        $resultFile = substr($asset, 0, $extensionPos + 1) . $parserConfig['output'];

        $from = $basePath . '/' . $asset;
        $to = $basePath . '/' . $this->destinationDir . '/' . $resultFile;

        if (!$this->needRecompile($from, $to)) {
            return $this->destinationDir . '/' . $resultFile;
        }

        $this->checkDestinationDir($resultFile);

        $asConsoleCommand = isset($parserConfig['asConsoleCommand']) && $parserConfig['asConsoleCommand'];
        if ($asConsoleCommand) { //can't use parent function because it not support destination directory
            $this->runCommand($ext, $from, $to);
        } else {
            $parser = new $parserConfig['class']($parserConfig['options']);
            $parserOptions = isset($parserConfig['options']) ? $parserConfig['options'] : array();
            $parser->parse($from, $to, $parserOptions);
        }

        if (YII_DEBUG) {
            Yii::info("Converted $asset into $resultFile ", __CLASS__);
        }

        return $this->destinationDir . '/' . $resultFile;
    }

    public function needRecompile($from, $to)
    {
        return $this->force || (@filemtime($to) < filemtime($from));
    }

    public function runCommand($ext, $from, $to)
    {
        if (!isset($this->commands[$ext])) {
            throw new Exception('No template for console command for parse file with extension ' . $ext);
        }
        list ($ext, $command) = $this->commands[$ext];
        $output = [];
        $command = strtr($command, [
            '{from}' => escapeshellarg($from),
            '{to}' => escapeshellarg($to),
        ]);
        exec($command, $output, $exit_code);
        if ($exit_code == 1) {
            throw new Exception(array_shift($output));
        }
    }

    public function checkDestinationDir($resultFile)
    {
        $dist = Yii::getAlias('@webroot/' . $this->destinationDir);
        $distDir = dirname("{$dist}/$resultFile");
        if (!is_dir($distDir)) {
            mkdir($distDir, '0755', true);
        }
    }
}
