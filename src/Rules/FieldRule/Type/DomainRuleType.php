<?php

namespace Mosparo\Rules\FieldRule\Type;

use Mosparo\Entity\RuleItem;
use Mosparo\Rules\FieldRule\Tester\DomainRuleTester;

final class DomainRuleType extends AbstractRuleType
{
    protected string $key = 'domain';
    protected string $name = 'rules.fieldRule.type.domain.title';
    protected string $description = 'rules.fieldRule.type.domain.shortIntro';
    protected string $icon = 'ti ti-building';
    protected array $subtypes = [
        [
            'key' => 'domain',
            'name' => 'rules.fieldRule.type.domain.domain.title',
        ],
    ];
    protected string $testerClass = DomainRuleTester::class;
    protected array $targetFieldKeys = ['formData.input[url]', 'formData.input[email]', 'formData.textarea'];
    protected string $helpTemplate = 'project_related/rules/field_rule/type/help/domain.html.twig';

    public function getValidatorPattern(): array
    {
        return [
            // This pattern tries to match it as good as possible but is not to be 100% precise.
            'domain' => '^([\w\-\.]+\.)*[\w\-\.]+\.\w{2,}$',
        ];
    }

    public function convertValueIntoRuleItem(string $value): RuleItem
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $value = substr($value, strrpos($value, '@') + 1);
        } else if (filter_var($value, FILTER_VALIDATE_URL)) {
            $value = parse_url($value, PHP_URL_HOST);
        }

        return (new RuleItem())->setValue($value);
    }
}
