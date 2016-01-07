<?php

namespace AppBundle\Util;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class Util
 *
 * This class contains static utility methods that are useful in different controllers.
 */
class Util
{
    /**
     * Constructs an array ready to be encoded to JSON containing error messages for constraint
     * violations.
     *
     * @param ConstraintViolationListInterface $list
     * @return array
     */
    public static function violationListToJson(ConstraintViolationListInterface $list)
    {
        $errorList = array();

        foreach ($list as $e) {
            $errorList[] = $e->getPropertyPath() . ': ' . $e->getMessage();
        }

        $data = array(
            'message' => 'Invalid arguments',
            'list' => $errorList
        );

        return $data;
    }
}