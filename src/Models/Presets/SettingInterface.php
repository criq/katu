<?php

namespace Katu\Models\Presets;

interface SettingInterface
{
	public function getId(): ?string;
	public function getName(): string;
	public function setName(string $name): SettingInterface;
	public function getValue();
	public function setValue($value): SettingInterface;
	public function getDescription(): ?string;
	public function setDescription(?string $description): SettingInterface;
	public function getIsSystem(): bool;
	public function setIsSystem(bool $isSystem): SettingInterface;
}
