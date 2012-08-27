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
use Countable;
use ArrayAccess;
use Iterator;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Magic_Object as MagicObject;
use axelitus\Acre\Common\Str as Str;

/**
 * Query Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Query extends MagicObject implements Countable, ArrayAccess, Iterator
{
    /**
     * @var string      The key/value pairs separator
     */
    const PAIR_SEPARATOR = '&';

    /**
     * @var string      The key/value separator
     */
    const VALUE_SEPARATOR = '=';

    /**
     * @var string      The name capturing regex pattern to parse a query string
     */
    const REGEX = <<<REGEX
/^(?#query)(?:\??(?P<query>(?:[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)(?:&[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)*))?/x
REGEX;

    /**
     * @var array   The key/value pairs associative array
     */
    protected $_pairs = array();

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array  $query   An associative array of key/value query pairs
     */
    protected function __construct(array $query)
    {
        $this->setPairs($query);
    }

    /**
     * Forges a new instance of the Query class.
     *
     * @static
     * @param string|array  $path$query     A query-formatted string or an array of key/value query pairs
     * @return Query     The new instance
     * @throws \InvalidArgumentException
     */
    public static function forge($query)
    {
        if (is_string($query)) {
            return static::parse($query);
        } elseif (is_array($query)) {
            return new static($query);
        } else {
            throw new InvalidArgumentException("The \$query parameter must be a string or an associative array of strings.");
        }
    }

    /**
     * Parses a query formatted string into a Query object.
     *
     * @static
     * @param string    $query  The query-formatted string
     * @return Authority    The new object
     * @throws \InvalidArgumentException
     */
    public static function parse($query)
    {
        if (!static::validate($query, $matches)) {
            throw new InvalidArgumentException("The \$query parameter is not in the correct format.");
        }

        $assoc = array();
        if ($query != '') {
            $queries = array_map(function($query) use (&$assoc)
            {
                list($key, $value) = explode(static::VALUE_SEPARATOR, $query) + array(null, null);
                $assoc[$key] = $value;
                return array($key => $value);
            }, explode(static::PAIR_SEPARATOR, isset($matches['query']) ? $matches['query'] : array()));
        }

        return static::forge($assoc);
    }

    /**
     * Tests if the given query string is valid (using the regex). It can additionally return the named capturing
     * group(s) using the $matches parameter as a reference.
     *
     * @static
     * @param string        $query      The query to test for validity
     * @param array|null    $matches    The named capturing groups from the match
     * @return bool     Whether the given query is valid
     */
    public static function validate($query, &$matches = null)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException("The \$path parameter must be a string.");
        }

        return (bool)preg_match(static::REGEX, $query, $matches);
    }

    /**
     * Pairs setter. It replaces the pairs array contents with the given array.
     *
     * @param array $pairs      The new pairs associative array
     */
    public function setPairs(array $pairs)
    {
        //var_dump($pairs); exit;
        $this->_pairs = array();
        foreach ($pairs as $key => $value) {
            $this->addPair($key, $value);
        }
    }

    /**
     * Pairs getter.
     *
     * @return array    The pairs associative array
     */
    public function getPairs()
    {
        return $this->_pairs;
    }

    /**
     * Adds (or replaces) a key=value pair. Null values are replaced by an empty string.
     *
     * @param $key      The pair's key
     * @param $value    The pair's value
     * @throws \InvalidArgumentException
     */
    public function addPair($key, $value)
    {
        if (!is_string($key) or $key == '') {
            throw new InvalidArgumentException("The \$key parameter must be a non empty string.");
        }

        $this->_pairs[$key] = ($value !== null) ? $value : '';
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
        return count($this->_pairs);
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
        return array_key_exists($offset, $this->_pairs);
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
        return $this->_pairs[$offset];
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
        $this->_pairs[$offset] = $value;
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
        unset($this->_pairs[$offset]);
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
        return current($this->_pairs);
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
        return key($this->_pairs);
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
        next($this->_pairs);
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
        return reset($this->_pairs);
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
     * Builds the full query-formatted string with the current values.
     *
     * @return string   The query-formatted string
     */
    protected function build()
    {
        $query = '';
        foreach ($this->_pairs as $item => $value) {
            $query .= sprintf("%s%s%s%s", $item, static::VALUE_SEPARATOR, $value, static::PAIR_SEPARATOR);
        }

        $query = Str::sub($query, 0, -1);

        return (($query != '') ? '?' : '').$query;
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
