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
     * @return TokenInterface|null
     */
    public function peek();

    /**
     * @param integer $increaser
     * @param integer $decreaser
     * @param boolean $resetPeek
     * @return TokenInterface
     */
    public function peekBeyond($increaser, $decreaser, $resetPeek = false);
    
    /**
     * @param integer $steps
     */
    public function setPeek($steps = 1);
    
    /**
     * @return void
     */
    public function resetPeek();

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
     * @return TokenInterface|null
     */
    public function getNext();

    /**
     * @return TokenInterface|null
     */
    public function getPrevious();

    /**
     * @param string $input
     */
    public function setInput($input);

    /**
     * @param $token
     * @return TokenInterface|null
     */
    public function forwardTo($token);

    /**
     * @param $token
     * @return TokenInterface|null
     */
    public function backwardTo($token);

    /**
     * @param $token
     * @return boolean
     */
    public function toToken($token);

    /**
     * @param array $tokens
     * @return boolean
     */
    public function toTokenAny(array $tokens);

    /**
     * @param $token
     * @return boolean
     */
    public function backToToken($token);

    /**
     * @param array $tokens
     * @return boolean
     */
    public function backToTokenAny(array $tokens);

    /**
     * @param integer $type
     * @param integer $limit
     * @return boolean
     */
    public function isTokenNearby($type, $limit = 3);

    /**
     * @param $token
     * @return boolean
     */
    public function isCurrent($token);

    /**
     * @param array $tokens
     * @return boolean
     */
    public function isCurrentAny(array $tokens);

    /**
     * @param $token
     * @return boolean
     */
    public function isNext($token);

    /**
     * @param array $tokens
     * @return boolean
     */
    public function isNextAny(array $tokens);

    /**
     * @param $token
     * @return boolean
     */
    public function isPrevious($token);

    /**
     * @param array $tokens
     * @return boolean
     */
    public function isPreviousAny(array $tokens);

    /**
     * @return string
     */
    public function getInput();

    /**
     * @return TokenInterface[]
     */
    public function getTokens();

    /**
     * @return TokenInterface
     */
    public function getToken();

    /**
     * @return string
     */
    public function getTokenValue();
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
     * @return void
     */
    public function resetPosition();

    /**
     * @param $token
     * @return string
     */
    public function getLiteral($token);

}