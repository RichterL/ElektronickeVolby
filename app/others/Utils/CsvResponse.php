<?php
declare(strict_types=1);

namespace Nette\Application\Responses;

use Nette;

/** Source code taken from https://blog.nette.org/cs/http-pozadavky-a-odpovedi-cast-2
 * 	and altered to accept CSV string as well
 */
final class CsvResponse implements Nette\Application\Response
{
	private $fileName;
	private $content;
	private $rows;
	private $delimiter;

	public function __construct(string $fileName, string $content, iterable $rows = [], string $delimiter = ',')
	{
		$this->fileName = $fileName;
		$this->content = $content;
		$this->rows = $rows;
		$this->delimiter = $delimiter;
	}

	public function send(Nette\Http\IRequest $request, Nette\Http\IResponse $response): void
	{
		$response->setContentType('text/csv', 'utf-8');
		$response->setHeader('Content-Description', 'File Transfer');

		$tmp = str_replace('"', "'", $this->fileName);
		$response->setHeader(
			'Content-Disposition',
			"attachment; filename=\"$tmp\"; filename*=utf-8''" . rawurlencode($this->fileName)
		);

		$fd = fopen('php://output', 'wb');
		if (!empty($this->content)) {
			fputs($fd, $this->content);
		} else {
			$bom = true;
			foreach ($this->rows as $row) {
				if ($bom) {
					// Aby MS Excel správně zobrazil diakritiku. Ale jen pokud existují nějaké řádky.
					fputs($fd, "\xEF\xBB\xBF");
					$bom = false;
				}

				$row = $row instanceof \Traversable ? iterator_to_array($row) : (array) $row;
				fputcsv($fd, $row, $this->delimiter);
			}
		}
		fclose($fd);
	}
}
