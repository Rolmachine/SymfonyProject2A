<?php

namespace Rol\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApplicationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'date')
            ->add('content', 'textarea')
            //->add('image', new ImageType())
            ->add('save', 'submit')
        ;
        
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,    // 1er argument : L'évènement qui nous intéresse : ici, PRE_SET_DATA
        function(FormEvent $event) { // 2e argument : La fonction à exécuter lorsque l'évènement est déclenché
            // On récupère notre objet Advert sous-jacent
            $Advert = $event->getData();

            // Cette condition est importante, on en reparle plus loin
            if (null === $Advert) {
                return; // On sort de la fonction sans rien faire lorsque $Advert vaut null
            }
            
        }
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rol\PlatformBundle\Entity\Application'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rol_platformbundle_application';
    }
}
