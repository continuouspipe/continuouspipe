<?php

namespace AppBundle\Admin;

use \Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TidesPerHourAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected $baseRouteName = 'users_tides_per_hour';

    /**
     * {@inheritdoc}
     */
    protected $baseRoutePattern = '/users/tides-per-hour';

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('user.username')
            ->add('name')
            ->add('has_trial')
            ->add('tides_per_hour')
            ->add('_action', 'actions', ['actions' => ['edit' => []]]);
    }


    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('user.username', 'text', ['read_only' => true])
            ->add('name', 'text', ['read_only' => true])
            ->add('tides_per_hour', 'number')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user.username')
//            ->add('name')
//            ->add('tides_per_hour')
        ;
    }
}