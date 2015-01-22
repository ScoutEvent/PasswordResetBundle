<?php

namespace ScoutEvent\PasswordResetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SendResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email');
        
        $builder->add('Send Reset', 'submit', array(
            'attr'=> array(
                'class' => 'btn btn-success pull-right'
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'sendReset';
    }
}
