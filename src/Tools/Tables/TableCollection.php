<?php

namespace Katu\Tools\Tables;

use App\Classes\Time;
use Katu\Files\UploadCollection;

class TableCollection extends \ArrayObject
{
	public static function createFromUploads(UploadCollection $uploads): TableCollection
	{
		$res = new static;

		foreach ($uploads as $upload) {
			switch ($upload->fileType) {
				case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
					$file = \Katu\Files\File::createTemporaryWithExtension("xlsx");
					$file->set($upload->getStream()->getContents());

					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
					$spreadsheet = $reader->load((string)$file);

					foreach ($spreadsheet->getAllSheets() as $worksheet) {
						$table = (new Table($worksheet->getTitle()))
							->setFilename($upload->fileName)
							;

						foreach ($worksheet->getRowIterator() as $row) {
							foreach ($worksheet->getColumnIterator() as $column) {
								$cell = $worksheet->getCell($column->getColumnIndex() . $row->getRowIndex());
								if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
									$timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell->getValue());
									$dateTime = new Time("@{$timestamp}", new \DateTimeZone("Europe/Prague"));
									$table[$row->getRowIndex()][$column->getColumnIndex()] = $dateTime->getDbDateTimeFormat();
								} else {
									$table[$row->getRowIndex()][$column->getColumnIndex()] = $cell->getCalculatedValue();
								}
							}
						}

						$res[] = $table;
					}

					break;
				case "text/csv":
					try {
						$title = preg_replace("/_/", " ", pathinfo($upload->fileName)["filename"]);
					} catch (\Throwable $e) {
						$title = $upload->fileName;
					}

					$table = (new Table($title))
						->setFilename($upload->fileName)
						;

					$file = \Katu\Files\File::createTemporaryWithExtension("csv");
					$file->set($upload->getStream()->getContents());

					$csv = \League\Csv\Reader::createFromPath((string)$file);
					$csv->setDelimiter(",");

					// Check column counts.
					$counts = array_map("count", iterator_to_array($csv->getRecords()));
					$averageCount = array_sum($counts) / count($counts);
					if ($averageCount == 1 || !is_int($averageCount)) {
						$csv->setDelimiter(";");
					}

					foreach ($csv->getRecords() as $record) {
						$table[] = $record;
					}

					$res[] = $table;

					break;
			}
		}

		return $res;
	}
}
