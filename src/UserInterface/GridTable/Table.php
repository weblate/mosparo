<?php

namespace Mosparo\UserInterface\GridTable;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Mosparo\Exception;
use Mosparo\UserInterface\GridTable\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;

class Table
{
    protected Request $request;

    protected AdapterInterface $adapter;

    protected array $columns = [];

    protected mixed $items = null;

    protected array $pageSizes = [];

    protected int $page = 1;

    protected int $perPage;

    protected int $totalPages = 1;

    protected ?int $totalItems;

    protected ?string $sortByField;

    protected ?string $sortByDirection;
    public function __construct(Request $request, AdapterInterface $adapter)
    {
        $this->request = $request;
        $this->adapter = $adapter;
    }

    public function addColumn(Column $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setPageSizes(array $pageSizes): self
    {
        $this->pageSizes = $pageSizes;

        return $this;
    }

    public function getPageSizes(): array
    {
        return $this->pageSizes;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setTotalPages(int $totalPages): self
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function setSortBy(?string $field, ?string $direction = null): self
    {
        $this->sortByField = $field;
        $this->sortByDirection = $direction;

        return $this;
    }

    public function getSortByField(): ?string
    {
        return $this->sortByField;
    }

    public function getSortByDirection(): ?string
    {
        return $this->sortByDirection;
    }

    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function query(): void
    {
        if (empty($this->columns)) {
            throw new Exception('No columns found.');
        }

        $this->findPage();
        $this->findOrderBy();

        $this->adapter->query($this);
    }

    protected function findPage(): void
    {
        $this->page = $this->request->query->get('page', 1);
        $this->perPage = $this->request->query->get('perPage', $this->perPage);
    }

    protected function findOrderBy(): void
    {
        if ($this->request->query->get('sortBy', '') !== '') {
            $sortBy = $this->request->query->get('sortBy');
            foreach ($this->columns as $column) {
                if ($column->getField() === $sortBy) {
                    $this->sortByField = $sortBy;
                    break;
                }
            }

            $sortDir = $this->request->query->get('sortDir');
            if (in_array(strtoupper($sortDir), ['ASC', 'DESC'])) {
                $this->sortByDirection = $sortDir;
            }
        }
    }

    public function setTotalItems(int $totalItems): self
    {
        $this->totalItems = $totalItems;

        return $this;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function setItems(mixed $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getItems(): mixed
    {
        return $this->items;
    }

    public function hasHeaderGroups(): bool
    {
        foreach ($this->columns as $column) {
            if ($column->getHeaderGroup() !== null) {
                return true;
            }
        }

        return false;
    }
}
