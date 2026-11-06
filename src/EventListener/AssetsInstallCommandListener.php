<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkApplication;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This listener extends `assets:install` command when SonataCoreBundle will be not register. Files from `Resources/private/SonataCoreBundleAssets`
 * will be copy with the same result like SonataCoreBundle is register.
 *
 * This class should be remove when support for Bootstrap 3 will be ended or assets system will be remove in favor for encore webpack.
 */
final class AssetsInstallCommandListener
{
    public const METHOD_COPY = 'copy';
    public const METHOD_ABSOLUTE_SYMLINK = 'absolute symlink';
    public const METHOD_RELATIVE_SYMLINK = 'relative symlink';

    private $filesystem;
    private $projectDir;

    public function __construct(Filesystem $filesystem, ?string $projectDir = null)
    {
        if (null === $projectDir) {
            @trigger_error(sprintf(
                'Not passing the project directory to the constructor of %s is deprecated since Symfony 4.3'
                .' and will not be supported in 5.0.',
                __CLASS__
            ), \E_USER_DEPRECATED);
        }

        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
    }

    public function copySonataCoreBundleAssets(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        $application = $command->getApplication();
        \assert($application instanceof FrameworkApplication);

        try {
            $coreBundle = $application->getKernel()->getBundle('SonataCoreBundle');
        } catch (\Exception $e) {
            $coreBundle = null;
        }

        if ('assets:install' !== $command->getName() || null !== $coreBundle) {
            return;
        }

        $this->execute($event->getInput(), $event->getOutput(), $application);
    }

    private function execute(InputInterface $input, OutputInterface $output, FrameworkApplication $application): int
    {
        /**
         * @var KernelInterface
         */
        $kernel = $application->getKernel();

        $targetArg = rtrim($input->getArgument('target') ?? '', '/');

        if (!$targetArg) {
            $targetArg = $this->getPublicDirectory($kernel->getContainer());
        }

        if (!is_dir($targetArg)) {
            $targetArg = $kernel->getProjectDir().'/'.$targetArg;

            if (!is_dir($targetArg)) {
                throw new InvalidArgumentException(sprintf(
                    'The target directory "%s" does not exist.',
                    $input->getArgument('target')
                ));
            }
        }

        $bundlesDir = $targetArg.'/bundles/';

        $io = new SymfonyStyle($input, $output);
        $io->newLine();

        if ($input->getOption('relative')) {
            $expectedMethod = self::METHOD_RELATIVE_SYMLINK;
            $io->text('Trying to install deprecated SonataCoreBundle assets from SonataAdminBundle as <info>relative symbolic links</info>.');
        } elseif ($input->getOption('symlink')) {
            $expectedMethod = self::METHOD_ABSOLUTE_SYMLINK;
            $io->text('Trying to install deprecated SonataCoreBundle assets from SonataAdminBundle as <info>absolute symbolic links</info>.');
        } else {
            $expectedMethod = self::METHOD_COPY;
            $io->text('Installing deprecated SonataCoreBundle assets from SonataAdminBundle as <info>hard copies</info>.');
        }

        $io->newLine();

        $copyUsed = false;
        $exitCode = 0;
        $validAssetDirs = [];

        $bundle = $kernel->getBundle('SonataAdminBundle');
        $originDir = $bundle->getPath().'/Resources/private/SonataCoreBundleAssets';

        $assetDir = preg_replace('/bundle$/', '', 'sonatacore');
        $targetDir = $bundlesDir.$assetDir;
        $validAssetDirs[] = $assetDir;

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $message = sprintf("%s\n-> %s", $bundle->getName(), $targetDir);
        } else {
            $message = $bundle->getName();
        }

        try {
            $this->filesystem->remove($targetDir);

            if (self::METHOD_RELATIVE_SYMLINK === $expectedMethod) {
                $method = $this->relativeSymlinkWithFallback($originDir, $targetDir);
            } elseif (self::METHOD_ABSOLUTE_SYMLINK === $expectedMethod) {
                $method = $this->absoluteSymlinkWithFallback($originDir, $targetDir);
            } else {
                $method = $this->hardCopy($originDir, $targetDir);
            }

            if (self::METHOD_COPY === $method) {
                $copyUsed = true;
            }

            if ($method === $expectedMethod) {
                $ioMethod = 'success';
            } else {
                $ioMethod = 'warning';
            }
        } catch (\Exception $e) {
            $exitCode = 1;
            $ioMethod = 'error';
        }

        if (0 !== $exitCode) {
            $io->error('Some errors occurred while installing assets.');
        } else {
            if ($copyUsed) {
                $io->note('Some assets were installed via copy. If you make changes to these assets you have to run this command again.');
            }

            switch ($ioMethod) {
                case 'success':
                case 'warning':
                    $io->$ioMethod('All deprecated SonataCoreBundle assets from SonataAdminBundle were successfully installed.');
                    break;
                case 'error':
                default:
                    $io->$ioMethod('No deprecated SonataCoreBundle assets from SonataAdminBundle were provided by any bundle.');
                    break;
            }
        }

        return $exitCode;
    }

    /**
     * Try to create relative symlink.
     *
     * Falling back to absolute symlink and finally hard copy.
     */
    private function relativeSymlinkWithFallback(string $originDir, string $targetDir): string
    {
        try {
            $this->symlink($originDir, $targetDir, true);
            $method = self::METHOD_RELATIVE_SYMLINK;
        } catch (IOException $e) {
            $method = $this->absoluteSymlinkWithFallback($originDir, $targetDir);
        }

        return $method;
    }

    /**
     * Try to create absolute symlink.
     *
     * Falling back to hard copy.
     */
    private function absoluteSymlinkWithFallback(string $originDir, string $targetDir): string
    {
        try {
            $this->symlink($originDir, $targetDir);
            $method = self::METHOD_ABSOLUTE_SYMLINK;
        } catch (IOException $e) {
            // fall back to copy
            $method = $this->hardCopy($originDir, $targetDir);
        }

        return $method;
    }

    /**
     * Creates symbolic link.
     *
     * @throws IOException if link can not be created
     */
    private function symlink(string $originDir, string $targetDir, bool $relative = false)
    {
        if ($relative) {
            $this->filesystem->mkdir(\dirname($targetDir));
            $originDir = $this->filesystem->makePathRelative($originDir, realpath(\dirname($targetDir)));
        }
        $this->filesystem->symlink($originDir, $targetDir);
        if (!file_exists($targetDir)) {
            throw new IOException(sprintf(
                'Symbolic link "%s" was created but appears to be broken.',
                $targetDir
            ), 0, null, $targetDir);
        }
    }

    /**
     * Copies origin to target.
     */
    private function hardCopy(string $originDir, string $targetDir): string
    {
        $this->filesystem->mkdir($targetDir, 0777);
        // We use a custom iterator to ignore VCS files
        $this->filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));

        return self::METHOD_COPY;
    }

    private function getPublicDirectory(ContainerInterface $container): string
    {
        $defaultPublicDir = 'public';

        if (null === $this->projectDir && !$container->hasParameter('kernel.project_dir')) {
            return $defaultPublicDir;
        }

        $composerFilePath = ($this->projectDir ?? $container->getParameter('kernel.project_dir')).'/composer.json';

        if (!file_exists($composerFilePath)) {
            return $defaultPublicDir;
        }

        $composerConfig = json_decode(file_get_contents($composerFilePath), true);

        if (isset($composerConfig['extra']['public-dir'])) {
            return $composerConfig['extra']['public-dir'];
        }

        return $defaultPublicDir;
    }
}
