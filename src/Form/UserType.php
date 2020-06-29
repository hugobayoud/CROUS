<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
			->add('nom', TextType::class, [
				'label' => false,
				'required' => true,
				'attr' => [
					'placeholder' => 'Prénom'
				]
			])
            ->add('prenom', TextType::class, [
				'label' => false,
				'required' => true,
				'attr' => [
					'placeholder' => 'Nom'
				]
			])
            ->add('date_deb_valid', DateType::class, [
				'label' => false,
				'widget' => 'single_text',
				'format' => 'yyyy-MM-dd',
				'required' => true
			])
            ->add('date_fin_valid', DateType::class, [
				'label' => false,
				'widget' => 'single_text',
				'format' => 'yyyy-MM-dd',
				'required' => true
			])
			->add('services', EntityType::class, [
				'class' => Service::class,
				'choice_label' => 'libelle_court',
				'multiple' => true
			])
			->add('enregistrer', SubmitType::class)

			// ->add('dsi', CheckboxType::class, [
			// 	'label_attr' => ['class' => 'switch-custom'],
			// 	'required' => false
			// ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
