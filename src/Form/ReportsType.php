<?php

namespace App\Form;

use App\Entity\Reports;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ReportsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Firstname',TextType::class, [
                'attr' => ['style' => 'margin-top:25px;margin-bottom:10px','label'=>'Client first name ','placeholder'=>'Client first name','class'=>'fadeIn second form-control'],
            ])
            ->add('Lastname',TextType::class,[
                'attr' => ['style' => 'margin-bottom:10px','label'=>'Client last name ','placeholder'=>'Client last name ','class'=>'fadeIn second form-control'],
            ])
            ->add('UserEmail',EmailType::class,[
                'attr' => ['style' => 'margin-bottom:10px','label'=>'Client email ','placeholder'=>'Client Email','class'=>'fadeIn second form-control'],
            ])
            ->add('name', FileType::class, [
                'label' => ' ',
                'required' => true,
                'constraints' => [
                    new File(array(
                        'mimeTypes' => array(
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ),
                        'mimeTypesMessage' => 'Please upload a valid Excel document',
                    ))
                ],
            ])

            ->add('Save',SubmitType::class,[
                'attr' => ['style' => 'margin-top:25px;','class'=>'fadeIn fourthbtn btn-lg btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reports::class,
        ]);
    }
}
