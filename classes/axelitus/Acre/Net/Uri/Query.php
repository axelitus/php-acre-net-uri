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
use axelitus\Acre\Common\Str as Str;

/**
 * Query Class
 *
 * @see         http://www.rfc-editor.org/rfc/std/std66.txt     STD 66 - Uniform Resource Identifier (URI): Generic Syntax
 * @package     axelitus\Acre\Net\Uri
 * @category    Net\Uri
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Query
{
    const PAIR_SEPARATOR = '&';
    const VALUE_SEPARATOR = '=';
    const REGEX = <<<REGEX
/^(?#query)(?:\??(?P<query>(?:[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)(?:&[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*(?:=[A-Za-z0-9\-._~%!$\'()*+,;:@\/?]*)?)*))?/x
REGEX;


    protected $_pairs = array();

    protected function __construct($query)
    {
        if (is_string($query)) {
            if(preg_match(static::REGEX, $query, $matches) != 1) {
                throw new InvalidArgumentException("The \$query parameter is not in the correct format.");
            }

            $queries = explode(static::PAIR_SEPARATOR, $matches['query']);
            foreach ($queries as $item) {
                $item = explode(static::VALUE_SEPARATOR, $item);
                $this->_pairs[$item[0]] = isset($item[1]) ? $item[1] : '';
            }
        } elseif (is_array($query)) {
            foreach ($query as $item => $value) {
                if (!is_string($item) or !is_String($value)) {
                    throw new InvalidArgumentException("All the keys and values for the query items must be strings.");
                }

                $this->_pairs[$item] = $value;
            }
        }
    }

    public static function forge($query)
    {
        if (!is_string($query) and !is_array($query)) {
            throw new InvalidArgumentException("The \$query parameter must be a string or an associative array of strings.");
        }

        return new static($query);
    }

    // TODO: Implement ArrayAccess and Iterator

    protected function build()
    {
        $query = '';
        foreach ($this->_pairs as $item => $value) {
            $query .= sprintf("%s%s%s%s", $item, static::VALUE_SEPARATOR, $value, static::PAIR_SEPARATOR);
        }

        $query = Str::sub($query, 0, -1);

        return '?'.$query;
    }

    public function __toString()
    {
        return $this->build();
    }
}
