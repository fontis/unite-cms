<?php

namespace UniteCMS\CoreBundle\Field\Types;

use Symfony\Component\Validator\Constraints as BaseAssert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UniteCMS\CoreBundle\Entity\FieldableField;
use UniteCMS\CoreBundle\Field\FieldableFieldSettings;
use UniteCMS\CoreBundle\Field\FieldType;
use UniteCMS\CoreBundle\Field\FieldTypeManager;
use UniteCMS\CoreBundle\Form\DateTimeType;
use UniteCMS\CoreBundle\Validator\Constraints as Assert;

class DateTimeFieldType extends DateFieldType
{
    const TYPE = "datetime";
    const FORM_TYPE = DateTimeType::class;

    const SETTINGS = ['not_empty', 'description', 'default', 'form_group', 'min', 'max', 'step'];

    /**
     * {@inheritdoc}
     */
    function alterViewFieldSettings(array &$settings, FieldTypeManager $fieldTypeManager, FieldableField $field = null) {
        parent::alterViewFieldSettings($settings, $fieldTypeManager, $field);
        $settings['type'] = 'date';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateDefaultValue($value, FieldableFieldSettings $settings, ExecutionContextInterface $context) {
        if ($context->getValidator()
                ->validate($value, new Assert\DateTimeLocal())
                ->count() > 0
        ) {
            $context->buildViolation('no_datetime_value')->atPath('min')->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    function getFormOptions(FieldableField $field): array
    {
        return array_merge(
            parent::getFormOptions($field),
            [
                'min' => $field->getSettings()->min ?? null,
                'max' => $field->getSettings()->max ?? null,
                'step' => $field->getSettings()->step ?? null,
            ]
        );
    }

    function validateSettings(FieldableFieldSettings $settings, ExecutionContextInterface $context)
    {
        FieldType::validateSettings($settings, $context);
        $settingsArray = $settings ? get_object_vars($settings) : [];

        if (!empty($settingsArray['min'])) {
            if ($context->getValidator()
                    ->validate($settingsArray['min'], new Assert\DateTimeLocal())
                    ->count() > 0
            ) {
                $context->buildViolation('no_datetime_value')->atPath('min')->addViolation();
            }
        }

        if (!empty($settingsArray['max'])) {
            if ($context->getValidator()
                    ->validate($settingsArray['max'], new Assert\DateTimeLocal())
                    ->count() > 0
            ) {
                $context->buildViolation('no_datetime_value')->atPath('max')->addViolation();
            }
        }

        if (!empty($settingsArray['min']) && !empty($settingsArray['max'])) {
            if ($context->getValidator()
                    ->validate($settingsArray['min'], new BaseAssert\LessThanOrEqual(['value' => $settingsArray['max']]))
                    ->count() > 0
            ) {
                $context->buildViolation('min_greater_than_max')->atPath('min')->addViolation();
            }
        }
    }
}