<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\Flow\Event\FlowRecovered;
use ContinuousPipe\River\Flow\Migrations\ToEventSourced\Migrator;
use ContinuousPipe\River\Migrations\GetEventStoreToSQLStore\FlowEventsMigrator;
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
    private $toEventsMigrator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var FlowEventsMigrator
     */
    private $flowEventsMigrator;

    /**
     * @param Session $session
     * @param Migrator $toEventsMigrator
     * @param UrlGeneratorInterface $urlGenerator
     * @param MessageBus $eventBus
     */
    public function __construct(
        Session $session,
        Migrator $toEventsMigrator,
        UrlGeneratorInterface $urlGenerator,
        MessageBus $eventBus,
        FlowEventsMigrator $flowEventsMigrator
    ) {
        $this->session = $session;
        $this->toEventsMigrator = $toEventsMigrator;
        $this->urlGenerator = $urlGenerator;
        $this->eventBus = $eventBus;
        $this->flowEventsMigrator = $flowEventsMigrator;
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
            $count = $this->toEventsMigrator->migrate();

            $this->session->getFlashBag()->add('success', 'Migration successful. Migrated ' . $count . ' flows!');
        } elseif ($migration == 'geteventstore-to-sql') {
            $count = $this->flowEventsMigrator->migrate();

            $this->session->getFlashBag()->add('success', 'Migration successful. Migrated ' . $count . ' events!');
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
