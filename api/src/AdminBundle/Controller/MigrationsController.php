<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\Flow\Event\FlowRecovered;
use ContinuousPipe\River\Flow\Migrations\ToEventSourced\Migrator;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(service="admin.controller.migrations")
 */
class MigrationsController
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param Session $session
     * @param Migrator $migrator
     * @param UrlGeneratorInterface $urlGenerator
     * @param MessageBus $eventBus
     */
    public function __construct(
        Session $session,
        Migrator $migrator,
        UrlGeneratorInterface $urlGenerator,
        MessageBus $eventBus
    ) {
        $this->session = $session;
        $this->migrator = $migrator;
        $this->urlGenerator = $urlGenerator;
        $this->eventBus = $eventBus;
    }

    /**
     * @Route("/migrations", name="admin_migrations")
     * @Template
     */
    public function listAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/migrations/{migration}/migrate", name="admin_migrate", methods={"POST"})
     */
    public function migrateAction(Request $request, string $migration)
    {
        if ($migration == 'to-event-sourced') {
            $count = $this->migrator->migrate();

            $this->session->getFlashBag()->add('success', 'Migration successful. Migrated ' . $count . ' flows!');
        } elseif ($migration == 'recover-flow') {
            $this->eventBus->handle(new FlowRecovered(
                Uuid::fromString($request->request->get('_uuid'))
            ));
        } else {
            $this->session->getFlashBag()->add('danger', 'Unknown migration "'.$migration.'"');
        }

        return new RedirectResponse($this->urlGenerator->generate('admin_migrations'));
    }
}
