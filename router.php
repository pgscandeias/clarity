<?php
/**
 * Router
 */
class Router
{
    /**
     * Checks if a url matches a given pattern.
     * If it does, returns a list of arguments.
     */
    public static function matchPattern($pattern, $url) {
        $return = array(
            'match' => false,
            'arguments' => array(),
        );

        if ($url == $pattern) {
            $return['match'] = true;
            return $return;
        }

        // convert URL parameter (e.g. ":id", "*") to regular expression
        $regex = preg_replace('#:([\w]+)#', '(?<\\1>[^/]+)', 
            str_replace(array('*', ')'), array('[^/]+', ')?'), $pattern)
        );
        if (substr($pattern,-1)==='/') $regex .= '?';

        // extract parameter values from URL if route matches the current request
        if (!preg_match('#^'.$regex.'$#', $url, $values)) {
          return;
        }
        // extract parameter names from URL
        preg_match_all('#:([\w]+)#', $pattern, $params, PREG_PATTERN_ORDER);
        $args = array();
        foreach ($params[1] as $param) {
          if (isset($values[$param])) $args[$param] = urldecode($values[$param]);
        }
        
        return array(
            'match' => true,
            'arguments' => $args
        );
    }
}