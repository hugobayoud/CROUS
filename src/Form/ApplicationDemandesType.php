<?php
namespace App\Form;

use App\Entity\ApplicationDemandes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationDemandesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
			->add('applicationDemandes', CollectionType::class, [
				'entry_type' => ApplicationDemandeType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
				'label' => false,
			])
			->add('save', SubmitType::class)
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApplicationDemandes::class,
        ]);
    }
}