<?php

namespace AuthenticatorBundle\Command;

use ContinuousPipe\Billing\BillingProfile\UserBillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Billing\Plan\Recurly\RecurlyPlanManager;
use ContinuousPipe\Security\Team\TeamRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshBillingProfilesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('refresh-billing-profiles');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $billingProfileRepository = $this->getBillingProfileRepository();
        $recurlyPlanManager = $this->getRecurlyPlanManager();
        $logger = $this->getLogger();

        foreach ($this->getTeamRepository()->findAll() as $team) {
            try {
                $billingProfile = $billingProfileRepository->findByTeam($team);
            } catch (UserBillingProfileNotFound $e) {
                $output->writeln(sprintf('[WARN] Team "%s" has no billing profile', $team->getSlug()));

                continue;
            }

            try {
                $recurlyPlanManager->refreshBillingProfile($billingProfile);
                $output->writeln(sprintf('[OK] Refreshed billing profile "%s" (team "%s")', $billingProfile->getUuid()->toString(), $team->getSlug()));
            } catch (\Exception $e) {
                $output->writeln(sprintf('[ERROR] Something went wrong while updating billing profile "%s" (team "%s")', $billingProfile->getUuid()->toString(), $team->getSlug()));
                $logger->warning('Was unable to refresh billing profile', [
                    'exception' => $e,
                    'billing_profile_uuid' => $billingProfile->getUuid()->toString(),
                ]);
            }
        }
    }

    private function getLogger() : LoggerInterface
    {
        return $this->getContainer()->get('logger');
    }

    private function getRecurlyPlanManager(): RecurlyPlanManager
    {
        return $this->getContainer()->get('app.billing.plan_manager');
    }

    private function getBillingProfileRepository() : UserBillingProfileRepository
    {
        return $this->getContainer()->get('app.repository.billing_profile');
    }

    private function getTeamRepository() : TeamRepository
    {
        return $this->getContainer()->get('app.repository.team');
    }
}
