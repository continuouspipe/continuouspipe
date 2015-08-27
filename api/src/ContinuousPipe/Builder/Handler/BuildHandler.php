<?php

namespace ContinuousPipe\Builder\Handler;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Builder;
use ContinuousPipe\Builder\BuildException;
use ContinuousPipe\Builder\BuildRepository;
use ContinuousPipe\Builder\Command\BuildCommand;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Logging\BuildLoggerFactory;
use ContinuousPipe\Builder\Notifier;
use GuzzleHttp\Exception\RequestException;
use LogStream\Logger;
use LogStream\Node\Text;

class BuildHandler
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @var BuildLoggerFactory
     */
    private $loggerFactory;

    /**
     * @param Builder            $builder
     * @param Notifier           $notifier
     * @param BuildRepository    $buildRepository
     * @param BuildLoggerFactory $loggerFactory
     */
    public function __construct(Builder $builder, Notifier $notifier, BuildRepository $buildRepository, BuildLoggerFactory $loggerFactory)
    {
        $this->builder = $builder;
        $this->notifier = $notifier;
        $this->buildRepository = $buildRepository;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param BuildCommand $command
     */
    public function handle(BuildCommand $command)
    {
        $build = $command->getBuild();

        $logger = $this->loggerFactory->forBuild($build);
        $logger->start();

        $build->updateStatus(Build::STATUS_RUNNING);
        $build = $this->buildRepository->save($build);

        try {
            $this->builder->build($build, $logger);

            $build->updateStatus(Build::STATUS_SUCCESS);
        } catch (DockerException $e) {
            $message = $e->getMessage() ? ': '.$e->getMessage() : '';
            $logger->append(new Text('An Docker error occurred'.$message));
            $build->updateStatus(Build::STATUS_ERROR);
        } catch (BuildException $e) {
            $logger->append(new Text($e->getMessage()));
            $build->updateStatus(Build::STATUS_ERROR);
        } catch (\Exception $e) {
            $this->appendException($logger, $e);
            $build->updateStatus(Build::STATUS_ERROR);
        } finally {
            $build = $this->buildRepository->save($build);
        }

        $notification = $build->getRequest()->getNotification();
        if (null !== $notification) {
            $this->notifier->notify($notification, $build);
        }
    }

    private function appendException(Logger $logger, \Exception $e)
    {
        $message = $e->getMessage();
        if ($e instanceof RequestException) {
            if ($response = $e->getResponse()) {
                $message .= '{'.$response->getStatusCode().'} ';

                if ($body = $response->getBody()) {
                    if ($body->isSeekable()) {
                        $body->seek(0);
                    }

                    $message .= ' ['.$body->getContents().']';
                }
            }
        }

        $logger->append(new Text('PANIC ('.get_class($e).' - '.$e->getCode().') '.$message));

        if (null !== $e->getPrevious()) {
            $this->appendException($logger, $e->getPrevious());
        }
    }
}
