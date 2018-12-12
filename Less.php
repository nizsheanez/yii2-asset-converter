<?php
namespace nizsheanez\assetConverter;

use Yii;
use yii\caching\FileCache;
use yii\base\Event;

class Less extends Parser
{
    /** Name of the event that will be sent right before a compile. It will include filenames and the compiler instance */
    const EVENT_BEFORE_COMPILE = 'beforeCompile';

    /** @var bool           true to automatically try to determine what to compile using the file cache manager */
    public $auto            = false;

    /** @var bool           true to compress the output */
    public $compressed      = false;

    /** @var bool           If true, adjust urls to be relative, false means don't touch */
    public $relativeUrls    = true;

    /** @var array           In this dirs less compiler find @import files */
    public $importDirs      = [];    
    
    /**
     * Set of variables to register and that will be available in the less files.
     * Note: The value should be in less format (strings are double quoted etc)
     * eg "variables" => ['some-variable' => '"variable quoted content"']
     *
     * On top of these 2 others will be added:
     * - published-url: Full url of the file currently being compiled
     * - published-base-url: Base Url of the assets folder
     *
     * @var array
     */
    public $variables       = [];

    /**
     * Temporary runtime data so the event handlers can access it
     * @var array
     */
    public $runtime         = null;

    /**
     * Parse a Less file to CSS
     */
    public function parse($src, $dst, $options)
    {
        $this->auto = isset($options['auto']) ? $options['auto'] : $this->auto;

        $variables = $this->variables;
        $assetManager = Yii::$app->assetManager;

        // Final url of the file being compiled
        $assetUrl = substr($dst, strpos($assetManager->basePath, $assetManager->baseUrl));
        $variables['published-url'] = '"' . $assetUrl . '"';

        // Root for the published folder
        $variables['published-base-url'] = '"/' . implode('/', array_slice(explode('/', ltrim($assetUrl, '/')), 0, 2)) . '"';

        $less = new \lessc();
        $less->setVariables($variables);
        $less->setImportDir(array_map(function($dir){ return Yii::getAlias($dir); }, $this->importDirs));

        // Compressed setting
        if ($this->compressed) {
            $less->setFormatter('compressed');
        }
        \Less_Parser::$default_options['compress'] = $this->compressed;
        \Less_Parser::$default_options['relativeUrls'] = $this->relativeUrls;

        // Send out pre-compile event
        $event = new \yii\base\Event();
        $this->runtime = ['sourceFile' => $src, 'destinationFile' => $dst, 'compiler' => $less];
        $event->sender = $this;
        Event::trigger($this, self::EVENT_BEFORE_COMPILE, $event);

        try {
            if ($this->auto) {
                /* @var FileCache $cacheMgr */
                $cacheMgr = Yii::createObject('yii\caching\FileCache');
                $cacheMgr->init();
                $cacheId = 'less#' . $dst;
                $cache = $cacheMgr->get($cacheId);
                if ($cache === false || (@filemtime($dst) < @filemtime($src))) {
                    $cache = $src;
                }
                $newCache = $less->cachedCompile($cache);

                if (!is_array($cache) || ($newCache["updated"] > $cache["updated"])) {
                    $cacheMgr->set($cacheId, $newCache);
                    file_put_contents($dst, $newCache['compiled']);
                }
            } else {
                $less->compileFile($src, $dst);
            }

            // If needed, respect the users configuration
            if ($assetManager->fileMode !== null)
                @chmod($dst, $assetManager->fileMode);

            unset($this->less);
        } catch (\Exception $e) {
            throw new \Exception(__CLASS__ . ': Failed to compile less file : ' . $e->getMessage() . '.');
        }
    }
}
