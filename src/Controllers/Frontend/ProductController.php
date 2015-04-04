<?php namespace Jiro\Extension\Product\Controllers\Frontend;

use Platform\Foundation\Controllers\Controller;

class ProductController extends Controller {

	/**
	 * Return the main view.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('jiro/product::index');
	}

}
