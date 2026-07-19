<?php

namespace Mosparo\UserInterface\GridTable\Adapter;

use Mosparo\UserInterface\GridTable\Table;

interface AdapterInterface
{
    public function query(Table $table);
}
