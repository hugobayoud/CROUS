<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditUserRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
				'constraints' => [
					new NotBlank([
						'message' => 'Merci de saisir une adresse email'
					])
				],
				'required' => true,
				'attr' => [
					'class' => 'form-control'
				]
			])
            ->add('dsi')
            ->add('roles', ChoiceType::class, [
				'choices' => [
					'Agent' 			=> 'ROLE_USER',
					'DSI' 				=> 'ROLE_DSI',
					'Administrateur' 	=> 'ROLE_ADMIN'
				],
				'multiple' => true,
				'label' => 'Rôles'
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
