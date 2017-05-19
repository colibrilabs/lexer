<?php

namespace Colibri\Lexer;

/**
 * Class AbstractLexer
 * @package Colibri\Lexer
 */
abstract class AbstractLexer implements \Iterator
{
  
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
  public function shiftTo($token)
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
  public function unshiftTo($token)
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
      $tokenResult = $this->processToken($match[0]);
      $tokenResult[static::POSITION] = $match[1];
      
      $this->tokens[] = $tokenResult;
    }
  }
  
  /**
   * @param string $input
   * @return array
   */
  protected function parse($input)
  {
    $pattern = $this->getGeneratedPattern();
  
    $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
    $matches = preg_split($pattern, $input, -1, $flags);
    
    return $matches;
  }
  
  /**
   * @return string
   */
  protected function getGeneratedPattern()
  {
    static $pattern;
    
    if (null === $pattern) {
      $catchable = implode('|', $this->getCatchablePatterns());
      $nonCatchable = implode('|', $this->getNonCatchablePatterns());
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
  abstract protected function getNonCatchablePatterns();
  
  /**
   * @return array
   */
  abstract protected function getCatchablePatterns();
  
  /**
   * @param $token
   * @return integer
   */
  abstract protected function processToken($token);
  
}
