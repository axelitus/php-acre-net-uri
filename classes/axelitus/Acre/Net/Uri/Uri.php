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
use axelitus\Acre\Net\Uri\Path as Path;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Str as Str;

/**
 * Uri Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Uri
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
(?#path)(?:\/?(?P<path>(?:[A-Za-z0-9\-._~%!$&\'()*+,;=@]+\/?)*))?
(?#query)(?:\??(?P<query>(?:[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)(?:&[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)*))?
(?#fragment)(?:\#(?P<fragment>[A-Za-z0-9\-._~%!$&\'()*+,;=:@\/?]*))?$/x
REGEX;

    protected $_scheme = '';
    protected $_authority = null;
    protected $_path = null;
    protected $_query = null;
    protected $_fragment = '';

    protected function __construct($components = '')
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
        if (!is_string($components) and !is_array($components)) {
            throw new InvalidArgumentException("The \$components parameter must be a string or an array.");
        }

        return new static($components);
    }

    public static function parse($uri)
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException("The \$uri parameter must be a string.");
        }

        preg_match(static::REGEX, $uri, $matches);

        $components = array(
            'scheme'    => $matches['scheme'],
            'authority' => $matches['authority'],
            'path'      => $matches['path'],
            'query'     => $matches['query'],
            'fragment'  => $matches['fragment']
        );

        return $components;
    }

    public static function validate($uri)
    {
        return (bool)preg_match(static::REGEX, $uri);
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

    public function isValid()
    {

    }
}
