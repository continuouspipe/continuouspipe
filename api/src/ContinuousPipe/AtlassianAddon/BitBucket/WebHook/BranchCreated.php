<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

/**
 * Event sent when a branch is created.
 *
 * This is sent only when the user-interface has created the branch. When created via a Git push,
 * only the `repo:push` event is sent.
 *
 */
class BranchCreated extends BranchEvent
{
}
