<?php

namespace App\Form;

use App\Entity\Valideur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ValideurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
            ->add('date_deb', DateType::class, [
				'widget' => 'single_text',
				'format' => 'yyyy-MM-dd',
				'required' => true
			])
            ->add('date_fin', DateType::class, [
				'widget' => 'single_text',
				'format' => 'yyyy-MM-dd',
				'required' => true
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Valideur::class,
        ]);
    }
}
