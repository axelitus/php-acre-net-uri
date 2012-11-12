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
 * Query Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
final class Query implements Countable, ArrayAccess, IteratorAggregate
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
/^(?#query)(?:\??(?<query>(?:[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)(?:&[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)*))?/x
REGEX;

    /**
     * @var array   The key/value pairs associative array
     */
    protected $pairs = array();

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array  $query   An associative array of key/value query pairs
     */
    protected function __construct(array $query)
    {
        $this->load($query);
    }

    /**
     * Forges a new instance of the Query class.
     *
     * @static
     * @param string|array  $query     A query-formatted string or an array of key/value query pairs
     * @return Query     The new instance
     * @throws \InvalidArgumentException
     */
    public static function forge($query = array())
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
     * @param string    $query      The query-formatted string
     * @param bool      $urldecode  Whether to decode the key-value pairs with urldecode
     * @return Authority    The new object
     * @throws \InvalidArgumentException
     */
    public static function parse($query, $urldecode = true)
    {
        return static::forge(static::parseAsArray($query, $urldecode));
    }

    /**
     * Parses the query formatted string into an array.
     *
     * @param string    $query      The query-formatted string
     * @param bool      $urldecode  Whether to decode the key-value pairs with urldecode
     * @return array    The parsed array
     * @throws \InvalidArgumentException
     */
    public static function parseAsArray($query, $urldecode = true)
    {
        if (!static::validate($query, $matches)) {
            throw new InvalidArgumentException("The \$query parameter is not in the correct format.");
        }

        $assoc = array();
        if ($query != '') {
            array_map(function ($query) use (&$assoc, $urldecode) {
                list($key, $value) = explode(Query::VALUE_SEPARATOR, $query) + array(null, null);

                if ($urldecode) {
                    $key = urldecode($key);
                    $value = urldecode(($value));
                }

                $assoc[$key] = $value;

                return array($key => $value);
            }, explode(static::PAIR_SEPARATOR, isset($matches['query']) ? $matches['query'] : array()));
        }

        return $assoc;
    }

    /**
     * Tests if the given query string is valid (using the regex). It can additionally return the named capturing
     * group(s) using the $matches parameter as a reference.
     *
     * @static
     * @param string        $query      The query to test for validity
     * @param array|null    $matches    The named capturing groups from the match
     * @return bool     Whether the given query is valid
     * @throws InvalidArgumentException
     */
    public static function validate($query, &$matches = null)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException("The \$path parameter must be a string.");
        }

        return (bool)preg_match(static::REGEX, $query, $matches);
    }

    /**
     * Loads a new pairs array. It replaces the current pairs with the new ones.
     *
     * @param array $pairs      The new pairs associative array
     */
    public function load(array $pairs)
    {
        $this->pairs = array();
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Sets an existing key to the given value.
     *
     * @param string    $key        The pair key
     * @param string    $value      The pair value
     * @param bool      $urldecode  Whether to decode the key-value pairs with urldecode
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function set($key, $value, $urldecode = true)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("The \$key parameter must be a string.");
        }

        if ($urldecode) {
            $key = urldecode($key);
            $value = urldecode($value);
        }

        $this->pairs[$key] = ($value !== null) ? $value : '';
    }

    /**
     * Gets the pairs array or a single pair
     *
     * @param null|string   $key        The wanted pair key or null to get all pairs
     * @param bool          $urlencode  Whether to encode the key-value pairs with urlencode
     * @return array|string     The pairs associative array or wanted pair
     * @throws OutOfBoundsException
     */
    public function get($key = null, $urlencode = true)
    {
        if ($key === null) {
            return $this->pairs;
        }

        if (!$this->has($key)) {
            throw new OutOfBoundsException(sprintf("Key %s does not exist.", $key));
        }

        return ($urlencode) ? urlencode($this->pairs[urldecode($key)]) : $this->pairs[$key];
    }

    /**
     * Checks if the given key exists.
     *
     * @param string    $key    The key to check
     * @return bool     Whether the key exists
     */
    public function has($key)
    {
        return array_key_exists($key, $this->pairs);
    }

    /**
     * Removes a pair.
     *
     * @param string    $key    The pair key to remove
     * @throws \OutOfBoundsException
     */
    public function remove($key)
    {
        if (!$this->has($key)) {
            throw new OutOfBoundsException(sprintf("Key %s does not exist.", $key));
        }

        unset($this->pairs[$key]);
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
        return count($this->pairs);
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
        return $this->has($offset);
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
     * @return  \Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->pairs);
    }

    //</editor-fold>

    /**
     * Builds the valid query-formatted string with the current values.
     *
     * @param bool  $urlencode  Whether to encode the key-value pairs with urlencode
     * @return string   The query-formatted string
     */
    public function build($urlencode = true)
    {
        $query = '';
        foreach ($this->pairs as $key => $value) {
            if ($urlencode) {
                $key = urlencode($key);
                $value = urlencode($value);
            }
            $query .= sprintf("%s%s%s%s", $key, static::VALUE_SEPARATOR, $value, static::PAIR_SEPARATOR);
        }

        // Strip the last appended PAIR_SEPARATOR
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
