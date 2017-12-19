<?php

namespace AuthenticatorBundle\Admin;

use ContinuousPipe\Security\User\User;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected $baseRouteName = 'users';

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = '/users';

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('username', 'text', [
                'read_only' => true,
            ])
            ->add('roles', 'choice', [
                'choices' => [
                    'ROLE_USER' => 'User',
                    'ROLE_ADMIN' => 'Administrator',
                    'ROLE_GHOST' => 'Ghost',
                ],
                'multiple' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('roles')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('username')
            ->add('roles')
        ;
    }
}
