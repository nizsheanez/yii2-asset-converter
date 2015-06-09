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
     * @var bool if is true, compiler will place line numbers in your compiled output
     */
    public $lineComments = false;

    /**
     * @var string output style
     */
    public $outputStyle = 'nested';

    /**
     * @var array defined formatters
     */
    protected $formatters = [
        'compressed', 'crunched', 'expanded', 'nested',
    ];


    /**
     * Parse a Scss file and convert it into CSS
     *
     * @param string $src source file path
     * @param string $dst destination file path
     * @param array $options parser options
     * @return mixed
     * @throws \Exception
     */
    public function parse($src, $dst, $options)
    {
        $this->importPaths   = !empty($options['importPaths']) ? $options['importPaths'] : $this->importPaths;
        $this->enableCompass = isset($options['enableCompass']) ? $options['enableCompass'] : $this->enableCompass;
        $this->lineComments  = isset($options['lineComments']) ? $options['lineComments'] : $this->lineComments;
        $this->outputStyle   = isset($options['outputStyle']) ? $options['outputStyle'] : $this->outputStyle;
        $this->outputStyle   = strtolower($this->outputStyle);

        $parser = new \Leafo\ScssPhp\Compiler();
        if (!empty($this->importPaths) && is_array($this->importPaths)) {
            $paths = [''];
            foreach ($this->importPaths as $path) {
                $paths[] = Yii::getAlias($path);
            }
            $parser->setImportPaths($paths);
        }

        if (in_array($this->outputStyle, $this->formatters)) {
            if ($this->lineComments && in_array($this->outputStyle, ['compressed', 'crunched'])) {
                $this->lineComments = false;
            }
            $parser->setFormatter('Leafo\\ScssPhp\\Formatter\\' . ucfirst($this->outputStyle));
        }

        if ($this->enableCompass) {
            new \scss_compass($parser);
        }

        if (!file_exists($src)) {
            throw new \Exception("Failed to open file \"$src\"");
        }

        if ($this->lineComments) {
            $content = self::insertLineComments($src, self::getRelativeFilename($src, $dst));
            file_put_contents($dst, self::parseLineComments($parser->compile($content, $src)));
        } else {
            file_put_contents($dst, $parser->compile(file_get_contents($src), $src));
        }
    }

    /**
     * Returns the relative filename of $src to $dst
     *
     * @param string $src
     * @param string $dst
     * @return string
     */
    protected static function getRelativeFilename($src, $dst) {
        $src = explode('/', $src);
        $dst = explode('/', $dst);
        for ($index=1, $count=count($src); $index<$count; $index++) {
            if (!isset($src[$index]) || !isset($dst[$index]) || $src[$index] !== $dst[$index]) {
                break;
            }
        }
        return str_repeat('../', count($dst)-$index-1) . implode('/', array_slice($src, $index));
    }

    /**
     * Inserts the line numbers into comments
     *
     * @param string $src
     * @param string $filename
     * @return string
     */
    protected static function insertLineComments($src, $filename)
    {
        $lines = explode("\n", preg_replace_callback('~/\*.+?\*/~sx',
            function($m) {return str_repeat("\n", substr_count($m[0], "\n"));}, file_get_contents($src)));

        foreach ($lines as $no => $line) {
            $no++;
            $newLine = '';
            for ($pos = 0; ($pos = mb_strpos($line, '{')) !== false;) {
                $comment = "/* line: $no, $filename */";
                $newLine .= mb_substr($line, 0, $pos+1) . $comment;
                $line = mb_substr($line, $pos+1);
            }
            if (!empty($newLine)) {
                $lines[$no-1] = $newLine . $line;
            }
        }
        return implode("\n", $lines);
    }

    /**
     * Parses the line numbers comments and moves them into the header of the block
     *
     * @param string $content
     * @return string
     */
    protected static function parseLineComments($content)
    {
        $content = preg_replace('~(/\*.+?\*/)([\x0b-\x20]+)~sx', "\\1\n\\2", $content);
        $lines = explode("\n", $content);
        unset($content);
        $begin = 0;
        foreach ($lines as $id => $line) {
            if (empty($line)) {
                if ($id > 0 && $id === $begin+1) {
                    $begin = $id;
                }
            } elseif (strpos($line, '}') !== false) {
                $begin = $id;
            } elseif (strpos($line, '{') !== false) {
                $begin = $id > 0 ? $id-1 : 0;
            } elseif (strpos($line, '/*') !== false) {
                unset($lines[$id]);
                if ($begin > 0) {
                    for (; !isset($lines[$begin]); $begin--);
                    for ($next=$begin+1; !isset($lines[$next]); $next++);
                    $lines[$begin] .= "\n" . preg_replace('~^(\s+)*.+$~', '\1', $lines[$next]) . ltrim($line);
                } else {
                    $lines[$begin] =  preg_replace('~^(\s+)*.+$~', '\1', $lines[$begin]) . ltrim($line) . "\n" . $lines[$begin];
                }
            }
        }
        return implode("\n", $lines);
    }
}
