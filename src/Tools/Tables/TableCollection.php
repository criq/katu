<?php

namespace Katu\Tools\Tables;

use Katu\Files\FileCollection;
use Katu\Files\UploadCollection;
use Katu\Tools\Calendar\Time;

class TableCollection extends \ArrayObject
{
	public static function createFromUploads(UploadCollection $uploads): TableCollection
	{
		$res = new static;

		// Process each upload individually to track which tables come from which upload
		foreach ($uploads as $upload) {
			$extension = $upload->getExtension() ?: (
				$upload->fileType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ? "xlsx" : "csv"
			);

			// Create temporary file from upload
			$file = \Katu\Files\File::createTemporaryWithExtension($extension);
			$file->set($upload->getStream()->getContents());

			// Use createFromFiles to process this single file
			$fileTables = static::createFromFiles([$file]);

			// Update filenames to match original upload filename
			foreach ($fileTables as $table) {
				$table->setFilename($upload->fileName);
				$res[] = $table;
			}
		}

		return $res;
	}

	public static function createFromFiles($files): TableCollection
	{
		$res = new static;

		// Convert to FileCollection if needed
		if (is_array($files)) {
			$files = new FileCollection($files);
		}

		foreach ($files as $file) {
			$mimeType = $file->getMime();
			$fileName = $file->getBasename();
			$extension = mb_strtolower($file->getExtension() ?: "");

			// Determine file type: check MIME type first, then fall back to extension
			$isXlsx = ($mimeType === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || $extension === "xlsx");
			$isCsv = (in_array($mimeType, ["text/csv", "text/plain"], true) || $extension === "csv");

			if ($isXlsx) {
				$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile((string)$file);
				$spreadsheet = $reader->load((string)$file);

				foreach ($spreadsheet->getAllSheets() as $worksheet) {
					$table = (new Table($worksheet->getTitle()))
						->setFilename($fileName)
						;

					foreach ($worksheet->getRowIterator() as $row) {
						foreach ($worksheet->getColumnIterator() as $column) {
							$cell = $worksheet->getCell("{$column->getColumnIndex()}{$row->getRowIndex()}");
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
			} elseif ($isCsv) {
				try {
					$title = preg_replace("/_/", " ", pathinfo($fileName)["filename"]);
				} catch (\Throwable $e) {
					$title = $fileName;
				}

				$table = (new Table($title))
					->setFilename($fileName)
					;

				$csv = \League\Csv\Reader::createFromPath((string)$file);
				$csv->setDelimiter(",");

				// Check column counts.
				$counts = array_map("count", iterator_to_array($csv->getRecords()));
				$averageCount = count($counts) ? array_sum($counts) / count($counts) : 0;
				if ($averageCount == 1 || !is_int($averageCount)) {
					$csv->setDelimiter(";");
				}

				$records = iterator_to_array($csv->getRecords());
				foreach ($records as $index => $record) {
					$table[$index + 1] = $record;
				}

				$res[] = $table;
			}
		}

		return $res;
	}

	public function getFirst(): ?Table
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
