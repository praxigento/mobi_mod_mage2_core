<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Core\App\Api\Web\Response;

/**
 * Response result data. If "SUCCESS" then $text is omitted.
 */
class Result
    extends \Praxigento\Core\Data
{
    /**
     * @return string
     */
    public function getCode()
    {
        $result = parent::getCode();
        return $result;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        $result = parent::getText();
        return $result;
    }

    /**
     * @param string $data
     */
    public function setCode($data)
    {
        parent::setCode($data);
    }

    /**
     * @param string $data
     */
    public function setText($data)
    {
        parent::setText($data);
    }
}