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

namespace Sonata\AdminBundle\Action;

use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

final class GetShortObjectDescriptionAction
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, AdminFetcherInterface $adminFetcher)
    {
        $this->twig = $twig;
        $this->adminFetcher = $adminFetcher;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not find admin for code "%s".',
                $request->get('_sonata_admin')
            ));
        }

        $objectId = $request->get('objectId');
        $object = $admin->getObject($objectId);
        if (null === $object) {
            throw new NotFoundHttpException(sprintf('Could not find subject for id "%s"', $objectId));
        }

        if ('json' === $request->get('_format')) {
            return new JsonResponse(['result' => [
                'id' => $admin->id($object),
                'label' => $admin->toString($object),
            ]]);
        }

        if ('html' === $request->get('_format')) {
            $templateRegistry = $admin->getTemplateRegistry();

            return new Response($this->twig->render($templateRegistry->getTemplate('short_object_description'), [
                'admin' => $admin,
                'description' => $admin->toString($object),
                'object' => $object,
                'link_parameters' => $request->get('linkParameters', []),
            ]));
        }

        throw new \RuntimeException('Invalid format');
    }
}
