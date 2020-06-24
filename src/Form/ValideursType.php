<?php
namespace App\Form;

use App\Entity\Valideurs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ValideursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder
			->add('valideurs', CollectionType::class, [
				'entry_type' => ValideurType::class,
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
            'data_class' => Valideurs::class,
        ]);
    }
}