<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;



class AdvertEditType extends AbstractType
{
    /*
     * On build le form à partir du parent mais sans la date
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date');
    }

    /*
     * On récupère le formulaire parent!
     */
    public function getParent()
    {
        return AdvertType::class;
    }


}
