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

    protected function __construct(array $components)
    {
        $components = is_string($components) ? static::parse($components) : $components;
        $this->_scheme = $components['scheme'];
        $this->_authority = (is_array($components['authority'])) ? Authority::forge($components['authority'])
            : Authority::parse($components['authority']);
        Authority::parse($components['authority']);
        $this->_path = Path::forge($components['path']);
        $this->_query = Query::forge($components['query']);
        $this->_fragment = $components['fragment'];
    }

    public static function forge($components = '')
    {
        if (is_string($components)) {
            return static::parse($components);
        } elseif (is_array($components)) {
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
            'authority' => isset($matches['authority']) ? $matches['authority'] : '',
            'path'      => isset($matches['path']) ? $matches['path'] : '',
            'query'     => isset($matches['query']) ? $matches['query'] : '',
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

    public function getScheme()
    {
        return $this->_scheme;
    }

    public function getAuthority($asString = true)
    {
        return ($asString) ? (string)$this->_authority : $this->_authority;
    }

    public function getPath($asString = true)
    {
        return ($asString) ? (string)$this->_path : $this->_path;
    }

    public function getQuery($asString = true)
    {
        return ($asString) ? (string)$this->_query : $this->_query;
    }

    public function getFragment()
    {
        return $this->_fragment;
    }

    public function getComponents($objAsStrings = true)
    {
        return array(
            'scheme'    => $this->_scheme,
            'authority' => $this->getAuthority($objAsStrings),
            'path'      => $this->getPath($objAsStrings),
            'query'     => $this->getQuery($objAsStrings),
            'fragment'  => $this->_fragment
        );
    }

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

    public function __toString()
    {
        return $this->build();
    }
}
