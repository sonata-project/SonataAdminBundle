<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;

class DumpActionRolesCommand extends Command
{
    public function configure()
    {
        $this->setName('sonata:admin:dump-action-roles');
        $this->setDescription('Dumps a set of access control rules for the classes');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'define the output format', 'yaml');
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'define the admin route prefix', '/admin');
        $this->setHelp(<<<EOF
Dumps a role hierachy and a set of access control rules using a different role
for each admin actions.
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $infos = array();
        foreach ($this->getAdminRoutesCollection($input->getOption('prefix'))->all() as $route) {
            $compiledRoute = $route->compile();

            $regex = str_replace(array("\n", ' '), '', $compiledRoute->getRegex());
            if ($pos = strpos($regex, '/$')) {
                $regex = substr($regex, 0, $pos).'/?$'.substr($regex, $pos + 2);
            }

            $defaults = $route->getDefaults();

            $controllerInfos = explode(':', $defaults['_controller']);

            $group = strtoupper(sprintf('ROLE_%s', str_replace(array('.','|'), '_', strtoupper($defaults['_sonata_admin']))));
            if (!isset($infos[$group])) {
                $infos[$group] = array();
            }

            $name = strtoupper(sprintf('ROLE_%s_%s',
                str_replace(array('.','|'), '_', strtoupper($defaults['_sonata_admin'])),
                $controllerInfos[2]
            ));

            $infos[$group][] = array(
                'path' => substr($regex, 1, -2),
                'roles' => $name
            );
        }

        $this->dumpYaml($output, $infos);
    }

    public function dumpYaml(OutputInterface $output, array $infos)
    {

        $output->writeln('security:');
        $output->writeln('    access_control:');
        foreach ($infos as $groups) {
            foreach ($groups as $group) {
                $output->writeln(sprintf('        - { path: %s, roles: [%s], methods: null }', $group['path'], $group['roles']));
            }
        }

        $output->writeln('');
        $output->writeln('    role_hierarchy:');

        $superAdmin = array();
        foreach ($infos as $groupName => $groups) {
            $roles = array();
            foreach ($groups as $group) {
                $roles[] = $group['roles'];
            }
            $output->writeln(sprintf('        %s: [%s] ', $groupName, implode(', ', $roles)));

            $superAdmin[] = $groupName;
        }

        $output->writeln(sprintf('        ROLE_SONATA_ADMIN_ROOT: [%s] ', implode(', ', $superAdmin)));
    }

    public function getAdminRoutesCollection($prefix)
    {
        $pool = $this->container->get('sonata.admin.pool');
        $collection = new RouteCollection;

        foreach ($pool->getAdminServiceIds() as $id) {

            $admin = $pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        $collection->addPrefix($prefix);
        return $collection;
    }
}
