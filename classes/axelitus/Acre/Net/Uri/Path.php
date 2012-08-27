<?php
/**
 * Part of the axelitus\Acre\Net\Uri Package.
 *
 * @package     axelitus\Acre\Net\Uri
 * @version     0.1
 * @author      Axel Pardemann (dev@axelitus.mx)
 * @license     MIT License
 * @copyright   2012 - Axel Pardemann
 * @link        http://axelitus.mx/
 */

namespace axelitus\Acre\Net\Uri;

use InvalidArgumentException;
use Countable;
use ArrayAccess;
use Iterator;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Magic_Object as MagicObject;
use axelitus\Acre\Common\Str as Str;

/**
 * Path Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Path extends MagicObject implements Countable, ArrayAccess, Iterator
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
     * @param array  $path   Ann array of strings containing the path segments
     */
    protected function __construct(array $path)
    {
        $this->setSegments($path);
    }

    /**
     * Forges a new instance of the Path class.
     *
     * @static
     * @param string|array  $path   A path-formatted string or an array of strings containing the path segments
     * @return Path     The new instance
     * @throws \InvalidArgumentException
     */
    public static function forge($path)
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
        if (!static::validate($path, $matches)) {
            throw new InvalidArgumentException("The \$path parameter is not in the correct format.");
        }

        if ($path != '') {
            $segments = explode(static::SEPARATOR, isset($matches['path']) ? $matches['path'] : array());
        }

        return static::forge($segments);
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
     * Segments setter.  It replaces the segments array contents with the given array.
     *
     * @param array $segments   The new segments
     * @throws \InvalidArgumentException
     */
    public function setSegments(array $segments)
    {
        $this->_segments = array();
        foreach ($segments as $segment) {
            $this->addSegment($segment);
        }
    }

    /**
     * Segments getter.
     *
     * @return array    The path segments
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * Adds a segment to the segments array.
     *
     * @param $segment      The new segment
     */
    public function addSegment($segment)
    {
        if (!is_string($segment) or Str::contains($segment, static::SEPARATOR)) {
            throw new InvalidArgumentException("The \$segment parameter must be a string and cannot contain the separator character '".static::SEPARATOR."'.");
        }

        $this->_segments[] = $segment;
    }

    //<editor-fold desc="Countable Interface">
    /**
     * Implements Countable interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
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
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @param   string|int  $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_segments);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @param   string|int  $offset
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->_segments[$offset];
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @param   string|int  $offset
     * @param   mixed       $value
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        $this->_segments[$offset] = $value;
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @param   string|int  $offset
     * @return  void
     */
    public function offsetUnset($offset)
    {
        unset($this->_segments[$offset]);
    }

    //</editor-fold>

    //<editor-fold desc="Iterator Interface">
    /**
     * Implements Iterator Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @return  mixed
     */
    public function current()
    {
        return current($this->_segments);
    }

    /**
     * Implements Iterator Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @return  mixed
     */
    public function key()
    {
        return key($this->_segments);
    }

    /**
     * Implements Iterator Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @return  void
     */
    public function next()
    {
        next($this->_segments);
    }

    /**
     * Implements Iterator Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @return  mixed
     */
    public function rewind()
    {
        return reset($this->_segments);
    }

    /**
     * Implements Iterator Interface
     *
     * @author  FuelPHP (http://fuelphp.com)
     * @see     FuelPHP Kernel Package (http://packagist.org/packages/fuel/core)
     * @return  bool
     */
    public function valid()
    {
        return !is_null($this->key());
    }

    //</editor-fold>

    /**
     * Builds the full path-formatted string with the current values.
     *
     * @return string   The path-formatted string
     */
    protected function build()
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
