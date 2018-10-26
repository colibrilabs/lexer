<?php

namespace Subapp\Lexer;

/**
 * Class Token
 * @package Subapp\Lexer
 */
class Token implements TokenInterface
{

    /**
     * @var string
     */
    private $token;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var integer
     */
    private $position;

    /**
     * Token constructor.
     * @param string $token
     * @param int $type
     * @param int $position
     */
    public function __construct($token, $type, $position)
    {
        $this->token = $token;
        $this->type = $type;
        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function __debugInfo()
    {
        return ['token', sprintf('[%s]:%d at %d', $this->getToken(), $this->getType(), $this->getPosition()),];
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

}