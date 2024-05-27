<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Form;

use App\Entity\Enabel\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfile extends AbstractType
{
    /** @var array<string>  */
    //private array $localeCodes;

//    public function __construct(string $locales)
//    {
//        $localeCodes = explode('|', $locales);
//        sort($localeCodes);
//        $this->localeCodes = $localeCodes;
//    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, [
                'label' => 'enabel_user.profile.label.fullName',
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'enabel_user.profile.label.language',
                'choices' => ['en' => 'en', 'fr' => 'fr'],
            ])
            ->add('email', TextType::class, [
                'label' => 'enabel_user.profile.label.email',
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'enabel_user.profile.btn.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getLocales(): array
    {
        $locales = [];

        foreach ($this->localeCodes as $localeCode) {
            $locales[Locales::getName($localeCode)] = $localeCode;
        }

        return $locales;
    }
}
