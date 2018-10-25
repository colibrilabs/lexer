<?php

namespace Subapp\Lexer;

/**
 * Class AbstractLexer
 * @package Subapp\Lexer
 */
interface LexerInterface
{

    /**
     * @inheritDoc
     */
    public function current();

    /**
     * @inheritDoc
     */
    public function key();

    /**
     * @inheritDoc
     */
    public function valid();

    /**
     * @inheritDoc
     */
    public function rewind();

    /**
     * @return $this
     */
    public function reset();

    /**
     * @return bool
     */
    public function next();

    /**
     * @return bool
     */
    public function previous();

    /**
     * @return mixed|null
     */
    public function peek();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return bool
     */
    public function hasPrevious();

    /**
     * @return array|null
     */
    public function getNext();

    /**
     * @return array|null
     */
    public function getPrevious();

    /**
     * @param mixed $input
     */
    public function setInput($input);

    /**
     * @param $token
     * @return array|null
     */
    public function forwardTo($token);

    /**
     * @param $token
     * @return array|null
     */
    public function backwardTo($token);

    /**
     * @param $token
     * @return bool
     */
    public function toToken($token);

    /**
     * @param array $tokens
     * @return bool
     */
    public function toTokenAny(array $tokens);

    /**
     * @param $token
     * @return bool
     */
    public function backToToken($token);

    /**
     * @param array $tokens
     * @return bool
     */
    public function backToTokenAny(array $tokens);

    /**
     * @param $token
     * @return bool
     */
    public function isCurrent($token);

    /**
     * @param array $tokens
     * @return bool
     */
    public function isCurrentAny(array $tokens);

    /**
     * @param $token
     * @return bool
     */
    public function isNext($token);

    /**
     * @param array $tokens
     * @return bool
     */
    public function isNextAny(array $tokens);

    /**
     * @param $token
     * @return bool
     */
    public function isPrevious($token);

    /**
     * @param array $tokens
     * @return bool
     */
    public function isPreviousAny(array $tokens);

    /**
     * @return string
     */
    public function getInput();

    /**
     * @return array[]
     */
    public function getTokens();

    /**
     * @return array
     */
    public function getToken();

    /**
     * @return integer
     */
    public function getTokenPosition();

    /**
     * @return integer
     */
    public function getTokenType();

    /**
     * @return integer
     */
    public function getPosition();

    /**
     * @param $token
     * @return string
     */
    public function getLiteral($token);

}