<?php

namespace AuthenticatorBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class WhiteListedUserAdmin extends AbstractAdmin
{
    /**
     * {@inheritdoc}
     */
    protected $baseRouteName = 'users_white_list';

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = '/users/white-list';

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('gitHubUsername')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('gitHubUsername')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('gitHubUsername')
        ;
    }
}
