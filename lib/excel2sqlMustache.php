<?php

namespace Excel2sql;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;


class Excel2sqlMustache {


    /**
     * @var Mustache_Engine|null
     */
    protected $mustache = null;

    /**
     * set default mustache parameters
     */
    public function __construct()
    {
        $this->mustache = new Mustache_Engine(array(
            'template_class_prefix' => '__MyTemplates_',
            'cache' => dirname(__FILE__).'/tmp/cache/mustache',
            'cache_file_mode' => 0666, // Please, configure your umask instead of doing this :)
            'cache_lambda_templates' => true,
            'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ .'/views'),
            'escape' => function($value) {
                return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
            },
            'charset' => 'UTF-8',
            'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
            'strict_callables' => true,
        ));
    }

    /**
     * @param $template
     * @param $data
     * @return string
     */
    public function render($template, $data){
        $tpl = $this->mustache->loadTemplate($template); // loads __DIR__.'/views/$template.mustache';
        return $tpl->render($data);
    }

}

