<?php
namespace App\Form;

use App\Data\SearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class SearchForm extends AbstractType
{
	public function buildForm( $builder, array $options)
	{
		$builder
			->add('q', TextType::class, [
				'label' => false,
				'required' => false,
				'attr' => [
					'placeholder' => 'Rechercher un service'
				]
			])	
			;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => SearchData::class,
			'method' => 'GET',
			'csrf_protection' => false
		]);
	}

	public function getBlockPrefix()
	{
		return '';
	}
}