<?php

namespace Subapp\Lexer;

/**
 * Interface TokenInterface
 * @package Subapp\Lexer
 */
interface TokenInterface
{

    /**
     * @return string
     */
    public function getToken();

    /**
     * @return integer
     */
    public function getType();

    /**
     * @return integer
     */
    public function getPosition();

    /**
     * @param string $token
     */
    public function setToken($token);

    /**
     * @param integer $type
     */
    public function setType($type);

    /**
     * @param integer $position
     */
    public function setPosition($position);

}