<?php

namespace nizsheanez\assetConverter;

use Yii;

/**
 * Class Scss
 * @package nizsheanez\assetConverter
 * @author Andrey Izman <izmanw@gmail.com>
 */
class Scss extends Parser
{
    /**
     * @var bool compass support
     */
    public $enableCompass = true;

    /**
     * @var array paths to import files
     */
    public $importPaths = [];


    /**
     * Parse a Scss file to CSS
     */
    public function parse($src, $dst, $options)
    {
        $options['importPaths'] = !empty($options['importPaths']) ? $options['importPaths'] : $this->importPaths;
        $options['enableCompass'] = isset($options['enableCompass']) ? $options['enableCompass'] : $this->enableCompass;

        $parser = new \Leafo\ScssPhp\Compiler();
        if (!empty($options['importPaths']) && is_array($options['importPaths'])) {
            $paths = [''];
            foreach ($options['importPaths'] as $path) {
                $paths[] = Yii::getAlias($path);
            }
            $parser->setImportPaths($paths);
        }

        if ($options['enableCompass']) {
            new \scss_compass($parser);
        }

        if (!file_exists($src)) {
            throw new \Exception("Failed to open file \"$dst\"");
        }

        file_put_contents($dst, $parser->compile(file_get_contents($src)));
    }
}
