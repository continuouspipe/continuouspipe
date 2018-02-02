<?php

namespace AuthenticatorBundle\Controller;

use ContinuousPipe\AuditLog\Storage\LogRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/audit-log", service="app.controller.audit_log")
 */
class AuditLogController
{
    /**
     * @var LogRepository|null
     */
    private $logRepository;

    public function __construct(LogRepository $logRepository = null)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @Route("/view", name="view_audit_log")
     * @Template
     */
    public function viewAction(Request $request)
    {
        if (null === $this->logRepository) {
            throw new \RuntimeException('Audit log is not activated');
        }

        $eventTypes = $this->logRepository->listEventTypes();
        $eventType = $request->get('event_type', reset($eventTypes));
        $pageCursor = $request->get('cursor', '');
        $pageSize = $request->get('limit', 10);
        $result = $this->logRepository->query($eventType, $pageCursor, $pageSize);

        return [
            'records' => $result->records(),
            'nextPageCursor' => $result->nextPageCursor(),
            'eventTypes' => $eventTypes,
            'eventType' => $eventType,
            'pageSize' => $pageSize,
        ];
    }
}
