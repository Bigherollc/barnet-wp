<?php

class BarnetScriptManager
{
    const CSS_TYPE = 1;
    const JS_TYPE = 2;

    protected $type;
    protected $version;
    protected $defer = array();
    protected $css = array();
    protected $js = array();

    public function __construct($type = 'admin', $version = '1.0.0')
    {
        $this->type = $type;
        $this->version = $version;
    }

    public function add($src, $type, $toFooter = false, $isDefer = false)
    {
        if ($type == static::CSS_TYPE) {
            $this->css[] = $src;
        } elseif ($type == static::JS_TYPE) {
            $this->js[] = array(
                'src' => $src,
                'to_footer' => $toFooter,
                'is_defer' => $isDefer
            );
        }

        return $this;
    }

    public function enqueue()
    {
        add_action("{$this->type}_enqueue_scripts", array($this, '_enqueue'));
        add_filter('script_loader_tag', function ($tag, $handle) {
            if (in_array($handle, $this->defer)) {
                return str_replace(' src', ' defer src', $tag);
            }

            return $tag;
        }, 10, 2);
    }

    public function _enqueue()
    {
        $cssDir = get_template_directory() . "/assets/css/";
        $jsDir = get_template_directory() . "/assets/js/";

        foreach ($this->css as $css) {
            $localCss = $cssDir . basename($css);
            $cssVer = file_exists($localCss) ? date("ymd-Gis", filemtime($localCss)) : $this->version;
            $handleName = DataHelper::removeChar(basename($css), '.');

            wp_register_style($handleName, $css, false, $cssVer);
            wp_enqueue_style($handleName);
        }

        foreach ($this->js as $js) {
            $localJs = $jsDir . basename($js['src']);
            $jsVer = file_exists($localJs) ? date("ymd-Gis", filemtime($localJs)) : $this->version;
            $handleName = DataHelper::removeChar(basename($js['src']), '.');

            wp_register_script($handleName, $js['src'], array(), $jsVer, $js['to_footer']);
            wp_enqueue_script($handleName);
            if ($js['is_defer']) {
                $this->defer[] = $handleName;
            }
        }
    }
}

$adminScriptManager = new BarnetScriptManager('admin');
$wpScriptManager = new BarnetScriptManager('wp');
