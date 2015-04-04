<?php namespace Jiro\Extension\Product\Controllers\Admin;

use Platform\Access\Controllers\AdminController;
use Jiro\Product\Database\ProductRepositoryInterface;

class ProductController extends AdminController 
{
	/**
	 * The Products repository.
	 *
	 * @var \Jiro\Product\Database\ProductRepositoryInterface
	 */
	protected $products;

	/**
	 * Holds all the mass actions we can execute.
	 *
	 * @var array
	 */
	protected $actions = [
		'delete',
		'enable',
		'disable',
	];

	/**
	 * Constructor.
	 *
	 * @param  \Jiro\Product\Database\ProductRepositoryInterface  $products
	 * @return void
	 */
	public function __construct(ProductRepositoryInterface $products)
	{
		parent::__construct();

		$this->products = $products;
	}

	/**
	 * Display a listing of products.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('jiro/product::index');
	}

	/**
	 * Datasource for the products Data Grid.
	 *
	 * @return \Cartalyst\DataGrid\DataGrid
	 */
	public function grid()
	{
		$columns = [
			'id',
			'name',
			'slug',
			'available_on',
			'created_at',
		];

		$settings = [
			'sort'      => 'created_at',
			'direction' => 'desc',
			'pdf_view'  => 'pdf',
		];

		$transformer = function($element)
		{
			$element->edit_uri = route('admin.jiro.product.edit', $element->id);

			return $element;
		};

		return datagrid($this->products->grid(), $columns, $settings, $transformer);
	}

	/**
	 * Show the form for creating a new product.
	 *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function create()
	{
		return $this->showForm('create');
	}

	/**
	 * Handle posting of the form for creating a new product.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{ 
		return $this->processForm('create');
	}

	/**
	 * Show the form for updating a page.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function edit($id)
	{
		return $this->showForm('update', $id);
	}

	/**
	 * Handle posting of the form for updating a page.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id)
	{
		return $this->processForm('update', $id);
	}

	/**
	 * Show the form for copying a product.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function copy($id)
	{
		return $this->showForm('copy', $id);
	}

	/**
	 * Remove the specified product.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function delete($id)
	{
		$type = $this->products->delete($id) ? 'success' : 'error';

		$this->alerts->{$type}(
			trans("jiro/product::message.{$type}.delete")
		);

		return redirect()->route('admin.product.all');
	}

	/**
	 * Executes the mass action.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function executeAction()
	{
		$action = request()->input('action');

		if (in_array($action, $this->actions))
		{
			foreach (request()->input('rows', []) as $row)
			{
				$this->products->{$action}($row);
			}

			return response('Success');
		}

		return response('Failed', 500);
	}

	/**
	 * Shows the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	protected function showForm($mode, $id = null)
	{
		if ( ! $data = $this->products->getPreparedProduct($id))
		{
			$this->alerts->error(trans('jiro/product::message.not_found', compact('id')));

			return redirect()->toAdmin('products');
		}

		$product = $data['product'];

		return view('jiro/product::form', compact(
			'product', 'mode'
		));
	}

	/**
	 * Processes the form.
	 *
	 * @param  string  $mode
	 * @param  int  $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function processForm($mode, $id = null)
	{
		// Store the product
		list($messages) = $this->products->store($id, request()->all());

		// Do we have any errors?
		if ($messages->isEmpty())
		{
			$this->alerts->success(trans("jiro/product::message.success.{$mode}"));

			return redirect()->route('admin.jiro.product.all');
		}

		$this->alerts->error($messages, 'form');

		return redirect()->back()->withInput();
	}

}
