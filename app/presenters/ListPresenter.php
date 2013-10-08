<?php
use App\DirectoryFacade;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ListPresenter extends Presenter
{

	/**
	 * @var string
	 * @persistent
	 */
	public $path = '/';

	/**
	 * @var string
	 * @persistent
	 */
	public $sort = 'date';

	/**
	 * @var string
	 * @persistent
	 */
	public $sortType = 'desc';

	/**
	 * @var DirectoryFacade
	 */
	private $directoryFacade;

	/**
	 * @var string
	 */
	private $root;

	/**
	 * @var string
	 */
	private $webRoot;

	/**
	 * @var bool
	 */
	private $ignoreHiddenFiles;


	/**
	 * @param DirectoryFacade $directoryFacade
	 */
	public function injectDirectoryFacade(DirectoryFacade $directoryFacade)
	{
		$this->directoryFacade = $directoryFacade;
	}


	public function startup()
	{
		parent::startup();
		$this->root = $this->context->parameters['root'];
		$this->webRoot = $this->context->parameters['webRoot'];
		$this->ignoreHiddenFiles = $this->context->parameters['ignoreHiddenFiles'];
	}


	public function actionDefault()
	{
		if (!DirectoryFacade::isPathInJail($this->root, $this->path)) {
			$this->redirect('this', array('path' => '/'));
		}
	}


	public function renderDefault()
	{
		$fullPath = realpath($this->root . DIRECTORY_SEPARATOR . $this->path);
		$this->template->items = $this->directoryFacade->findAll(
			$fullPath,
			$this->sort,
			$this->sortType,
			$this->ignoreHiddenFiles
		);
		$this->template->inRoot = realpath($this->root) === $fullPath ? TRUE : FALSE;
		$this->template->path = $this->path;
	}


	/**
	 * @param string $item
	 */
	public function handleDelete($item)
	{
		$object = realpath($this->root . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR . $item);
		$isDir = is_dir($object);
		DirectoryFacade::remove($object);

		if ($isDir) {
			$this->flashMessage('Directory ' . $item . ' successfully deleted', 'success');
		} else {
			$this->flashMessage('File ' . $item . ' successfully deleted', 'success');
		}
		$this->redirect('default');
	}


	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;

		$form->addUpload('file');
		$form->addSubmit('upload', 'Upload');

		$form->onSuccess[] = callback($this, 'formSubmitted');
		return $form;
	}


	/**
	 * @param \Nette\Application\UI\Form
	 */
	public function formSubmitted(Form $form)
	{
		$values = $form->getValues();
		/** @var \Nette\Http\FileUpload $upload */
		$upload = $values->file;

		if ($upload->getError() === UPLOAD_ERR_NO_FILE) {
			$this->flashMessage('No file selected', 'danger');
			$this->redirect('this');
			return;
		}

		$path = realpath($this->root . DIRECTORY_SEPARATOR . $this->path) . DIRECTORY_SEPARATOR;
		$file = $path . $upload->getName();

		$suffix = 2;
		while (is_file($file)) {
			$parts = explode('.', $file);
			$extension = $parts[count($parts) - 1];
			$file = substr($file, 0, strlen($file) - strlen($extension) - 1) . '_' . $suffix . '.' . $extension;
			$suffix++;
		}

		$upload->move($file);
		$this->flashMessage('File ' . substr($file, strlen($path)) . ' successfully uploaded', 'success');
		$this->redirect('this');
	}


	/************************ helpers ************************/


	/**
	 * @param string $sort
	 * @return string
	 */
	public function getSortType($sort)
	{
		if ($sort === $this->sort) {
			return $this->sortType === 'asc' ? 'desc' : 'asc';
		} else {
			return 'asc';
		}
	}


	/**
	 * @param \SplFileInfo $item
	 * @return string
	 */
	public function getItemPath(\SplFileInfo $item)
	{
		return rtrim($this->path, '/') . '/' . $item->getFileName();
	}


	/**
	 * @param \SplFileInfo $item
	 * @return string
	 */
	public function getFilePath(\SplFileInfo $item)
	{
		return rtrim(rtrim($this->webRoot, '/') . $this->path, '/') . '/' . $item->getFileName();
	}


	/**
	 * @return string
	 */
	public function getBackPath()
	{
		$fullPath = realpath($this->root . DIRECTORY_SEPARATOR . $this->path . '/../');
		$root = realpath($this->root);

		if ($root === $fullPath) {
			$diff = '/';
		} else {
			$diff = substr($fullPath, strlen($root));
		}

		return str_replace('\\', '/', $diff);
	}


	/**
	 * @param \SplFileInfo $item
	 * @return string
	 */
	public function getDeleteConfirmMessage(\SplFileInfo $item)
	{
		if ($item->isDir()) {
			return 'Do you really want delete folder ' . $item->getFilename();
		} else {
			return 'Do you really want delete file ' . $item->getFilename();
		}
	}
}
