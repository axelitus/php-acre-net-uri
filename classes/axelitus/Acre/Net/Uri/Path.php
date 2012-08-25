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

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Str as Str;

use InvalidArgumentException;

/**
 * Path Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Path
{
    /**
     * @var string  The path segments separators
     */
    const SEPARATOR = '/';

    /**
     * @var string  The name capturing regex pattern to parse a path string
     */
    const REGEX = <<<REGEX
/^(?#path)(?:\/?(?P<path>(?:[A-Za-z0-9\-._~%!$&\'()*+,;=@]+\/?)*))?(?:\?|\#|$)/x
REGEX;

    /**
     * @var array   The path segments
     */
    protected $_segments = array();

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param string|array  $path   A path-formatted string or an array of strings containing the path segments
     * @throws \InvalidArgumentException
     */
    protected function __construct($path)
    {
        if(is_string($path)) {
            $this->_segments = explode(static::SEPARATOR, $path);
        } elseif(is_array($path)) {
            foreach($path as $segment) {
                if(!is_string($segment)) {
                    throw new InvalidArgumentException("All path segments must be strings.");
                }

                $this->_segments[] = $segment;
            }
        }
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
        if(!is_string($path) and !is_array($path)) {
            throw new InvalidArgumentException("The \$path parameter must be a string or an array of strings.");
        }

        return new static($path);
    }

    // TODO: Implement ArrayAccess and Iterator

    /**
     * Builds the full path-formatted string with the current values.
     *
     * @return string   The path-formatted string
     */
    protected function build()
    {
        $path = '';
        foreach($this->_segments as $segment) {
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
