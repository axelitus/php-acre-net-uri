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
class Authority extends MagicObject
{
    const USERINFO_SEPARATOR = '@';
    const PORT_SEPARATOR = ':';
    const REGEX = <<<REGEX
/^(?:(?#authority)(?:\/\/)?
  (?#userinfo)(?:(?P<userinfo>.+)@)?
  (?#host)(?P<host>(?:(?#named|IPv4)[A-Za-z0-9\-._~%]+|(?#IPv6)\[[A-Fa-f0-9:.]+\]|(?#IPvFuture)\[v[A-Fa-f0-9][A-Za-z0-9\-._~%!$&\'()*+,;=:]+\])?)
  (?#port)(?::(?P<port>[0-9]+))?
)?(?:\/|\?|\#|$)/x
REGEX;


    protected $_userinfo = '';
    protected $_host = '';
    protected $_port = 0;

    protected function __construct($components)
    {
        foreach ($components as $component => $value) {
            $this->{$component} = $value;
        }
    }

    public static function forge($host = '', $port = 0, $userinfo = '')
    {
        if (is_array($host)) {
            return new static($host);
        } else {
            return new static(array('userinfo' => $userinfo, 'host' => $host, 'port' => $port));
        }
    }

    public static function parse($authority)
    {
        if (!is_string($authority)) {
            throw new InvalidArgumentException("The \$authority parameter must be a string.");
        }

        if (preg_match(static::REGEX, $authority, $matches) != 1) {
            throw new InvalidArgumentException("The \$authority parameter is not in the correct format.");
        }

        $components = array(
            'userinfo' => $matches['userinfo'],
            'host'     => $matches['host'],
            'port'     => (int)$matches['port'],
        );

        return static::Forge($components);
    }

    public function setUserinfo($userinfo)
    {
        if (!is_string($userinfo)) {
            throw new InvalidArgumentException("The \$userinfo parameter must be a string.");
        }

        $this->_userinfo = $userinfo;

        return $this;
    }

    public function getUserinfo()
    {
        return $this->_userinfo;
    }

    public function setHost($host)
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException("The \$host parameter must be a string.");
        }

        $this->_host = $host;

        return $this;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setPort($port)
    {
        if (!Num::isInt($port)) {
            throw new InvalidArgumentException("The \$port parameter must be an integer.");
        }

        $this->_port = $port;

        return $this;
    }

    public function getPort()
    {
        return $this->_port;
    }

    protected function build()
    {
        $userinfo = $this->_userinfo.(($this->_userinfo != '') ? static::USERINFO_SEPARATOR : '');
        $port = $this->_port > 0 ? static::PORT_SEPARATOR.$this->_port : '';

        $authority = sprintf("%s%s%s", $userinfo, $this->_host, $port);

        return ($authority != '') ? '//'.$authority : $authority;
    }

    public function __toString()
    {
        return $this->build();
    }
}
