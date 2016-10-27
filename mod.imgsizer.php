<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Imgsizer {

    function __construct() { $this->init(); }

    function calculateSize() {
        // get dimensions and mime type
        $sizePath = reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']);

        try {
            $size = getimagesize($sizePath);
        } catch (Exception $exception) {
			ee()->TMPL->log_item("ImgSizer Error: Unable to read image size ($sizePath)");
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
        ee()->TMPL->log_item("ImgSizer Error: " . $err);
        return ee()->TMPL->no_results();
    }

    function init() {
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
        if (ee()->config->item('imgsizer_cache_path') != false) {
            $this->settings['cache_path'] = ee()->config->item('imgsizer_cache_path');
        }
        if (ee()->TMPL->fetch_param('cache_path') != '') {
            $this->settings['cache_path'] = ee()->TMPL->fetch_param('cache_path');
        }

        // set cache url
        $this->settings['cache_url'] = '/images/imgsizer';
        if (ee()->config->item('imgsizer_cache_url') != false) {
            $this->settings['cache_url'] = ee()->config->item('imgsizer_cache_url');
        }
        if (ee()->TMPL->fetch_param('cache_url') != '') {
            $this->settings['cache_url'] = ee()->TMPL->fetch_param('cache_url');
        }

        // fetch vars from the tag
        $this->settings['color'] = (!ee()->TMPL->fetch_param('color')) ? '000000' : ee()->TMPL->fetch_param('color');
        $this->settings['color'] = str_replace('#', '', $this->settings['color']);

        $this->settings['height'] = (!ee()->TMPL->fetch_param('height')) ? false : ee()->TMPL->fetch_param('height');

        $this->settings['quality'] = (!ee()->TMPL->fetch_param('quality')) ? 65 : ee()->TMPL->fetch_param('quality');

        $this->settings['src'] = (!ee()->TMPL->fetch_param('src')) ? false : rawurldecode(ee()->TMPL->fetch_param('src'));

        $this->settings['size_src'] = (!ee()->TMPL->fetch_param('size_src')) ? ee()->TMPL->fetch_param('src') : ee()->TMPL->fetch_param('size_src');

        $this->settings['width'] = (!ee()->TMPL->fetch_param('width')) ? false : ee()->TMPL->fetch_param('width');

        if($this->settings['size_src'] != '') {
            if (!file_exists(reduce_double_slashes($this->settings['root_path'] . '/' . $this->settings['size_src']))) {
                return $this->error("Can't read size source file. " . $this->settings['size_src']);
            }
        }
    }

    function output() {
        $tagdata = ee()->TMPL->tagdata;

        if ($tagdata) {
            foreach(ee()->TMPL->var_single as $key => $val) {
                switch ($val) {
                    case "url":
                    case "sized":
                        $tagdata = ee()->TMPL->swap_var_single($val, $this->output['url'], $tagdata);
                        break;
                    case "width":
                        $tagdata = ee()->TMPL->swap_var_single($val, $this->output['width'], $tagdata);
                        break;
                    case "height":
                        $tagdata = ee()->TMPL->swap_var_single($val, $this->output['height'], $tagdata);
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
            if (ee()->TMPL->fetch_param($var)) {
                $this->output['passthrough'].= ' '.$var.'="' . ee()->TMPL->fetch_param($var) . '"';
            }
        }

        $this->output['passthrough'] .= (!ee()->TMPL->fetch_param('passthrough')) ? '' : ' '.ee()->TMPL->fetch_param('passthrough');
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
                return $this->error("Unable to create placeholder cache folder " . $this->settings['cache_path'] . '/placeholders/');
            }
        }

        // create placeholder if it doesn't exist already
        $ph_path = $this->output['path'] . $this->output['filename'];
        if (!file_exists($ph_path)) {
        	if (!touch($ph_path)) {
				return $this->error("Unable to create placeholder file " . $ph_path);
			}
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
                return $this->error("Unable to create cache folder " . $this->output['path']);
            }
        }

        if (!$this->calculateSize()) {
            return $this->error("Unable to calculate image size");
        }

        // check cache
        $img_path = $this->output['path'] . $this->output['filename'];
        if (!file_exists($img_path) || filemtime($img_path) < filemtime($inputPath) ) {
        	if (!touch($img_path)) {
				return $this->error("Unable to create output file " . $img_path);
			}
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
                    return $this->error("Unhandled mime type " . $this->input['mimetype']);
            }

            $outImage = imagecreatetruecolor($this->output['width'], $this->output['height']);
            imagealphablending($outImage, false);
            imagesavealpha($outImage, true);
            imagecopyresampled($outImage, $inImage, 0, 0, 0, 0, $this->output['width'], $this->output['height'], $this->input['width'], $this->input['height']);

            // save to disk
            switch ($this->input['mimetype']) {
                case IMAGETYPE_GIF:
                    imagegif($outImage, $img_path);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($outImage, $img_path, $this->settings['quality']);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($outImage, $img_path);
                    break;
            }

            // set modified time to that of the source file
            touch($img_path, filemtime($inputPath));

        }

        return $this->output();
    }

    static function usage() { return file_get_contents(__DIR__.'/README.md'); }

}
