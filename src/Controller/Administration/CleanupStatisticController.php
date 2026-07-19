<?php

namespace Mosparo\Controller\Administration;

use Doctrine\ORM\EntityManagerInterface;
use Mosparo\Entity\CleanupStatistic;
use Mosparo\UserInterface\GridTable\Adapter\OrmAdapter;
use Mosparo\UserInterface\GridTable\Column;
use Mosparo\UserInterface\GridTable\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/administration/cleanup-statistic')]
class CleanupStatisticController extends AbstractController
{
    #[Route('/', name: 'administration_cleanup_statistic')]
    public function index(EntityManagerInterface $entityManager, Factory $factory): Response
    {
        $adapter = (new OrmAdapter($entityManager, CleanupStatistic::class));

        $table = $factory->create($adapter)
            ->addColumn(new Column(
                'dateTime',
                'administration.cleanupStatistic.list.dateTime',
                template: 'administration/cleanup_statistic/list/_dateTime.html.twig',
            ))
            ->addColumn(new Column(
                'cleanupExecutor',
                'administration.cleanupStatistic.list.cleanupExecutor',
                template: 'administration/cleanup_statistic/list/_cleanupExecutor.html.twig',
            ))
            ->addColumn(new Column(
                'numberOfStoredSubmitTokens',
                'administration.cleanupStatistic.list.submitTokens',
                template: 'administration/cleanup_statistic/list/_submitTokens.html.twig',
            ))
            ->addColumn(new Column(
                'numberOfStoredSubmissions',
                'administration.cleanupStatistic.list.submissions',
                template: 'administration/cleanup_statistic/list/_submissions.html.twig',
            ))
            ->addColumn(new Column(
                'executionTime',
                'administration.cleanupStatistic.list.executionTime',
                template: 'administration/cleanup_statistic/list/_executionTime.html.twig',
            ))
            ->addColumn(new Column(
                'cleanupStatus',
                'administration.cleanupStatistic.list.cleanupStatus',
                template: 'administration/cleanup_statistic/list/_cleanupStatus.html.twig',
            ))
            ->setSortBy('dateTime', 'DESC')
        ;

        $table->query();

        return $this->render('administration/cleanup_statistic/list.html.twig', [
            'table' => $table
        ]);
    }
}
