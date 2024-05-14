<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use EllisLab\Addons\FilePicker\FilePicker;

Class Imgsizer_rte {

	public $info = array(
		'name' => 'Imgsizer RTE',
		'version' => '4.1.0',
		'description' => 'Add a sized image to the RTE',
		'cp_only' => 'y',
	);

	function globals()
	{
		$fp = new FilePicker();

		return array(
			'rte.imgsizer.label' => 'Imgsizer',
			'rte.imgsizer.title' => 'Imgsizer',
			'rte.imgsizer.url' =>  ee('CP/URL')->make($fp->controller, array('directory' => 'all'))->compile()
		);
	}

	function definition()
	{
		return file_get_contents( 'rte.imgsizer.js', TRUE );
	}

	function libraries()
	{
		$fp = new FilePicker();
		$fp->inject(ee()->view);
		return array();
	}

	function styles()
	{
		return file_get_contents( 'rte.imgsizer.css', TRUE );
	}

}
