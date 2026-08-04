<?php

namespace Mosparo\Form;

use Doctrine\ORM\EntityManagerInterface;
use Mosparo\Entity\RuleItem;
use Mosparo\Rules\FieldRule\Type\EmailRuleType;
use Mosparo\Rules\FieldRule\Type\UnicodeBlockRuleType;
use Mosparo\Util\ChoicesUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use zepi\Unicode\UnicodeIndex;

class RuleItemFormType extends AbstractType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Mosparo\Rules\FieldRule\Type\RuleTypeInterface $ruleType */
        $ruleType = $options['rule_type'];
        if ($ruleType === null) {
            return;
        }

        $locale = $options['locale'];

        $choices = ChoicesUtil::buildChoices($ruleType->getSubtypes());
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'rules.fieldRule.form.items.type',
                'choices' => $choices,
                'attr' => ['readonly' => (count($choices) === 1)]
            ])
            ->add('spamRatingFactor', NumberType::class, [
                'label' => 'rules.fieldRule.form.items.rating',
                'required' => false,
                'html5' => true,
                'scale' => 1,
                'attr' => [
                    'placeholder' => '1.0',
                    'class' => 'rule-item-rating',
                    'min' => -1000000,
                    'max' => 1000000,
                    'step' => 'any',
                ]
            ])
        ;

        if ($ruleType instanceof UnicodeBlockRuleType) {
            $unicodeIndex = new UnicodeIndex();
            $blockChoices = [];
            foreach ($unicodeIndex->getIndex() as $key => $className) {
                $block = new $className();
                $blockChoices[$block->getName($locale)] = $key;
            }

            uksort($blockChoices, [$this, 'sortBlocks']);

            $builder
                ->add('value', ChoiceType::class, ['label' => 'rules.fieldRule.form.items.value', 'choices' => $blockChoices])
            ;
        } else if ($ruleType instanceof EmailRuleType) {
            $builder
                ->add('value', EmailType::class, [
                    'label' => 'rules.fieldRule.form.items.value',
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
            ;
        } else {
            $builder
                ->add('value', TextType::class, [
                    'label' => 'rules.fieldRule.form.items.value',
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RuleItem::class,
            'rule_type' => null,
            'block_duplicated_value' => false,
            'locale' => 'en',
            'translation_domain' => 'mosparo',
            'constraints' => [
                new Callback([$this, 'validateValue']),
            ],
        ]);
    }

    public function sortBlocks(string $keyA, string $keyB): int
    {
        $keyA = mb_strtolower($keyA);
        $keyB = mb_strtolower($keyB);

        $pattern = ['ä', 'ö', 'ü'];
        $replacement = ['a', 'o', 'u'];

        $keyA = str_replace($pattern, $replacement, $keyA);
        $keyB = str_replace($pattern, $replacement, $keyB);

        if ($keyA < $keyB) {
            return -1;
        } else if ($keyA > $keyB) {
            return 1;
        } else {
            return 0;
        }
    }

    public function validateValue(mixed $data, ExecutionContextInterface $context): void
    {
        /** @var \Mosparo\Entity\RuleItem $ruleItem */
        $ruleItem = $data;

        /** @var \Symfony\Component\Form\Form $form */
        $form = $context->getObject();
        $options = $form->getConfig()->getOptions();

        // If we add the item to an existing rule, we verify that the value does not exist yet.
        if ($options['block_duplicated_value'] ?? false) {
            $qb = $this->entityManager->createQueryBuilder()
                ->select('ri.id')
                ->from(RuleItem::class, 'ri')
                ->where('ri.rule = :rule')
                ->andWhere('ri.type = :type')
                ->andWhere('ri.value = :value')
                ->setParameter('rule', $ruleItem->getRule())
                ->setParameter('type', $ruleItem->getType())
                ->setParameter('value', $ruleItem->getValue())
                ->setMaxResults(1);

            $result = $qb->getQuery()->getOneOrNullResult();
            if ($result['id'] ?? false) {
                $context->buildViolation('rules.fieldRule.value.alreadyExists')
                    ->atPath('value')
                    ->addViolation();
                return;
            }
        }

        /** @var \Mosparo\Rules\FieldRule\Type\RuleTypeInterface $ruleType */
        $ruleType = $options['rule_type'];
        if ($ruleType === null) {
            return;
        }

        $ruleItemType = $data->getType();
        if (in_array($ruleItemType, ['regex', 'uaRegex'])) {
            if (@preg_match($data->getValue(), '') === false) {
                $context->buildViolation('rules.fieldRule.value.invalidFormat')
                    ->atPath('value')
                    ->addViolation();
            }

            return;
        }

        $validatorPatterns = $ruleType->getValidatorPattern();
        $validatorPattern = $validatorPatterns[$data->getType()] ?? null;
        if (!$validatorPattern) {
            return;
        }

        if (preg_match('/' . $validatorPattern . '/', $data->getValue())) {
            return;
        }

        $context->buildViolation('rules.fieldRule.value.invalidFormat')
            ->atPath('value')
            ->addViolation();
    }
}
