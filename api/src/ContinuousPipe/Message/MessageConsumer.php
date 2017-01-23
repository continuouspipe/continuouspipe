<?php

namespace ContinuousPipe\Message;

interface MessageConsumer
{
    public function consume(Message $message);
}
