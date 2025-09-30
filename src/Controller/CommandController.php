<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use App\Services\EtatService;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update:etat')]
final class CommandController extends AbstractController
{
    public function __construct(
        private readonly EtatService $etatService,
        private readonly SortieRepository $sortieRepository,
    ) {
    }

    public function __invoke(OutputInterface $output): int
    {
        try {
            $sorties = $this->sortieRepository->findAll();
            foreach ($sorties as $sortie) {
                $this->etatService->checkWorkflow($sortie);
            }
        } catch (Exception $e) {
            $output->writeln([
                '',
                '==================================',
                'ECHEC :',
                $e->getMessage(),
                '==================================',
                '',
            ]);

            return Command::FAILURE;
        }
        $output->writeln([
            '',
            '==================================',
            'Succès',
            '==================================',
            '',
        ]);

        return Command::SUCCESS;
    }
}
