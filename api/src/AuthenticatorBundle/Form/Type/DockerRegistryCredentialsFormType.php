<?php

namespace AuthenticatorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DockerRegistryCredentialsFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('password', 'text')
            ->add('email', 'email')
            ->add('serverAddress', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ContinuousPipe\User\DockerRegistryCredentials',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'docker_registry_credentials';
    }
}
