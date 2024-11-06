<?php

namespace App\Form;

use App\Entity\Admin;
use App\Entity\Participant;
use App\Entity\Participation;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'required' => true,
            'attr' => ['placeholder' => 'Entrez votre nom et prénom']])
        ->add('email', EmailType::class, [
            'required' => true,
            'attr' => ['placeholder' => 'exemple@gmail.com']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
