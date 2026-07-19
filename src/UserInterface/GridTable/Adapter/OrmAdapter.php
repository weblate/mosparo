<?php

namespace Mosparo\UserInterface\GridTable\Adapter;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Mosparo\UserInterface\GridTable\Table;

class OrmAdapter implements AdapterInterface
{
    protected EntityManagerInterface $entityManager;

    protected string $entityFqcn;

    protected ?Closure $queryCallback = null;

    public function __construct(EntityManagerInterface $entityManager, string $entityFqcn)
    {
        $this->entityManager = $entityManager;
        $this->entityFqcn = $entityFqcn;
    }

    public function setQueryCallback(Closure $queryCallback): self
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }

	public function query(Table $table)
	{
        $orderBy = $this->getOrderBy($table);
        $qb = $this->entityManager->createQueryBuilder()
            ->from($this->entityFqcn, 'e')
        ;

        if ($orderBy) {
            $qb->orderBy($orderBy);
        }

        if ($this->queryCallback !== null) {
            $this->queryCallback->call($this, $qb);
        }

        $countQb = (clone $qb)
            ->select('COUNT(e.id) AS c')
        ;
        $table->setTotalItems($countQb->getQuery()->getSingleScalarResult() ?? 0);
        $table->setTotalPages(ceil($table->getTotalItems() / $table->getPerPage()));

        $qb
            ->select('e')
            ->setFirstResult(($table->getPage() - 1) * $table->getPerPage())
            ->setMaxResults($table->getPerPage())
        ;

        $table->setItems($qb->getQuery()->getResult());
	}

    protected function getOrderBy(Table $table): ?OrderBy
    {
        $orderBy = null;
        if ($table->getSortByField() !== null) {
            $prefix = (str_contains($table->getSortByField(), '.')) ? '' : 'e.';
            $orderBy = new OrderBy($prefix . $table->getSortByField(), $table->getSortByDirection() ?: null);
        }

        return $orderBy;
    }
}
