<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;

class ListTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var Transformation
     */
    private $transformation;

    /**
     * @param Transformation $transformation
     */
    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (false === is_array($from)) {
            throw new ConstraintViolationException(
                'The input value must be an array',
                'must_be_array'
            );
        }

        $result = [];
        foreach ($from as $value) {
            $transformedValue = $this->transformation->transform($value);
            $result[] = $transformedValue;
        }

        return $result;
    }
}
