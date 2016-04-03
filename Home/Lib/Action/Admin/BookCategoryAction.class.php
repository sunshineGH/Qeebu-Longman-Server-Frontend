<?php


class BookCategoryAction extends CommonAction {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$rs = pageing('bookshop_category',7,$_GET['search'],'id desc','1','name');
		$this->assign('data',$rs['data']);
		$this->display('index');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->display('create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(empty($_POST['name'])){
			$this->error('请填写频道名称!');
		}

		$arr = array(
			'name'      	=> $_POST['name'],
			'category_sort' => $_POST['category_sort'],
			);
		$category = M('bookshop_category');
		$res = $category->add($arr);
		if(!$res) {
			$this->error('添加失败');
		}


		redirect(U('Admin/BookCategory/index'));

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		$this->display('show');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit()
	{
		$id = $_GET['id'];
		$category = M('bookshop_category');
		$data = $category->find($id);

		$this->assign('data',$data);

		$this->display('edit');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
		if(empty($_POST['name'])){
			$this->error('请填写频道名称!');
		}

		$arr = array(
			'name'      	=> $_POST['name'],
			'category_sort' => $_POST['category_sort'],
			);
		$category = M('bookshop_category');
		$category->where('id='.$_POST['id'])->save($arr);


		redirect(U('Admin/BookCategory/index'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$id = $_GET['id'];
		//判断该频道下如果没有资料才允许删除
		$bookshop = M('bookshop');
		$res = $bookshop->where('bookshop_category_id='.$id)->count();
		if($res) {
			$this->error('该分类下有书籍,不允许删除');
		}
		$category = M('bookshop_category');
		$category->where('id='.$id)->delete();

		redirect(U('Admin/BookCategory/index'));
	}

}