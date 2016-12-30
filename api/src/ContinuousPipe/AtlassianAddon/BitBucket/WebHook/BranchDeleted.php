<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket\WebHook;

/**
 * Event sent when a branch is deleted.
 *
 * This is sent only when the user-interface has deleted the branch. When deleted via a Git push,
 * only the `repo:push` event is sent.
 */
class BranchDeleted extends BranchEvent
{
}
