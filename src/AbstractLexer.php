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
    
    const MAX_PEEK_STEPS = 5;

    /**
     * @var TokenInterface|null
     */
    public $token;

    /**
     * @var string
     */
    private $input;

    /**
     * @var array|TokenInterface[]
     */
    private $tokens = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $peek = 0;

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
        $this->peek = 0;

        return $this;
    }

    /**
     * @return bool
     */
    public function next()
    {
        $this->setPeek(0);
        $this->position++;

        $this->token = $this->isValid() ? $this->tokens[$this->position] : null;

        return $this->isValid();
    }

    /**
     * @return bool
     */
    public function previous()
    {
        $this->setPeek(0);
        $this->position--;

        $this->token = $this->isValid() ? $this->tokens[$this->position] : null;

        return $this->isValid();
    }

    /**
     * @return TokenInterface|null
     */
    public function peek()
    {
        return isset($this->tokens[$this->position + $this->peek + 1]) ? $this->tokens[$this->position + ++$this->peek] : null;
    }

    /**
     * @param integer $increaser
     * @param integer $decreaser
     * @param bool $resetPeek
     * @return TokenInterface
     */
    public function peekBeyond($increaser, $decreaser, $resetPeek = false)
    {
        $counter = 0;
        $token = $this->peek();

        do {
            if ($token->is($increaser) || $token->is($decreaser)) {
                $counter = ($counter + ($token->is($increaser) ? 1 : -1));
            }
        } while ($counter > 0 && ($token = $this->peek()));

        if (true === $resetPeek) {
            $this->resetPeek();
        }

        return $token;
    }
    
    /**
     * @param int $steps
     */
    public function setPeek($steps = 1)
    {
        $this->peek = (integer)min(abs($steps), AbstractLexer::MAX_PEEK_STEPS);
    }
    
    /**
     * @return void
     */
    public function resetPeek()
    {
        $this->peek = 0;
    }
    
    /**
     * @return bool
     */
    public function isValid()
    {
        return isset($this->tokens[$this->position]);
    }

    /**
     * @return boolean
     */
    public function hasNext()
    {
        return isset($this->tokens[$this->position + 1]);
    }

    /**
     * @return boolean
     */
    public function hasPrevious()
    {
        return isset($this->tokens[$this->position - 1]);
    }

    /**
     * @return TokenInterface|null
     */
    public function getNext()
    {
        return $this->hasNext() ? $this->tokens[$this->position + 1] : null;
    }

    /**
     * @return TokenInterface|null
     */
    public function getPrevious()
    {
        return $this->hasNext() ? $this->tokens[$this->position - 1] : null;
    }

    /**
     * @param string $input
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
     * @return TokenInterface|null
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
     * @return TokenInterface|null
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
     * @return boolean
     */
    public function toToken($token)
    {
        return ($this->isNext($token) && $this->next()) ? true : false;
    }

    /**
     * @param array $tokens
     * @return boolean
     */
    public function toTokenAny(array $tokens)
    {
        return ($this->isNextAny($tokens) && $this->next()) ? true : false;
    }

    /**
     * @param $token
     * @return boolean
     */
    public function backToToken($token)
    {
        return ($this->isPrevious($token) && $this->previous()) ? true : false;
    }

    /**
     * @param array $tokens
     * @return boolean
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
        $position = ($this->position + 1);

        return $this->hasNext() && ($this->tokens[$position]->getType() === $token);
    }

    /**
     * @param array $tokens
     * @return boolean
     */
    public function isNextAny(array $tokens)
    {
        $position = ($this->position + 1);

        return $this->hasNext() && in_array($this->tokens[$position]->getType(), $tokens, true);
    }

    /**
     * @param $token
     * @return boolean
     */
    public function isPrevious($token)
    {
        $position = ($this->position - 1);

        return $this->hasPrevious() && ($this->tokens[$position]->getType() === $token);
    }

    /**
     * @param array $tokens
     * @return bool
     */
    public function isPreviousAny(array $tokens)
    {
        $position = ($this->position - 1);

        return $this->hasPrevious() && in_array($this->tokens[$position]->getType(), $tokens, true);
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return array|TokenInterface[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return null|string
     */
    public function getTokenValue()
    {
        return $this->isValid() ? $this->token->getToken() : null;
    }

    /**
     * @return integer
     */
    public function getTokenPosition()
    {
        return $this->token->getPosition();
    }

    /**
     * @return integer
     */
    public function getTokenType()
    {
        return $this->token->getType();
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * @return void
     */
    public function resetPosition()
    {
        $this->position = 0;
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
