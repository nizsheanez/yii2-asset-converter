<?php
namespace nizsheanez\assetConverter;

use Yii;

class Sass extends Parser
{

    /**
     * @var string to the class pointing to where sass parser is located.
     */
    public $sassParserClass = '\SassParser';

    /**
     * @var string to the sass parser cache
     */

    public $cachePath = '@app/runtime/cache/sass-parser';

    /**
     * Parse a Sass file to CSS
     */
    public function parse($src, $dst, $options)
    {
        if (!empty($options['cachePath'])) {
            $options['cache_location'] = Yii::getAlias($options['cachePath']);

            if (!is_dir($options['cache_location'])) {
                mkdir($options['cache_location'], 0777, true);
            }
        }
        $parser = Yii::createObject($this->sassParserClass, $options);
        file_put_contents($dst, $parser->toCss($src));
    }
}
