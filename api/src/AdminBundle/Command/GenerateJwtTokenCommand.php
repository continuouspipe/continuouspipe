<?php

namespace AdminBundle\Command;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class GenerateJwtTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('generate:jwt-token');

        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addOption('ttl', null, InputOption::VALUE_REQUIRED, 'Time-to-live (in seconds) of the token', '3600');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $token = $this->generate(
            new User($input->getArgument('username'), null),
            intval($input->getOption('ttl'))
        );

        $output->writeln([
            '',
            '   <info>'.$token.'</info>',
            ''
        ]);
    }

    private function generate(UserInterface $user, int $ttl) : string
    {
        $payload = [
            'username' => $user->getUsername(),
            'exp' => time() + $ttl,
        ];

        $jwtCreatedEvent = new JWTCreatedEvent(
            $payload,
            $user
        );

        $this->getDispatcher()->dispatch(Events::JWT_CREATED, $jwtCreatedEvent);

        $jwtString = $this->getJwtEncoder()->encode($jwtCreatedEvent->getData());

        $jwtEncodedEvent = new JWTEncodedEvent($jwtString);
        $this->getDispatcher()->dispatch(Events::JWT_ENCODED, $jwtEncodedEvent);

        return $jwtString;
    }

    private function getDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    private function getJwtEncoder()
    {
        return $this->getContainer()->get('lexik_jwt_authentication.encoder');
    }
}
