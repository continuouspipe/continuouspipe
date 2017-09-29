<?php

namespace AppBundle\Controller;

use ContinuousPipe\AuditLog\Storage\LogRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/audit-log", service="app.controller.audit_log")
 */
class AuditLogController
{
    private $pageSizes = [
        10,
        20,
        50,
    ];

    /**
     * @var LogRepository
     */
    private $logRepository;

    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @Route("/view", name="view_audit_log")
     * @Template
     *
     * @return array
     */
    public function viewAction(Request $request)
    {
        $eventTypes = $this->logRepository->listEventTypes();
        $eventType = $request->get('event_type', reset($eventTypes));
        $pageCursor = $request->get('page', '');
        $pageSize = $request->get('limit', reset($this->pageSizes));
        $result = $this->logRepository->query($eventType, $pageCursor, $pageSize);

        return [
            'records' => $result->records(),
            'nextPageCursor' => $result->nextPageCursor(),
            'eventTypes' => $eventTypes,
            'eventType' => $eventType,
            'pageSizes' => $this->pageSizes,
            'pageSize' => $pageSize,
            'hasNextPage' => !empty($result->nextPageCursor()),
        ];
    }
}
