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
    const REGEX = "/^(?#scheme)(?:(?P<scheme>[A-Za-z][A-Za-z0-9+\-.]*):)?(?:(?#authority)//(?#userinfo)(?:(?P<userinfo>.+)@)?(?#host)(?P<host>(?:(?#named|IPv4)[A-Za-z0-9\-._~%]+|(?#IPv6)\[[A-Fa-f0-9:.]+\]|(?#IPvFuture)\[v[A-Fa-f0-9][A-Za-z0-9\-._~%!$&'()*+,;=:]+\])?)(?#port)(?::(?P<port>[0-9]+))?)?(?#path)(?:/?(?P<path>(?:[A-Za-z0-9\-._~%!$&'()*+,;=@]+/?)*))?(?#query)(?:\?(?P<query>[A-Za-z0-9\-._~%!$&'()*+,;=:@/?]*))?(?#fragment)(?:\#(?P<fragment>[A-Za-z0-9\-._~%!$&'()*+,;=:@/?]*))?$/";

    protected $_scheme = '';
    protected $_authority = null;
    protected $_path = null;
    protected $_query = null;
    protected $_fragment = '';

    public function parse($uri)
    {

    }

    public static function validate($uri)
    {

    }

    public function isValid()
    {

    }
}
