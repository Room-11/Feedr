<?php declare(strict_types=1);
/**
 * GitHub repository collection
 *
 * PHP version 7.0
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2015 Pieter Hordijk <https://github.com/PeeHaa>
 * @license    See the LICENSE file
 * @version    1.0.0
 */
namespace Feedr\Storage\GitHub\Data;

/**
 * GitHub repository collection
 *
 * @category   Feedr
 * @package    Storage
 * @subpackage GitHub
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class RepositoryCollection implements \Iterator, \Countable
{
    /**
     * @var array List of repositories in the collection
     */
    private $repositories = [];

    public function addFromApiResult(array $repositories)
    {
        foreach ($repositories as $repository) {
            $this->repositories[] = new Repository($repository);
        }
    }

    public function count(): int
    {
        return count($this->repositories);
    }

    public function rewind()
    {
        reset($this->repositories);
    }

    public function current(): Repository
    {
        return current($this->repositories);
    }

    public function key(): int
    {
        return key($this->repositories);
    }

    public function next()
    {
        next($this->repositories);
    }

    public function valid(): bool
    {
        return key($this->repositories) !== null;
    }
}
