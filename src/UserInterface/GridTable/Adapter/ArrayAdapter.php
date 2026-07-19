<?php

namespace Mosparo\UserInterface\GridTable\Adapter;

use Mosparo\UserInterface\GridTable\Table;

class ArrayAdapter implements AdapterInterface
{
    protected mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

	public function query(Table $table)
	{

        $table->setTotalItems(count($this->data));
        $table->setTotalPages(ceil($table->getTotalItems() / $table->getPerPage()));

        $table->setItems($this->data);
	}
}
