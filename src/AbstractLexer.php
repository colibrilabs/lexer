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
  
    return $this;
  }
  
  /**
   * @return bool
   */
  public function next()
  {
    $this->peek = 0;
    $this->position++;
  
    $this->token = $this->isValid() ? $this->tokens[$this->position] : null;
    
    return $this->isValid();
  }
  
  /**
   * @return mixed|null
   */
  public function peek()
  {
    return isset($this->tokens[$this->position + $this->peek]) ? $this->tokens[$this->position + $this->peek++] : null;
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
   * @return array|null
   */
  public function getNext()
  {
    return $this->hasNext() ? $this->tokens[$this->position + 1] : null;
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
  public function skipUntil($token)
  {
    while ($this->isValid() && $this->token[static::TYPE] !== $token) $this->next();
    
    return $this->current();
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
