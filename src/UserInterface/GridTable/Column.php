<?php

namespace Mosparo\UserInterface\GridTable;

class Column
{
    protected string $field;

    protected string $label;

    protected bool $sortable;

    protected bool $mapped;

    protected ?string $template;

    protected ?string $cellClass;

    protected bool $isNumeric;

    protected int $decimals;

    protected ?string $headerGroup;

    public function __construct(string $field, string $label, bool $sortable = true, bool $mapped = true, ?string $template = null, ?string $cellClass = null, bool $isNumeric = false, int $decimals = 0, ?string $headerGroup = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->sortable = $sortable;
        $this->mapped = $mapped;
        $this->template = $template;
        $this->cellClass = $cellClass;
        $this->isNumeric = $isNumeric;
        $this->decimals = $decimals;
        $this->headerGroup = $headerGroup;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isMapped(): bool
    {
        return $this->mapped;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getCellClass(): ?string
    {
        return $this->cellClass;
    }

    public function isNumeric(): bool
    {
        return $this->isNumeric;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getHeaderGroup(): ?string
    {
        return $this->headerGroup;
    }
}
