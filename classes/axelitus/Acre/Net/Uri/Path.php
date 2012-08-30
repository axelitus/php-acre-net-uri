<?php
/**
 * Part of the axelitus\Acre\Net\Uri Package.
 *
 * @package     axelitus\Acre\Net\Uri
 * @version     0.1
 * @author      Axel Pardemann (dev@axelitus.mx)
 * @license     MIT License     http://opensource.org/licenses/mit-license.php
 * @copyright   2012 - Axel Pardemann
 * @link        http://axelitus.mx/
 */

namespace axelitus\Acre\Net\Uri;

use InvalidArgumentException;
use OutOfBoundsException;
use Countable;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Str as Str;

/**
 * Path Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
final class Path implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @var string  The path segments separators
     */
    const SEPARATOR = '/';

    /**
     * @var string  The name capturing regex pattern to parse a path string
     */
    const REGEX = <<<REGEX
/^(?#path)(?:(?P<path>\/?(?:[A-Za-z0-9\-._~%!$&\'()*+,;=@]+\/?)*))?(?:\?|\#|$)/x
REGEX;

    /**
     * @var array   The path segments
     */
    protected $_segments = array();

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array  $path   An array of strings containing the path segments
     */
    protected function __construct(array $path)
    {
        $this->load($path);
    }

    /**
     * Forges a new instance of the Path class.
     *
     * @static
     * @param string|array  $path   A path-formatted string or an array of strings containing the path segments
     * @return Path     The new instance
     * @throws \InvalidArgumentException
     */
    public static function forge($path = array())
    {
        if (is_string($path)) {
            return static::parse($path);
        } elseif (is_array($path)) {
            return new static($path);
        } else {
            throw new InvalidArgumentException("The \$path parameter must be a string or an array of strings.");
        }
    }

    /**
     * Parses a path formatted string into a Path object.
     *
     * @static
     * @param string    $path   The path-formatted string
     * @return Authority    The new object
     * @throws \InvalidArgumentException
     */
    public static function parse($path)
    {
        return static::forge(static::parseAsArray($path));
    }

    /**
     * Parses a path formatted string into a segments array.
     *
     * @static
     * @param string    $path   The path-formatted string
     * @return array    The segments array
     * @throws \InvalidArgumentException
     */
    public static function parseAsArray($path)
    {
        if (!static::validate($path, $matches)) {
            throw new InvalidArgumentException("The \$path parameter is not in the correct format.");
        }

        $segments = ($path != '') ? explode(static::SEPARATOR, isset($matches['path']) ? $matches['path'] : array())
            : array();

        return $segments;
    }

    /**
     * Tests if the given path string is valid (using the regex). It can additionally return the named capturing
     * group(s) using the $matches parameter as a reference.
     *
     * @static
     * @param string        $path       The path to test for validity
     * @param array|null    $matches    The named capturing groups from the match
     * @return bool     Whether the given path is valid
     */
    public static function validate($path, &$matches = null)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException("The \$path parameter must be a string.");
        }

        return (bool)preg_match(static::REGEX, $path, $matches);
    }

    /**
     * Loads a new segments array. It replaces the current segments with the new ones.
     *
     * @param array $segments   The new segments
     * @throws \InvalidArgumentException
     */
    public function load(array $segments)
    {
        $this->_segments = array();
        $this->add($segments);
    }

    /**
     * Sets an existing segment to the given value.
     *
     * @param int       $index      The segment index
     * @param string    $segment    The segment value
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function set($index, $segment)
    {
        if (!$this->has($index)) {
            throw new OutOfBoundsException(sprintf("Index %s does not exist.", $index));
        }

        if (!is_string($segment)) {
            throw new InvalidArgumentException("The \$segment parameter must be a string.");
        }

        $this->_segments[$index] = $segment;
    }

    /**
     * Gets the segments array or a single segment
     *
     * @param null|int  $index      The wanted segment index or null to get all segments
     * @return array|string     The path segments or wanted segment
     */
    public function get($index = null)
    {
        if ($index === null) {
            return $this->_segments;
        }

        if (!$this->has($index)) {
            throw new OutOfBoundsException(sprintf("Index %s does not exist.", $index));
        }

        return $this->_segments[$index];
    }

    /**
     * Checks if the given index exists.
     *
     * @param int   $index      The index to check
     * @return bool     Whether the index exists
     */
    public function has($index)
    {
        return array_key_exists($index, $this->_segments);
    }

    /**
     * Adds a new segment (or multiple segments) to the end of the segments array.
     *
     * @param string|array  $segment      The new segment(s)
     */
    public function add($segment)
    {
        if (!($is_string = is_string($segment)) and !is_array($segment)) {
            throw new InvalidArgumentException("The \$segment parameter must be a string or an array of strings.");
        }

        $segments = $is_string ? static::parse($segment) : $segment;
        foreach ($segments as $new_segment) {
            if (!is_string($new_segment)) {
                throw new InvalidArgumentException("The new segment must be a string.");
            }

            if (Str::contains($new_segment, static::SEPARATOR)) {
                $this->add($new_segment);
            } else {
                $this->_segments[] = $new_segment;
            }
        }
    }

    /**
     * Removes a segment. The segments array re-indexes.
     *
     * @param int   $index      The segment to remove
     * @throws \OutOfBoundsException
     */
    public function remove($index)
    {
        if (!$this->has($index)) {
            throw new OutOfBoundsException(sprintf("Index %s does not exist.", $index));
        }

        unset($this->_segments[$index]);
        $this->_segments = array_Values($this->_segments);
    }

    //<editor-fold desc="Countable Interface">
    /**
     * Implements Countable interface
     *
     * @see     http://fr.php.net/manual/en/class.countable.php     The Countable interface
     * @return  int
     */
    public function count()
    {
        return count($this->_segments);
    }

    //</editor-fold>

    //<editor-fold desc="ArrayAccess Interface">
    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        $this->has($offset);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @param   mixed   $value
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  void
     */
    public function offsetUnset($offset)
    {
       $this->remove($offset);
    }

    //</editor-fold>

    //<editor-fold desc="IteratorAggregate Interface">
    /**
     * Implements IteratorAggregate Interface
     *
     * @see     http://www.php.net/manual/en/class.iteratoraggregate.php     The IteratorAggregate interface
     * @return  mixed
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_segments);
    }

    //</editor-fold>

    /**
     * Builds the valid path-formatted string with the current values.
     *
     * @return string   The path-formatted string
     */
    public function build()
    {
        $path = '';
        foreach ($this->_segments as $segment) {
            $path .= sprintf("%s%s", $segment, static::SEPARATOR);
        }

        return Str::sub($path, 0, -1);
    }

    /**
     * The toString magic function to get a string representation of the object.
     *
     * @return string   The string representation of this object
     */
    public function __toString()
    {
        return $this->build();
    }
}
