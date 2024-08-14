<?php

namespace Katu\Tools\Images;

class QRCode
{
	protected $string;
	protected $size;
	protected $margin;

	public function __construct(string $string, ?int $size = 400, ?int $margin = 0)
	{
		$this->setString($string);
		$this->setSize($size);
		$this->setMargin($margin);
	}

	public function __toString(): string
	{
		return "data:image/png;base64,{$this->getBase64PNG()}";
	}

	public function setString(string $string): QRCode
	{
		$this->string = $string;

		return $this;
	}

	public function getString(): string
	{
		return $this->string;
	}

	public function setSize(?int $size): QRCode
	{
		$this->size = $size;

		return $this;
	}

	public function getSize(): ?int
	{
		return $this->size;
	}

	public function getResolvedSize(): int
	{
		return $this->getSize() ?: 400;
	}

	public function setMargin(?int $margin): QRCode
	{
		$this->margin = $margin;

		return $this;
	}

	public function getMargin(): ?int
	{
		return $this->margin;
	}

	public function getResolvedMargin(): int
	{
		return !is_null($this->getMargin()) ? $this->getMargin() : 10;
	}

	public function getImageString(\Endroid\QrCode\Writer\WriterInterface $writer): string
	{
		return \Endroid\QrCode\Builder\Builder::create()
			->writer($writer)
			->data($this->getString())
			->encoding(new \Endroid\QrCode\Encoding\Encoding("UTF-8"))
			->size($this->getResolvedSize())
			->margin($this->getResolvedMargin())
			->roundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin)
			->build()
			->getString()
			;
	}

	public function getPNG(): string
	{
		return $this->getImageString(new \Endroid\QrCode\Writer\PngWriter);
	}

	public function getBase64PNG(): string
	{
		return base64_encode($this->getPNG());
	}
}
