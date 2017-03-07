<?php

namespace ContinuousPipe\Authenticator\Invitation;

use ContinuousPipe\Authenticator\WhiteList\WhiteList;

class InvitationWhiteList implements WhiteList
{
    /**
     * @var WhiteList
     */
    private $decoratedWhiteList;

    /**
     * @var InvitationToggleFactory
     */
    private $invitationToggleFactory;


    public function __construct(WhiteList $decoratedWhiteList, InvitationToggleFactory $invitationToggleFactory)
    {
        $this->decoratedWhiteList = $decoratedWhiteList;
        $this->invitationToggleFactory = $invitationToggleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($username)
    {
        $invitationToggle = $this->invitationToggleFactory->createFromSession();
        if ($invitationToggle->isActive()) {
            return true;
        }

        return $this->decoratedWhiteList->contains($username);
    }

    /**
     * {@inheritdoc}
     */
    public function add($username)
    {
        $this->decoratedWhiteList->add($username);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($username)
    {
        $this->decoratedWhiteList->remove($username);
    }
}
