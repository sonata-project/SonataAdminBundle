<?php

namespace Sonata\AdminBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'sonata:admin:install-assets',
    description: 'Copy assets from SonataAdminBundle into assets/sonata_admin/ project directory.'
)]
class InstallSonataAssetsCommand extends Command
{
    public function __construct(private Filesystem $filesystem) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'target directory',
                './assets'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sourceDir = __DIR__ . '/../../assets';
        $targetArgument = $input->getArgument('target');

        try {
            if ($this->filesystem->exists($sourceDir)) {
                $this->filesystem->mirror($sourceDir, $targetArgument, null, ['override' => true]);
                $io->success('The SonataAdmin assets source files have been successfully copied to "./assets/sonata_admin" directory.');
            } else {
                $io->error('Source directory not found: ' . $sourceDir);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('An error occurred while copying files: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
