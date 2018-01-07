<?php

namespace AdminBundle\Twig;

class AdminExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('class', array($this, 'classFilter')),
        );
    }

    /**
     * Return the class of the given object.
     *
     * @param mixed $object
     *
     * @return string
     */
    public function classFilter($object)
    {
        return get_class($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin';
    }
}
