<?php

namespace Mosparo\UserInterface\GridTable;

use Doctrine\ORM\EntityManagerInterface;
use Mosparo\Helper\InterfaceHelper;
use Mosparo\UserInterface\GridTable\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Factory
{
    protected EntityManagerInterface $entityManager;

    protected RequestStack $requestStack;

    protected InterfaceHelper $interfaceHelper;

    public function __construct(RequestStack $requestStack, InterfaceHelper $interfaceHelper)
    {
        $this->requestStack = $requestStack;
        $this->interfaceHelper = $interfaceHelper;
    }

    public function create(AdapterInterface $adapter): Table
    {
        $request = $this->requestStack->getMainRequest();
        return (new Table($request, $adapter))
            ->setPageSizes([10, 20, 50, 100, 250, 500])
            ->setPerPage($this->interfaceHelper->determineNumberOfItemsPerPage($request))
        ;
    }
}
