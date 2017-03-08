<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;

class TidesPerHourAdmin extends Admin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name', 'text', ['read_only' => true])
            ->add('has_trial', 'text', ['read_only' => true])
            ->add('tides_per_hour', 'number')
        ;
    }

}