<?php

namespace app\base\validators;

use Biblys\Isbn\IsbnParsingException;
use Biblys\Isbn\IsbnValidationException;
use yii\validators\Validator;
use Biblys\Isbn\Isbn;

class IsbnValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        try {
            $model->$attribute = Isbn::convertToIsbn13($model->$attribute);
            Isbn::validateAsIsbn13($model->$attribute);
        } catch (IsbnParsingException|IsbnValidationException $e) {
            $this->addError($model, $attribute, 'Incorrect ISBN: '.$e->getMessage());
        }
    }
}
