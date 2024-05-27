<?php

declare(strict_types=1);

namespace App\Form;

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
    private array $localeCodes;

    public function __construct(string $locale)
    {
        $localeCodes = explode('|', $locale);
        sort($localeCodes);
        $this->localeCodes = $localeCodes;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class, [
                'label' => 'label.fullName',
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'label.language',
                'choices' => $this->getLocales(),
            ])
            ->add('email', TextType::class, [
                'label' => 'label.email',
                'disabled' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'btn.save',
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
