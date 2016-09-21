<?php

class Imgsizer_ext {

	var $name = 'Imgsizer';
	var $version = '4.1.0';
	var $settings_exists = 'n';

	function channel_entries_tagdata_end($tagdata, $row)
	{
		if(strpos($tagdata, 'imgsizer-figure') !== false) {
			// parse tag so we have full image paths to work with
			$tagdata = ee()->TMPL->parse_variables($tagdata, []);

			// get the tags out of the tagdata
			preg_match_all("/<figure(.*)imgsizer-figure(.*)figure>/", $tagdata, $tags);

			foreach($tags[0] as $tag) {
				$figure_tag = $tag;
				$out_figure = str_replace('imgsizer-figure','', $figure_tag);
				preg_match("/<img(.*)>/U", $figure_tag, $result);
				$img_tag = $result[0];

				// get any alt text from image tag
				$alt_text = preg_match("/alt=\"(.*)\"/U", $img_tag, $result) ? $result[1] : '';

				// get the src and width, remove that data from the html output
				preg_match("/data-src=\"(.*)\"/U", $figure_tag, $result);
				$src = $result[1];
				$out_figure = str_replace($result[0], '', $out_figure);
				preg_match("/data-width=\"(.*)\"/U", $figure_tag, $result);
				$width = $result[1];
				$out_figure = str_replace($result[0], '', $out_figure);

				if ($width == '') {
					// no resizing, show regular image
					$out_img = '<img src="' . $src . '" alt="' . $alt_text . '" />';
				} else {
					// replace the new tags into the tagdata
					$out_img = '{exp:imgsizer:size src="' . $src . '" width="' . $width . '"}
					<img src="{sized}" width="{width}" height="{height}" alt="' . $alt_text . '" />
					{/exp:imgsizer:size}';
				}
				$out_figure = str_replace($img_tag, $out_img, $out_figure);
				$tagdata = str_replace($figure_tag, $out_figure, $tagdata);
			}
		}
		return $tagdata;
	}

}
