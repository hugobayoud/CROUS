<?php
namespace App\Form;

use App\Entity\Dsis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DsisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dsis', CollectionType::class, [
            'entry_type' => DsiType::class,
			'entry_options' => ['label' => false],
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dsis::class,
        ]);
    }
}