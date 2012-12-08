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

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Num as Num;
use axelitus\Acre\Common\Str as Str;
use axelitus\Acre\Common\Magic_Object as MagicObject;

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
  (?#userinfo)(?:(?<userinfo>.+)@)?
  (?#host)(?<host>(?:(?#named|IPv4)[A-Za-z0-9\-._~%]+|(?#IPv6)\[[A-Fa-f0-9:.]+\]|(?#IPvFuture)\[v[A-Fa-f0-9][A-Za-z0-9\-._~%!$&\'()*+,;=:]+\])?)
  (?#port)(?::(?<port>[0-9]+))?
)?(?:\/|\?|\#|$)/x
REGEX;

    /**
     * @var int         The default value of the port when none given
     */
    const DEFAULT_PORT = 80;

    /**
     * @var string      The userinfo segment
     */
    protected $userinfo = '';

    /**
     * @var string      The host segment
     */
    protected $host = '';

    /**
     * @var int         The port segment (as an integer)
     */
    protected $port = 0;

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array $components     An associative array containing zero or more components (userinfo, host and/or port)
     *                              Other unknown entries in the array will be ignored.
     */
    protected function __construct(array $components)
    {
        foreach ($components as $component => $value) {
            if (Str::isOneOf($component, array('host', 'port', 'userinfo'))) {
                $this->{'set'.$component}($value);
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
            'host' => isset($matches['host']) ? $matches['host'] : '',
            'port' => isset($matches['port']) ? (int)$matches['port'] : 0
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
     * @throws InvalidArgumentException
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

        $this->userinfo = $userinfo;

        return $this;
    }

    /**
     * Userinfo segment getter.
     *
     * @return string   The userinfo segment value
     */
    public function getUserinfo()
    {
        return $this->userinfo;
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

        $this->host = $host;

        return $this;
    }

    /**
     * Host segment getter.
     *
     * @return string   The host segment value
     */
    public function getHost()
    {
        return $this->host;
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

        if (!Num::between($port, 0, 65535, Num::RANGE_BOTH_INCLUSIVE)) {
            throw new InvalidArgumentException("Valid \$port parameters are between 0 and 65535.");
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Port segment getter.
     *
     * @return int      The port segment value
     */
    public function getPort($defaultOnNull = true)
    {
        return ($this->port === 0 and $defaultOnNull) ? self::DEFAULT_PORT : $this->port;
    }

    /**
     * Builds the valid authority-formatted string with the current values.
     *
     * @return string   The authority-formatted string
     */
    public function build(array $options = array())
    {
        $options = $this->validateBuildOptions($options);

        $userinfo = (!($exists = array_key_exists('userinfo', $options)) or ($exists and $options['userinfo'] == true))
            ? ($this->userinfo.(($this->userinfo != '')
                ? static::USERINFO_SEPARATOR
                : ''))
            : '';
        $port = (!($exists = array_key_exists('port', $options)) or ($exists and $options['port'] == true))
            ? (($this->getPort(false) > 0)
                ? static::PORT_SEPARATOR.$this->port
                : ((!($exists = array_key_exists('omit_default_port', $options)) or ($exists and $options['omit_default_port'] == true))
                    ? ''
                    : static::PORT_SEPARATOR.static::DEFAULT_PORT))
            : '';

        $authority = sprintf("%s%s%s", $userinfo, $this->host, $port);

        return ($authority != '') ? '//'.$authority : $authority;
    }

    protected function validateBuildOptions(array $options)
    {
        // userinfo
        if($this->userinfo == '') {
            $options['userinfo'] = false;
        }

        // port
        // do nothing

        // omit_default_port
        // do nothing

        return $options;
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
