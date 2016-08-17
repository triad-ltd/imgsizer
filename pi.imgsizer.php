<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array( // EE2 support
    'pi_author' => 'Roger Hughes, David Rencher, Christian Maloney, Stephen Sweetland',
    'pi_author_url' => 'http://clicked.me.uk/',
    'pi_description' => 'Image resizer - resize images and create placeholders',
    'pi_name' => 'ImageSizer',
    'pi_usage' => Imgsizer::usage(),
    'pi_version' => '4.0.2',
);

class Imgsizer {

    function __construct() { $this->init(); }

    function Imgsizer() { $this->init(); } // EE2 support

    function calculateSize() {
        // get dimensions and mime type
        $sizePath = reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']);
        if (!$size = @getimagesize($sizePath)) {
            return false;
        }
        $this->input['width'] = $size[0];
        $this->input['height'] = $size[1];
        $this->input['mimetype'] = $size[2];

        // dimension calculation
        if ($this->settings['width'] && !$this->settings['height']) {
            $ratio = $this->input['height'] / $this->input['width'];
            $this->output['height'] = round($this->settings['width'] * $ratio);
            $this->output['width'] = $this->settings['width'];
        }

        if ($this->settings['height'] && !$this->settings['width']) {
            $ratio = $this->input['width'] / $this->input['height'];
            $this->output['height'] = $this->settings['height'];
            $this->output['width'] = round($this->settings['height'] * $ratio);
        }

        if ($this->settings['height'] && $this->settings['width']) {
            $this->output['height'] = $this->settings['height'];
            $this->output['width'] = $this->settings['width'];
        }
        return true;
    }

    function error($err) {
        $this->EE->TMPL->log_item("ImageSizer Error: " . $err);
        return $this->EE->TMPL->no_results();
    }

    function init() {
        $this->EE = get_instance();

        $this->input = array();
        $this->output = array();
        $this->settings = array();


        // set document root folder
        if (array_key_exists('DOCUMENT_ROOT', $_ENV)) {
            $this->settings['root_path'] = reduce_double_slashes($_ENV['DOCUMENT_ROOT'] . "/");
        } else {
            $this->settings['root_path'] = reduce_double_slashes($_SERVER['DOCUMENT_ROOT'] . "/");
        }

        // set cache path
        // tag value first (if present), then config value (if present), then default
        $this->settings['cache_path'] = $this->settings['root_path'] . '/images/imgsizer';
        if ($this->EE->config->item('imgsizer_cache_path') != false) {
            $this->settings['cache_path'] = $this->EE->config->item('imgsizer_cache_path');
        }
        if ($this->EE->TMPL->fetch_param('cache_path') != '') {
            $this->settings['cache_path'] = $this->EE->TMPL->fetch_param('cache_path');
        }

        // set cache url
        $this->settings['cache_url'] = '/images/imgsizer';
        if ($this->EE->config->item('imgsizer_cache_url') != false) {
            $this->settings['cache_url'] = $this->EE->config->item('imgsizer_cache_url');
        }
        if ($this->EE->TMPL->fetch_param('cache_url') != '') {
            $this->settings['cache_url'] = $this->EE->TMPL->fetch_param('cache_url');
        }


        // fetch vars from the tag
        $this->settings['color'] = (!$this->EE->TMPL->fetch_param('color')) ? '000000' : $this->EE->TMPL->fetch_param('color');
        $this->settings['color'] = str_replace('#', '', $this->settings['color']);

        $this->settings['height'] = (!$this->EE->TMPL->fetch_param('height')) ? false : $this->EE->TMPL->fetch_param('height');

        $this->settings['quality'] = (!$this->EE->TMPL->fetch_param('quality')) ? 65 : $this->EE->TMPL->fetch_param('quality');

        $this->settings['src'] = (!$this->EE->TMPL->fetch_param('src')) ? false : rawurldecode($this->EE->TMPL->fetch_param('src'));

        $this->settings['size_src'] = (!$this->EE->TMPL->fetch_param('size_src')) ? $this->EE->TMPL->fetch_param('src') : $this->EE->TMPL->fetch_param('size_src');

        $this->settings['width'] = (!$this->EE->TMPL->fetch_param('width')) ? false : $this->EE->TMPL->fetch_param('width');

        if($this->settings['size_src'] != '') {
            if (!file_exists(reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']))) {
                return $this->error("Can't read size source file.");
            }
        }
    }

    function output() {
        $tagdata = $this->EE->TMPL->tagdata;

        if ($tagdata) {
            foreach($this->EE->TMPL->var_single as $key => $val) {
                switch ($val) {
                    case "url":
                    case "sized":
                        $tagdata = $this->EE->TMPL->swap_var_single($val, $this->output['url'], $tagdata);
                        break;
                    case "width":
                        $tagdata = $this->EE->TMPL->swap_var_single($val, $this->output['width'], $tagdata);
                        break;
                    case "height":
                        $tagdata = $this->EE->TMPL->swap_var_single($val, $this->output['height'], $tagdata);
                        break;
                }
            }
            return $tagdata;
        } else {
            return '<img src="' . $this->output['url'] . '" width="'.$this->output['width'].'" height="'.$this->output['height'].'" ' . $this->passthrough() . '/>';
        }
    }

    function passthrough() {
        $this->output['passthrough'] = '';

        foreach(array('alt','class','id','style','title') as $var) {
            if ($this->EE->TMPL->fetch_param($var)) {
                $this->output['passthrough'].= ' '.$var.'="' . $this->EE->TMP->fetch_param($var) . '"';
            }
        }

        $this->output['passthrough'] .= (!$this->EE->TMPL->fetch_param('passthrough')) ? '' : ' '.$this->EE->TMPL->fetch_param('passthrough');
    }

    function placeholder() {
        // validation
        if ($this->settings['size_src']) {
            if (!$this->settings['width'] && !$this->settings['height']) {
                return $this->error("Placeholder with 'size_source' must have either 'width' or 'height'");
            }
        } else {
            if ($this->settings['width'] == false || $this->settings['height'] == false) {
                return $this->error("Placeholder requires 'width' and 'height'");
            }
        }
        if (!file_exists(reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']))) {
            return $this->error("Can't read size source");
        }

        // set output filename and dimensions
        // file format [width]-[height]-[color].png
        if (!$this->settings['size_src']) {
            $this->output['height'] = $this->settings['height'];
            $this->output['width'] = $this->settings['width'];
        } else {
            if (!$this->calculateSize()) {
                return $this->error("Unable to calculate image size");
            }
        }
        $this->output['filename'] = "{$this->output['width']}-{$this->output['height']}-{$this->settings['color']}.png";

        // set output path and url, create placeholder if it doesn't exist
        $this->output['url'] = reduce_double_slashes($this->settings['cache_url'] . '/placeholders/' . $this->output['filename']);
        $this->output['path'] = reduce_double_slashes($this->settings['cache_path'] . '/placeholders/');
        if (!is_dir($this->output['path'])) {
            if (!mkdir($this->output['path'], 0777, true)) {
                return $this->error("Unable to create placeholder cache folder");
            }
        }

        // create placeholder if it doesn't exist already
        if (!file_exists($this->output['path'] . $this->output['filename'])) {
            $ph = imagecreate($this->output['width'], $this->output['height']);
            imagecolorallocate(
                $ph,
                hexdec(substr($this->settings['color'],0,2)),
                hexdec(substr($this->settings['color'],2,2)),
                hexdec(substr($this->settings['color'],4,2))
                );
            imagepng($ph, $this->output['path'] . $this->output['filename']);
            imagedestroy($ph);
        }

        return $this->output();
    }

    function size() {
        // validation
        if ($this->settings['src'] == false) {
            return $this->error("Parameter 'src' is required");
        }
        if ($this->settings['width'] == false && $this->settings['height'] == false) {
            return $this->error("At least one of 'width' or 'height' must be specified");
        }
        if (!file_exists(reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']))) {
            return $this->error("Can't read size source");
        }

        $inputPath = reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['src']);

        // build filename and output path
        // file format [width]-[height]-[quality]-[filename]
        $inf = pathinfo($this->settings['src']);
        $this->output['path'] = reduce_double_slashes($this->settings['cache_path'] . '/' . $inf['dirname'] . '/');
        $this->output['filename'] = $this->settings['width'].'-'.$this->settings['height'].'-'.$this->settings['quality'].'-'.$inf['basename'];
        $this->output['url'] = reduce_double_slashes($this->settings['cache_url'] . '/' . $inf['dirname'] . '/' . $this->output['filename']);
        if (!is_dir($this->output['path'])) {
            if (!mkdir($this->output['path'], 0777, true)) {
                return $this->error("Unable to create cache folder");
            }
        }

        if (!$this->calculateSize()) {
            return $this->error("Unable to calculate image size");
        }

        // check cache
        if (!file_exists($this->output['path'] . $this->output['filename']) ||
            filemtime($this->output['path'] . $this->output['filename']) < filemtime($inputPath) ) {

            // perform resize/crop
            switch ($this->input['mimetype']) {
                case IMAGETYPE_GIF:
                    $inImage = imagecreatefromgif($inputPath);
                    break;
                case IMAGETYPE_JPEG:
                    $inImage = imagecreatefromjpeg($inputPath);
                    break;
                case IMAGETYPE_PNG:
                    $inImage = imagecreatefrompng($inputPath);
                    break;
                default:
                    return $this->error("Unhandled mime type");
            }

            $outImage = imagecreatetruecolor($this->output['width'], $this->output['height']);
            imagealphablending($outImage, false);
            imagesavealpha($outImage, true);
            imagecopyresampled($outImage, $inImage, 0, 0, 0, 0, $this->output['width'], $this->output['height'], $this->input['width'], $this->input['height']);

            // save to disk
            switch ($this->input['mimetype']) {
                case IMAGETYPE_GIF:
                    imagegif($outImage, $this->output['path'] . $this->output['filename']);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($outImage, $this->output['path'] . $this->output['filename'], $this->settings['quality']);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($outImage, $this->output['path'] . $this->output['filename']);
                    break;
            }

        }

        return $this->output();
    }

    static function usage() { return file_get_contents(__DIR__.'/README.md'); }

}
