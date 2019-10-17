<?php

namespace app\components;


class JwtValidationData extends \sizeg\jwt\JwtValidationData
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->validationData->setIssuer('http://test.loc');
        $this->validationData->setAudience('http://test.loc');
        $this->validationData->setId('4f1g23a12aa');

        parent::init();
    }
}