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

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Magic_Object as MagicObject;
use axelitus\Acre\Common\Num as Num;

/**
 * Authority Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
final class Authority extends MagicObject
{
    /**
     * @var string      The userinfo segment separator
     */
    const USERINFO_SEPARATOR = '@';

    /**
     * @var string      The port segment separator
     */
    const PORT_SEPARATOR = ':';

    /**
     * @var string      The name capturing regex pattern to parse an authority string
     */
    const REGEX = <<<REGEX
/^(?:(?#authority)(?:\/\/)?
  (?#userinfo)(?:(?P<userinfo>.+)@)?
  (?#host)(?P<host>(?:(?#named|IPv4)[A-Za-z0-9\-._~%]+|(?#IPv6)\[[A-Fa-f0-9:.]+\]|(?#IPvFuture)\[v[A-Fa-f0-9][A-Za-z0-9\-._~%!$&\'()*+,;=:]+\])?)
  (?#port)(?::(?P<port>[0-9]+))?
)?(?:\/|\?|\#|$)/x
REGEX;

    /**
     * @var string      The userinfo segment
     */
    protected $_userinfo = '';

    /**
     * @var string      The host segment
     */
    protected $_host = '';

    /**
     * @var int         The port segment (as an integer)
     */
    protected $_port = 0;

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array $components     An associative array containing zero or more components (userinfo, host and/or port)
     *                              Other unknown entries in the array will be ignored.
     */
    protected function __construct(array $components)
    {
        foreach ($components as $component => $value) {
            if ($this->hasProperty($component) and $this->hasPropertySetter($component)) {
                $this->{$component} = $value;
            }
        }
    }

    /**
     * Forges a new instance of the Authority class.
     *
     * @static
     * @param string $host      The authority host segment value
     * @param int    $port      The authority port segment value
     * @param string $userinfo  The authority userinfo segment value
     * @return Authority    The new Authority object
     */
    public static function forge($host = '', $port = 0, $userinfo = '')
    {
        if (is_array($host)) {
            return new static($host);
        } else {
            return new static(array('userinfo' => $userinfo, 'host' => $host, 'port' => $port));
        }
    }

    /**
     * Parses an authority formatted string into an Authority object.
     *
     * @static
     * @param string    $authority    The authority-formatted string
     * @return Authority    The new object
     * @throws \InvalidArgumentException
     */
    public static function parse($authority)
    {
        if (!static::validate($authority, $matches)) {
            throw new InvalidArgumentException("The \$authority parameter is not in the correct format.");
        }

        $components = array(
            'userinfo' => isset($matches['userinfo']) ? $matches['userinfo'] : '',
            'host'     => isset($matches['host']) ? $matches['host'] : '',
            'port'     => isset($matches['port']) ? (int)$matches['port'] : 0
        );

        return static::forge($components);
    }

    /**
     * Tests if the given authority string is valid (using the regex). It can additionally return the named capturing
     * group(s) using the $matches parameter as a reference.
     *
     * @static
     * @param string        $authority  The path to test for validity
     * @param array|null    $matches    The named capturing groups from the match
     * @return bool     Whether the given path is valid
     */
    public static function validate($authority, &$matches = null)
    {
        if (!is_string($authority)) {
            throw new InvalidArgumentException("The \$authority parameter must be a string.");
        }

        return (bool)preg_match(static::REGEX, $authority, $matches);
    }

    /**
     * Userinfo segment setter.
     * The new value is not format-validated.
     *
     * @param string $userinfo     The userinfo segment value to set
     * @return Authority    This instance for chaining
     * @throws \InvalidArgumentException
     */
    public function setUserinfo($userinfo)
    {
        if (!is_string($userinfo)) {
            throw new InvalidArgumentException("The \$userinfo parameter must be a string.");
        }

        $this->_userinfo = $userinfo;

        return $this;
    }

    /**
     * Userinfo segment getter.
     *
     * @return string   The userinfo segment value
     */
    public function getUserinfo()
    {
        return $this->_userinfo;
    }

    /**
     * Host segment setter.
     * The new value is not format-validated.
     *
     * @param string    $host   The host segment value to set
     * @return Authority    This instance for chaining
     * @throws \InvalidArgumentException
     */
    public function setHost($host)
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException("The \$host parameter must be a string.");
        }

        $this->_host = $host;

        return $this;
    }

    /**
     * Host segment getter.
     *
     * @return string   The host segment value
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Port segment setter.
     *
     * @param int $port    The port segment value to set
     * @return Authority    This instance for chaining
     * @throws \InvalidArgumentException
     */
    public function setPort($port)
    {
        if (!Num::isInt($port)) {
            throw new InvalidArgumentException("The \$port parameter must be an integer.");
        }

        $this->_port = $port;

        return $this;
    }

    /**
     * Port segment getter.
     *
     * @return int      The port segment value
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Builds the full authority-formatted string with the current values.
     *
     * @return string   The authority-formatted string
     */
    protected function build()
    {
        $userinfo = $this->_userinfo.(($this->_userinfo != '') ? static::USERINFO_SEPARATOR : '');
        $port = $this->_port > 0 ? static::PORT_SEPARATOR.$this->_port : '';

        $authority = sprintf("%s%s%s", $userinfo, $this->_host, $port);

        return ($authority != '') ? '//'.$authority : $authority;
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
