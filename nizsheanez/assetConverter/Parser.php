<?php
namespace nizsheanez\assetConverter;

use yii\base\Object;

abstract class Parser extends Object
{
    /**
     * Parse a asset file.
     *
     * @param string $src source file path
     * @param string $dst destination file path
     * @param array $options parser options
     * @return mixed
     */
    abstract public function parse($src, $dst, $options);
}