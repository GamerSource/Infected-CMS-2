<?php

    /**
     * Dependencies
     */
    require_once ICMS_SYS_PATH . 'lib/database/databaseexception.php';
    require_once ICMS_SYS_PATH . 'lib/database/idatabaseadapter.php';
    require_once ICMS_SYS_PATH . 'lib/database/idatabaseresult.php';
    require_once ICMS_SYS_PATH . 'lib/database/idatabasequery.php';

    /**
     * 
     */
    abstract class Database
    {
        private static $adapterInstances = array();
        
        public static function &factory($pattern)
        {
            if (isset(self::$adapterInstances[$pattern]))
            {
                return self::$adapterInstances[$pattern];
            }
            $parsed_pattern = @parse_url($pattern);
            if ($parsed_pattern ===  false)
            {
                throw new DatabaseException('database connection pattern is invalid!', 401);
            }
            if (!isset($parsed_pattern['scheme']))
            {
                throw new DatabaseException('no database adapter given!', 402);
            }
            $adapter =& $parsed_pattern['scheme'];
            $adapter_path = dirname(__FILE__) . DIRECTORY_SEPARATOR .
                            'adapters' . DIRECTORY_SEPARATOR .
                            $adapter . DIRECTORY_SEPARATOR .
                            'adapter.php';
            if (!file_exists($adapter_path))
            {
                throw new DatabaseException('database adapter ' . $adapter . ' not found!', 404);
            }

            require_once $adapter_path;
            $adapter .= 'Adapter';

            if (!class_exists($adapter))
            {
                throw new DatabaseException('the given database adapter is invalid', 403);
            }

            if (in_array('IDatabaseAdapter', class_implements($adapter)) !== true)
            {
                throw new DatabaseException('the given database adapter is invalid', 403);
            }

            if (!$adapter::validate($parsed_pattern))
            {
                throw new DatabaseException('the pattern validation failed for the fiven adapter!', 405);
            }
            $instance = new $adapter($parsed_pattern);
            self::$adapterInstances[$pattern] = $instance;
            
            return self::$adapterInstances[$pattern];
        }
    }
?>
