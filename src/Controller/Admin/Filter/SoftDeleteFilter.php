<?php

declare(strict_types=1);

namespace Enabel\UserBundle\Controller\Admin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SoftDeleteFilter implements FilterInterface
{
    use FilterTrait;

    private const CHOICE_VALUE_YES = true;
    private const CHOICE_VALUE_NO = false;

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ChoiceType::class);
    }

    public function setChoiceLabels(string $yesChoiceLabel, string $noChoiceLabel): self
    {
        $this->dto->setFormTypeOption('choices', [
            $yesChoiceLabel => self::CHOICE_VALUE_YES,
            $noChoiceLabel => self::CHOICE_VALUE_NO,
        ]);

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function apply(
        QueryBuilder $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto $fieldDto,
        EntityDto $entityDto
    ): void {
        if (self::CHOICE_VALUE_YES === $filterDataDto->getValue()) {
            $queryBuilder->andWhere(
                sprintf('%s.%s IS NOT NULL', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty())
            );
        } else {
            $queryBuilder->andWhere(
                sprintf('%s.%s IS NULL', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty())
            );
        }
    }
}
