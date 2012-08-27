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
use axelitus\Acre\Net\Uri\Path as Path;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Magic_Object as MagicObject;
use axelitus\Acre\Common\Str as Str;

/**
 * Uri Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Uri extends MagicObject
{
    const SCHEME_SEPARATOR = ':';
    const FRAGMENT_SEPARATOR = '#';
    const REGEX = <<<REGEX
/^(?#scheme)(?:(?P<scheme>[A-Za-z][A-Za-z0-9+\-.]*):)?
(?:(?#authority)\/\/(?P<authority>
  (?#userinfo)(?:(?P<userinfo>.+)@)?
  (?#host)(?P<host>(?:(?#named|IPv4)[A-Za-z0-9\-._~%]+|(?#IPv6)\[[A-Fa-f0-9:.]+\]|(?#IPvFuture)\[v[A-Fa-f0-9][A-Za-z0-9\-._~%!$&\'()*+,;=:]+\])?)
  (?#port)(?::(?P<port>[0-9]+))?
(?::.+)?))?
(?#path)(?:(?P<path>\/?(?:[A-Za-z0-9\-._~%!$&\'()*+,;=@]+\/?)*))?
(?#query)(?:\??(?P<query>(?:[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)(?:&[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)*))?
(?#fragment)(?:\#(?P<fragment>[A-Za-z0-9\-._~%!$&\'()*+,;=:@\/?]*))?$/x
REGEX;

    protected $_scheme = '';
    protected $_authority = null;
    protected $_path = null;
    protected $_query = null;
    protected $_fragment = '';

    /**
     * @var null    Virtual property not actually used
     */
    protected $_components = null;

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array  $components    An associative array containing the individual parts of the URI in their
     *                              corresponding object or scalar types.
     */
    protected function __construct(array $components)
    {
        $this->_scheme = $components['scheme'];
        $this->_authority = $components['authority'];
        $this->_path = $components['path'];
        $this->_query = $components['query'];
        $this->_fragment = $components['fragment'];
    }

    /**
     * Forges a new instance of the Uri class.
     *
     * @static
     * @param string|array $components      An associative array containing the individual parts of the URI for
     *                                      initialization. The parts will be parsed accordingly or they can contain
     *                                      the corresponding objects.
     * @return Uri
     * @throws \InvalidArgumentException
     */
    public static function forge($components = '')
    {
        if (is_string($components)) {
            return static::parse($components);
        } elseif (is_array($components)) {
            // Complete missing array entries
            $components['scheme'] = (isset($components['scheme']) ? $components['scheme'] : '');
            $components['authority'] = (isset($components['authority']) ? $components['authority']
                : Authority::forge());
            $components['path'] = (isset($components['path']) ? $components['path'] : Path::forge());
            $components['query'] = (isset($components['query']) ? $components['query'] : Query::forge());
            $components['fragment'] = (isset($components['fragment']) ? $components['fragment'] : '');

            // Validate entries
            if (!is_string($components['scheme'])) {
                throw new InvalidArgumentException("The scheme components must be a string.");
            }

            if (!$components['authority'] instanceof Authority) {
                if (!is_string($components['authority']) and !is_array($components['authority'])) {
                    throw new InvalidArgumentException("The authority components must be a string, an array or an Authority object.");
                }

                $components['authority'] = Authority::forge($components['authority']);
            }

            if (!$components['path'] instanceof Path) {
                if (!is_string($components['path']) and !is_array($components['path'])) {
                    throw new InvalidArgumentException("The path components must be a string, an array or an Path object.");
                }

                $components['path'] = Path::forge($components['path']);
            }

            if (!$components['query'] instanceof Query) {
                if (!is_string($components['query']) and !is_array($components['query'])) {
                    throw new InvalidArgumentException("The query components must be a string, an array or an Query object.");
                }

                $components['query'] = Query::forge($components['query']);
            }

            if (!is_string($components['fragment'])) {
                throw new InvalidArgumentException("The fragment components must be a string.");
            }

            return new static($components);
        } else {
            throw new InvalidArgumentException("The \$components parameter must be a string or an array.");
        }
    }

    /**
     * Parses an URI if valid and return a components array.
     *
     * @static
     * @param $uri      The URI to parse
     * @return Uri
     * @throws \InvalidArgumentException
     */
    public static function parse($uri)
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException("The \$uri parameter must be a string.");
        }

        if (!Uri::validate($uri, $matches)) {
            throw new InvalidArgumentException("The given URI is not a valid formatted URI.");
        }

        $components = array(
            'scheme'    => isset($matches['scheme']) ? $matches['scheme'] : '',
            'authority' => isset($matches['authority']) ? $matches['authority'] : array(),
            'path'      => isset($matches['path']) ? $matches['path'] : array(),
            'query'     => isset($matches['query']) ? $matches['query'] : array(),
            'fragment'  => isset($matches['fragment']) ? $matches['fragment'] : ''
        );

        return static::forge($components);
    }

    /**
     * Tests if the given URI is valid (using the regex). It can additionally return the named capturing group(s)
     * using the $matches parameter as a reference.
     *
     * @param string        $uri          The URI to test for validity
     * @param array|null    $matches      The named capturing groups from the match
     * @return bool     Whether the given URI is valid
     */
    public static function validate($uri, &$matches)
    {
        return (bool)preg_match(static::REGEX, $uri, $matches);
    }

    /**
     * Scheme getter.
     *
     * @return string   The scheme value
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Authority getter.
     *
     * @param bool $asString    Whether to return the object as a string
     * @return string|Authority     The Authority object or its string representation
     */
    public function getAuthority($asString = true)
    {
        return ($asString) ? (string)$this->_authority : $this->_authority;
    }

    /**
     * Path getter.
     *
     * @param bool $asString    Whether to return the object as a string
     * @return string|Path      The Path object or its string representation
     */
    public function getPath($asString = true)
    {
        return ($asString) ? (string)$this->_path : $this->_path;
    }

    /**
     * Query getter.
     *
     * @param bool $asString    Whether to return the object as a string
     * @return string|Query     The Query object or its string representation
     */
    public function getQuery($asString = true)
    {
        return ($asString) ? (string)$this->_query : $this->_query;
    }

    /**
     * Scheme getter.
     *
     * @return string   The fragment value
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * Gets an associative array with the URI components.
     *
     * @param bool $objAsStrings    Whether to return the objects or their string representations
     * @return array    The associative array of components
     */
    public function getComponents($objAsStrings = true)
    {
        //var_dump($this); exit;
        return array(
            'scheme'    => $this->_scheme,
            'authority' => $this->getAuthority($objAsStrings),
            'path'      => $this->getPath($objAsStrings),
            'query'     => $this->getQuery($objAsStrings),
            'fragment'  => $this->_fragment
        );
    }

    /**
     * Builds the valid URI string with the current components.
     *
     * @return string   The URI string
     */
    protected function build()
    {
        $uri = $this->_scheme;
        $uri .= ($uri != '' ? static::SCHEME_SEPARATOR : '').(string)$this->_authority;

        $path = (string)$this->_path;
        $uri .= (($uri != '' and !Str::beginsWith($path, Path::SEPARATOR)) ? Path::SEPARATOR : '').$path;

        $uri .= (string)$this->_query;
        $uri .= $this->_fragment != '' ? '#'.$this->_fragment : '';

        return $uri;
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
