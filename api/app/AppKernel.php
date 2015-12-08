<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
            new ContinuousPipe\SecurityBundle\ContinuousPipeSecurityBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new LogStream\LogStreamBundle(),
            new WorkerBundle\WorkerBundle(),
            new AppBundle\AppBundle(),
        );

        if (in_array($this->getEnvironment(), ['test', 'smoke_test'])) {
            $bundles[] = new AppTestBundle\AppTestBundle();
        }

        $bundles[] = new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle();
        $bundles[] = new SimpleBus\SymfonyBridge\SimpleBusEventBusBundle();
        $bundles[] = new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle();
        $bundles[] = new SimpleBus\AsynchronousBundle\SimpleBusAsynchronousBundle();
        $bundles[] = new SimpleBus\RabbitMQBundleBridge\SimpleBusRabbitMQBundleBridgeBundle();
        $bundles[] = new SimpleBus\JMSSerializerBundleBridge\SimpleBusJMSSerializerBundleBridgeBundle();
        $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        $bundles[] = new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle();

        if (in_array($this->getEnvironment(), array('dev', 'test', 'smoke_test'))) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
