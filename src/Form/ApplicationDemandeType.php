<?php

namespace App\Form;

use App\Entity\Application;
use App\Entity\ApplicationDemande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ApplicationDemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
		//PROPOSER UN SELECT POUR LE CHOIX DE LAPPLICATION
			->add('application', EntityType::class, [
				'class' => Application::class,
				'choice_label' => 'code',
			])
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
            'data_class' => ApplicationDemande::class,
        ]);
    }
}
