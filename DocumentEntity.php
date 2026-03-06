<?php

namespace YouriyPaluch\FileExplorer;
class DocumentEntity
{
	const TYPE_DIR = 'dir';
	const TYPE_FILE = 'file';

	private string $_name;
	private string $_type;
	private string $_url;
	private string $_fullPath;

	public function __construct(string $baseDir, string $baseUrl, string $fullPath, array $additionalQueryParams)
	{
		$this->_fullPath = $fullPath;
		$path = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $fullPath);
		$this->_name = pathinfo($path, PATHINFO_BASENAME);

		$queryData = $additionalQueryParams;
		if ($path) {
			if (pathinfo($this->_name, PATHINFO_EXTENSION)) {
				$queryData['path'] = dirname($path);
			} else {
				$queryData['path'] = $path;
			}
		}
		if (is_dir($this->_fullPath)) {
			$this->_type = self::TYPE_DIR;
		} else {
			$this->_type = self::TYPE_FILE;
			$queryData['file'] = $this->_name;
		}

		$this->_url = $baseUrl . '?' . http_build_query($queryData);
	}

	public function getName(): string
	{
		return $this->_name;
	}

	public function getType(): string
	{
		return $this->_type;
	}

	public function getUrl(): string
	{
		return $this->_url;
	}

	public function getDownloadUrl(): string
	{
		if ($this->isFile()) {
			return $this->_url . '&' . http_build_query(['download' => 1]);
		} else {
			throw new DocumentEntityException('File cannot be download.');
		}
	}

	public function getSize(): string
	{
		if ($this->isDir()) {
			return '-';
		}

		$bytes = filesize($this->_fullPath);
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= (1 << (10 * $pow));

		return round($bytes, 2) . ' ' . $units[$pow];
	}

	public function getCreatedAt(string $format = 'd.m.Y H:i'): string
	{
		return date($format, filectime($this->_fullPath));
	}

	public function getModifiedAt(string $format = 'd.m.Y H:i'): string
	{
		return date($format, filemtime($this->_fullPath));
	}

	/**
	 * @throws DocumentEntityException
	 */
	public function getContent(): int
	{
		header('Content-Disposition: inline; filename="' . basename($this->_fullPath) . '"');
		return $this->_getContent();
	}

	public function isDir(): bool
	{
		return $this->_type === self::TYPE_DIR;
	}

	public function isFile(): bool
	{
		return $this->_type === self::TYPE_FILE;
	}

	public function getExtension(): string
	{
		return pathinfo($this->_fullPath, PATHINFO_EXTENSION);
	}

	public function download(): string
	{
		header('Content-Disposition: attachment; filename="' . basename($this->_fullPath) . '"');
		return $this->_getContent();
	}

	private function _getContent(): string
	{
		if (!$this->isFile() || !is_readable($this->_fullPath)) {
			throw new DocumentEntityException('File not found or access denied.');
		}

		$fileSize = filesize($this->_fullPath);
		header('Content-Type: text/plain; charset=utf-8');

		if ($fileSize !== false) {
			header('Content-Length: ' . $fileSize);
		}

		if (ob_get_level()) {
			ob_end_clean();
		}
		return readfile($this->_fullPath);
	}
}
