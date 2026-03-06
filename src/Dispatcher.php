<?php

namespace YouriyPaluch\FileExplorer;

use Throwable;

class Dispatcher
{
	const TEMPLATE_TABLE = 'table';
	const TEMPLATE_TILE = 'tile';
	const TEMPLATE_LIST = [
		self::TEMPLATE_TABLE,
		self::TEMPLATE_TILE,
	];

	const SORT_ASC = 'asc';
	const SORT_DESC = 'desc';
	const SORT_BY_NAME = 'name';
	const SORT_BY_MODIFIED = 'modified';
	const SORT_BY_SIZE = 'size';
	const SORT_ORDER_DEFAULT = [
		self::SORT_ASC,
		self::SORT_DESC,
	];
	const SORT_BY_MAP = [
		self::SORT_BY_NAME => self::SORT_ORDER_DEFAULT,
		self::SORT_BY_MODIFIED => self::SORT_ORDER_DEFAULT,
		self::SORT_BY_SIZE => self::SORT_ORDER_DEFAULT,
	];

	private string $_baseDir;
	private int $_currentPage;
	private int $_countPerPage;
	private int $_countPage = 1;
	private array $_urlSegmentList;
	private DocumentEntity $_documentEntity;
	private array $_excludeFileList = ['.', '..'];
	private string $_baseUrl;
	private string $_fullPath;
	private string $_sort;
	private string $_order;
	private string $_template;
	private array $_additionalQueryParams = [];
	private array $_allowedExtensions;

	public function __construct(
		string $baseDir,
		string $baseUrl,
		array  $allowedExtensions = [],
		array  $excludeFileList = [],
		int    $countPerPage = 300
	)
	{
		$this->_baseUrl = $baseUrl;
		$this->_baseDir = $baseDir;
		$this->_allowedExtensions = $allowedExtensions;
		$this->_excludeFileList = array_merge($this->_excludeFileList, $excludeFileList);
		$this->_countPerPage = $countPerPage;

		$this->_currentPage = (int) $this->_getParam('page', 1);
		$this->_sort = $this->_getParam('sort', self::SORT_BY_NAME);
		$this->_order = $this->_getParam('order', self::SORT_ASC);
		$this->_template = $this->_getParam('template', self::TEMPLATE_TABLE);
		if (!in_array($this->_template,self::TEMPLATE_LIST)) {
			throw new WrongTemplateException('Invalid template type');
		}

		$fileName = $this->_getParam('file', '');
		$path = urldecode($this->_getParam('path', ''));
		if (str_contains($fileName, '..') || str_contains($path, '..')) {
			throw new Exception('Invalid file name');
		}

		if ($this->_template !== self::TEMPLATE_TABLE) {
			$this->_additionalQueryParams['template'] = $this->_template;
		}

		if ($this->_sort !== self::SORT_BY_NAME) {
			$this->_additionalQueryParams['sort'] = $this->_sort;
		}

		if ($this->_order !== self::SORT_ASC) {
			$this->_additionalQueryParams['order'] = $this->_order;
		}

		if ($search = $this->_getParam('search', '')) {
			$this->_additionalQueryParams['search'] = $search;
		}

		$this->_fullPath = $baseDir;
		if ($path) {
			$this->_fullPath .= DIRECTORY_SEPARATOR . $path;
		}
		if ($fileName) {
			$this->_fullPath .= DIRECTORY_SEPARATOR . $fileName;
		}
		$this->_urlSegmentList = array_filter(explode('/', $path), 'strlen');
		$this->_documentEntity = new DocumentEntity($this->_baseDir, $this->_baseUrl, $this->_fullPath, $this->_additionalQueryParams);
	}

	/**
	 * @throws Exception
	 */
	public function showContent(): string
	{
		try {
			if ($this->_documentEntity->isDir()) {
				return $this->_renderTemplate();
			}
			if ($this->_documentEntity->isFile()
				&& !in_array($this->_documentEntity->getExtension(), $this->_excludeFileList)
				&& (!$this->_allowedExtensions || in_array($this->_documentEntity->getExtension(), $this->_allowedExtensions))) {
				if($this->_getParam('download', 0)) {
					return $this->_documentEntity->download();
				}
				return $this->_documentEntity->getContent();
			} else {
				throw new Exception('Content of this file disabled to show');
			}
		} catch (Throwable $e) {
			throw new Exception('Something went wrong. Original error: ' . $e->getMessage());
		}
	}

	/**
	 * @return DocumentEntity[]
	 */
	protected function _getFileListForPage(): array
	{
		if ($this->_getParam('use-ls', 0) && empty($this->_additionalQueryParams['search'])) {
			$command = $this->_getCommandReserved();
		} else {
			$command = $this->_getCommand();
		}

		// Відкриваємо потік
		$handle = popen($command, 'r');

		$queryParams = $this->_additionalQueryParams;
		unset($queryParams['search']);

		$items = [];
		if ($handle) {
			$offset = ($this->_currentPage - 1) * $this->_countPerPage;
			$index = 0;
			$countFiles = 0;
			while (($file = fgets($handle)) !== false) {
				$file = trim($file); // Прибираємо перенесення рядка

				if ($index >= $offset) {
					$items[] = new DocumentEntity($this->_baseDir, $this->_baseUrl, $this->_fullPath . DIRECTORY_SEPARATOR . $file, $queryParams);
					$countFiles++;
				}

				$index++;

				if ($countFiles >= $this->_countPerPage) {
					break;
				}
			}
			if (fgets($handle)) {
				$this->_countPage = $this->_currentPage + 1;
			} else {
				$this->_countPage = $this->_currentPage;
			}
			pclose($handle);
		}
		return $items;
	}

	/**
	 * @throws WrongTemplateException
	 * @throws Exception
	 */
	protected function _renderTemplate(): string
	{
		$queryParams = $this->_additionalQueryParams;
		unset($queryParams['template']);
		if ($this->_urlSegmentList) {
			$path = ['path' => implode('/', $this->_urlSegmentList)];
		} else {
			$path = [];
		}

		if ($this->_currentPage > 1) {
			$currentPage = ['page' => $this->_currentPage];
		} else {
			$currentPage = [];
		}
		$queryParams += $currentPage + $path;
		$viewUrlList = [];
		foreach (self::TEMPLATE_LIST as $template) {
			if ($template !== self::TEMPLATE_TABLE) {
				$queryParams['template'] = $template;
			}

			$viewUrlList[] = [
				'name' => ucfirst($template),
				'isActive' => $this->_template !== $template,
				'url' => $this->_baseUrl . '?' . http_build_query($queryParams)
			];
		}

		$queryParams = $this->_additionalQueryParams;
		$queryParams += $currentPage + $path;
		unset($queryParams['sort'], $queryParams['order']);

		$sortList = [];
		foreach (self::SORT_BY_MAP as $sort => $orderList) {
			$sortName = ucfirst($sort);
			$sortList[$sortName] = [];
			foreach ($orderList as $order) {
				if ($sort !== self::SORT_BY_NAME) {
					$queryParams['sort'] = $sort;
				}
				if ($order !== self::SORT_ASC) {
					$queryParams['order'] = $order;
				}
				$sortList[$sortName][$order] = [
					'name' => ucfirst($order),
					'isActive' => $this->_sort . $this->_order !== $sort . $order,
					'url' => $this->_baseUrl . '?' . http_build_query($queryParams)
				];
				unset($queryParams['sort'], $queryParams['order']);
			}
		}

		$documentList = $this->_getFileListForPage();
		if ($this->_currentPage > 1 && !$documentList) {
			throw new Exception('No files found');
		}
		$breadcrumbs = $this->_getBreadcrumbs();

		$data = [
			'breadcrumbs' => $breadcrumbs,
			'documentEntityList' => $documentList,
			'baseUrl' => $this->_getHomeUrl(),
			'countPage' => $this->_countPage,
			'currentPage' => $this->_currentPage,
			'backUrl' => $this->_getBackUrl($breadcrumbs),
			'viewUrlList' => $viewUrlList,
			'sortList' => $sortList,
			'template' => $this->_template,
		];
		extract($data);
		ob_start();
		try {
			include 'base.php';
		} catch (Throwable $e) {
			ob_end_clean();
			throw new Exception('Error while rendering template. Original error: ' . $e->getMessage());
		}

		return ob_get_clean();
	}

	protected function _getHomeUrl(): string
	{
		$queryParams = $this->_additionalQueryParams;
		unset($queryParams['search']);
		return $this->_baseUrl . '?' . http_build_query($queryParams);
	}

	protected function _getBreadcrumbs(): array
	{
		$queryParams = $this->_additionalQueryParams;
		unset($queryParams['search']);
		$breadcrumbs = [];
		for ($i = 1; $i <= count($this->_urlSegmentList); $i++) {
			$j = $i - 1;
			if ($i === count($this->_urlSegmentList)) {
				$isActive = 0;
			} else {
				$isActive = 1;
			}
			$breadcrumbs[$j]['url'] = $this->_baseUrl . '?' . http_build_query(
					array_merge(
						$queryParams,
						['path' => implode('/', array_slice($this->_urlSegmentList, 0, $i))]
					)
				);
			$breadcrumbs[$j]['name'] = $this->_urlSegmentList[$j];
			$breadcrumbs[$j]['isActive'] = $isActive;
		}

		return $breadcrumbs;
	}

	protected function _getBackUrl(array $breadcrumbs): string
	{
		$queryParams = $this->_additionalQueryParams;
		unset($queryParams['search']);
		if (count($breadcrumbs) > 1) {
			$queryParams = array_merge(
				$queryParams,
				['path' => implode('/', array_slice($this->_urlSegmentList, 0, -1))]
			);
			return $this->_baseUrl . '?' . http_build_query($queryParams);
		}
		return $this->_baseUrl . '?' . http_build_query($queryParams);
	}

	protected function _getParam(string $paramName, $defaultValue = null): ?string
	{
		return $_GET[$paramName] ?? $defaultValue;
	}

	protected function _getCommandReserved(): string
	{
		// ls: вивести вмісту папки
		// -1: виводити файли по одному файлу на рядок
		// a: вивести всі файли, включаючи приховані
		// S: сортування за розміром
		// t: сортування по часу створення
		// r: реверс сортування
		// --group-directories-first: (реверсу нема) показати спочатку папки
		$command = 'ls ';
		$flags = '-1a';
		switch ($this->_sort) {
			case self::SORT_BY_MODIFIED:
				$flags .= 't';
				break;
			case self::SORT_BY_SIZE:
				$flags .= 'S';
				break;
			default:
		}

		// для правильного сортування по даті модифікації і розміру потрібно прапор r ставити при ASC
		if (($this->_order === self::SORT_DESC && $this->_sort === self::SORT_BY_NAME)
			|| ($this->_order === self::SORT_ASC && $this->_sort !== self::SORT_BY_NAME)
		) {
			$flags .= 'r';
		}

		// приклад команди
		// ls -1a --group-directories-first '/var/www/platinumlist/data/logs' | grep -vE '^(\.|\.\.|\.gitignore|\.keep|\.gitkeep)$|\\(\.txt|\.log)$'
		// де
		// ^(\.|\.\.|\.gitignore|\.keep|\.gitkeep)$' - перелік елементів, які треба виключити з пошуку, має включати повне ім'я включно з розширенням
		// \\(\.txt|\.log)$ - перелік розширень файлів які треба вивести, при цьому папки теж виводяться.
		$grepArs = '';
		if ($this->_excludeFileList) {
			$grepArs = ' | grep -vE \'^(';
			for ($i = 0; $i < count($this->_excludeFileList) - 1; $i++) {
				$grepArs .= str_replace('.', '\.', $this->_excludeFileList[$i]) . '|';
			}
			$grepArs .= str_replace('.', '\.', array_last($this->_excludeFileList)) . ')$';
		}

		if ($this->_allowedExtensions) {
			if (empty($grepArs)) {
				$grepArs = ' | grep -vE \\\\\'(';
			} else {
				$grepArs .= '|\\\\(';
			}
			for ($i = 0; $i < count($this->_allowedExtensions) - 1; $i++) {
				$grepArs .= '\.' . $this->_allowedExtensions[$i] . '|';
			}
			$grepArs .= '\.' . array_last($this->_allowedExtensions) . ')$\'';
		} else {
			$grepArs .= '\'';
		}

		$command .= $flags . ' --group-directories-first ' . escapeshellarg($this->_fullPath) . $grepArs;
		return $command;
	}

	protected function _getCommand(): string
	{
		// find: рекурсивний пошук від вказаного шляху
		// -mindepth і -maxdepth: глибина рекурсі пошуку, де
		// depth 0 — це сам /path (стартова точка)
		// depth 1 — елементи безпосередньо всередині /path (файли/папки)
		// depth 2 — елементи всередині підпапок і т.д.
		// -iname: шукає в іменах файлів і папок
		// -type d -o: виводити і файли і папки
		// -type f -regextype posix-extended -iregex: включення тільки файлів з розширеннями по регулярці
		// %T@: вивід дати модифікації елементи в секундах від Unix-епохи
		// %s: вивід розміру елемента в байтах
		// sort: сортує завжди по першій колонці, тому виводимо дві колонки '%T@ \t%f\n\', де
		// перша колонка - %T@
		// друга колонка - \t%f, тут табуляція(\t) для команди cut, для якої розділення на колонки іде по табуляції
		// приклад: 1764147075.5231152030   App
		// -n: числове сортування, де 10 > 9. Без нього сортує як рядок і тоді 9 > 10.
		// -r: зворотне сортування
		// cut -f2-: вивід тільки значення другої колонки
		// Беремо тільки ім'я
		$command = 'find ' . $this->_fullPath . ' -mindepth 1 -maxdepth 1';
		$search = $this->_additionalQueryParams['search'] ?? '';
		if ($search) {
			$command .= ' -iname \'*' . $search . '*\'';
		}

		if (!empty($this->_excludeFileList)) {
			$excludeParts = [];
			foreach ($this->_excludeFileList as $name) {
				$excludeParts[] = '-name ' . escapeshellarg($name);
			}
			$command .= ' ! \\( ' . implode(' -o ', $excludeParts) . ' \\) ';
		}

		if (!empty($this->_allowedExtensions)) {
			$extList = array_map(static function ($ext) {
				return preg_quote((string) $ext, '/');
			}, $this->_allowedExtensions);

			$regex = '.*\\.(' . implode('|', $extList) . ')$';
			$command .= ' \\( -type d -o \\( -type f -regextype posix-extended -iregex ' . escapeshellarg($regex) . ' \\) \\) ';
		} else {
			$command .= ' \\( -type d -o -type f \\) ';
		}

		$command .= '-printf \'';

		$order = $this->_order === self::SORT_DESC ? 'r' : '';
		switch ($this->_sort) {
			case self::SORT_BY_MODIFIED:
				$command .= '%T@ \t%f\n\' | sort -n' . $order . ' | cut -f2-';
				break;
			case self::SORT_BY_SIZE:
				$command .= '%s \t%f\n\' | sort -n' . $order . ' | cut -f2-';
				break;
			default:
				$command .= '%f\n\' | sort ' . ($order ? '-r' : '');
				break;
		}

		return $command;
	}
}
