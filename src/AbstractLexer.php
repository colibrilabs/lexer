<?php

namespace Subapp\Lexer;

/**
 * Class AbstractLexer
 * @package Subapp\Lexer
 */
abstract class AbstractLexer implements \Iterator, LexerInterface
{

    const T_UNDEFINED = -1;

    const TYPE = 'type';
    const POSITION = 'position';
    const TOKEN = 'token';

    /**
     * @var array|null
     */
    public $token;

    /**
     * @var string
     */
    private $input;

    /**
     * @var array
     */
    private $tokens = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $peek = 1;

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->isValid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->reset();

        $this->token = $this->isValid() ? $this->tokens[$this->position] : null;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->token = null;
        $this->position = 0;

        return $this;
    }

    /**
     * @return bool
     */
    public function next()
    {
        $this->peek = 1;
        $this->position++;

        $this->token = $this->isValid() ? $this->tokens[$this->position] : null;

        return $this->isValid();
    }

    /**
     * @return bool
     */
    public function previous()
    {
        $this->peek = 1;
        $this->position--;

        $this->token = $this->isValid() ? $this->tokens[$this->position] : null;

        return $this->isValid();
    }

    /**
     * @return mixed|null
     */
    public function peek()
    {
        return isset($this->tokens[$this->position + $this->peek + 1]) ? $this->tokens[$this->position + $this->peek++ + 1] : null;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return isset($this->tokens[$this->position]);
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return isset($this->tokens[$this->position + 1]);
    }

    /**
     * @return bool
     */
    public function hasPrevious()
    {
        return isset($this->tokens[$this->position - 1]);
    }

    /**
     * @return array|null
     */
    public function getNext()
    {
        return $this->hasNext() ? $this->tokens[$this->position + 1] : null;
    }

    /**
     * @return array|null
     */
    public function getPrevious()
    {
        return $this->hasNext() ? $this->tokens[$this->position - 1] : null;
    }

    /**
     * @param mixed $input
     */
    public function setInput($input)
    {
        $this->input = $input;
        $this->tokens = [];

        $this->reset();
        $this->tokenize();
    }

    /**
     * @param $token
     * @return array|null
     */
    public function forwardTo($token)
    {
        while ($this->isValid() && !$this->isNext($token)) {
            $this->next();
        }

        return $this->current();
    }

    /**
     * @param $token
     * @return array|null
     */
    public function backwardTo($token)
    {
        while ($this->isValid() && !$this->isPrevious($token)) {
            $this->previous();
        }

        return $this->current();
    }

    /**
     * @param $token
     * @return bool
     */
    public function toToken($token)
    {
        return ($this->isNext($token) && $this->next()) ? true : false;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function toTokenAny(array $tokens)
    {
        return ($this->isNextAny($tokens) && $this->next()) ? true : false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function backToToken($token)
    {
        return ($this->isPrevious($token) && $this->previous()) ? true : false;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function backToTokenAny(array $tokens)
    {
        return ($this->isPreviousAny($tokens) && $this->previous()) ? true : false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function isCurrent($token)
    {
        return $this->isValid() && ($this->getTokenType() === $token);
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function isCurrentAny(array $tokens)
    {
        return $this->hasNext() && in_array($this->getTokenType(), $tokens, true);
    }

    /**
     * @param $token
     * @return bool
     */
    public function isNext($token)
    {
        return $this->hasNext() && ($this->tokens[$this->position + 1][static::TYPE] === $token);
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function isNextAny(array $tokens)
    {
        return $this->hasNext() && in_array($this->tokens[$this->position + 1][static::TYPE], $tokens, true);
    }

    /**
     * @param $token
     * @return bool
     */
    public function isPrevious($token)
    {
        return $this->hasPrevious() && ($this->tokens[$this->position - 1][static::TYPE] === $token);
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function isPreviousAny(array $tokens)
    {
        return $this->hasPrevious() && in_array($this->tokens[$this->position - 1][static::TYPE], $tokens, true);
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getTokenPosition()
    {
        return $this->token[self::POSITION];
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->token[self::TYPE];
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $token
     * @return string
     */
    public function getLiteral($token)
    {
        $reflection = new \ReflectionClass(static::class);

        foreach ($reflection->getConstants() as $name => $constant) {
            if ($token === $constant) {
                return sprintf('%s::%s', static::class, $name);
            }
        }

        return $token;
    }

    /**
     * @return void
     */
    protected function tokenize()
    {
        $matches = $this->parse($this->getInput());

        foreach ($matches as $match) {
            if (($token = $this->createToken($match)) && $this->isApplicable($token)) {
                $this->tokens[] = $token;
            }
        }
    }

    /**
     * @param string $input
     * @return array
     */
    protected function parse($input)
    {
        $pattern = $this->getPattern();

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($pattern, $input, -1, $flags);

        return $matches;
    }

    /**
     * @return string
     */
    protected function getPattern()
    {
        static $pattern;

        if (null === $pattern) {
            $nonCatchable = implode('|', $this->getDummyPatterns());
            $catchable = implode('|', $this->getPatterns());
            $pattern = sprintf('/(%s)|%s/%s', $catchable, $nonCatchable, $this->getModifiers());
        }

        return $pattern;
    }

    /**
     * @return string
     */
    protected function getModifiers()
    {
        return 'i';
    }

    /**
     * @return array
     */
    abstract protected function getDummyPatterns();

    /**
     * @return array
     */
    abstract protected function getPatterns();

    /**
     * @param TokenInterface $token
     * @return void
     */
    abstract protected function completeToken(TokenInterface $token);

    /**
     * @param TokenInterface $token
     * @return boolean
     */
    abstract protected function isApplicable(TokenInterface $token);

    /**
     * @param array $matchToken
     * @return TokenInterface
     */
    protected function createToken(array $matchToken)
    {
        // extract token value and token position
        list($value, $position) = $matchToken;

        // create token object without token type
        $token = new Token($value, AbstractLexer::T_UNDEFINED, $position);

        // definition token type and token value post-processing...
        // it works at concrete class implementation
        $this->completeToken($token);

        return $token;
    }

}
