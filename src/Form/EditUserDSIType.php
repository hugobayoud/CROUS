<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EditUserDSIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
			->add('email', EmailType::class)
            ->add('dsi', CheckboxType::class, [
                'label_attr' => ['class' => 'switch-custom'],
            ])
		;

		// A utiliser pour les validations de chaque service
		// $builder->add('dsis', CollectionType::class, [
		// 	// Pour chaque entrée, une checkbox custom
		// 	'dsi' => CheckboxType::class, [
        //         'label_attr' => ['class' => 'switch-custom'],
        //     ],
		// ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
