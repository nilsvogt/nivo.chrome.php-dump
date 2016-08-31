<?php
namespace Nivo\Debug;

/*
 * with an nginx server, header capabilities are quite limited in size. Adapt config
 * on your development environment as shown below:
 *
 * /etc/nginx/nginx.conf
 * http {
    ...
    fastcgi_buffers 4 512k;
    fastcgi_buffer_size 512k;
  }
 */
Class Chrome
{

    /**
     * Append header information if headers have not been sent already
     * @param  object $instance The instance being reflected in the chrome-extension
     * @return bool Success
     */
    public static function dump($instance){

        if(headers_sent()){
            // well headers have already been sent so stop processing here..
            return false;
        }

        // obtain stored data that may have been processed already
        #$headers_list = apache_response_headers(); // TODO: find an alternative that does not depend on apache
        #$payload = $headers_list['X-INHERITANCE-CHAIN'] ? json_decode($headers_list['X-INHERITANCE-CHAIN']) : [];
        $payload = [];

        // appand current instance information
        if(gettype($instance) === 'object'){
            $payload[] = self::processInstance($instance);
        }

        // these data will be obtained by the chrome extension later on from the header
        header('X-INHERITANCE-CHAIN: ' . json_encode($payload));

        return true;
    }

    /**
     * Create a resulting representation of a given instance using reflection
     *
     * The representation object will be passed to the corresponding Chrome-Extension
     * within the http headers.
     *
     * @param  object $instance The instance being reflected
     * @return void
     */
    protected static function processInstance($instance){

        $class = new \ReflectionClass($instance);

        $methods = $class->getMethods();

        $parents = array();

        do{
            $parents[$class->getName()] = [
                'interfaces' => $class->getInterfaces(),
                'properties' => []
            ];

            // obtain all properties of the current instance
            foreach ($class->getProperties() as $property) {
                if(is_null($property)) continue;
                $parents[$class->getName()]['properties'][] = self::getPropertyInfo($property, $instance);
            }
        }while($class = $class->getParentClass());


        $payload = [
            "summary" => $parents,
            "classes" => []
        ];

        $class_prev = null;
        $current = null;
        foreach ($methods as $method) {
            if($method->class !== $class_prev){
                if($current) $payload['classes'][] = $current;

                $current = [
                    'classname' => $method->class,
                    'interfaces' => $parents[$method->class]['interfaces'],
                    'properties' => $parents[$method->class]['properties'],
                    'methods' => []
                ];

                $class_prev = $method->class;
            }
            $current['methods'][] = self::getMethodInfo($method);
        }
        $payload['classes'][] = $current;


        return $payload;
    }

    /**
     * Obtain useful information about a given method
     *
     * @param  ReflectionMethod $method The method of interest
     * @return array Array containing all necessary metadata
     */
    protected static function getMethodInfo($method){
        $info = [];
        $info['visibility'] = self::getMethodVisibility($method);
        $info['comment'] = $method->getDocComment();
        $info['name'] = $method->name;
        $info['params'] = [];

        $params = $method->getParameters();
        $parsed_params = [];
        foreach ($params as $param) {
            preg_match('|(Parameter #\d+ \[ <)(?<required>\w+)(> )?(?<type>.*)(?<name>\$\w+) (.*)|', $param, $matches);
            $matches = ['required' => $matches['required'], 'type' => $matches['type'], 'name' => $matches['name']];
            $info['params'][] = $matches;
        }

        return $info;
    }

    /**
     * Obtain useful information about a given property
     *
     * @param  ReflectionProperty $property The property of interest
     * @return array Array containing all necessary metadata
     */
    protected static function getPropertyInfo(\ReflectionProperty $property, $instance)
    {
        $info = [];

        $property->setAccessible(true);
        $info['visibility'] = self::getPropertyVisibility($property);
        $info['comment'] = $property->getDocComment();
        $info['name'] = $property->name;
        $info['value'] = $property->getValue($instance);

        return $info;
    }

    /**
     * Get an array containing the applied visibility of a passed method
     * @param  ReflectionMethod $method The method of interest
     * @return array
     */
    protected static function getMethodVisibility($method){
        $visibility = [];
        !$method->isAbstract()  ?: $visibility[] = 'abstract';
        !$method->isFinal()     ?: $visibility[] = 'final';
        !$method->isPublic()    ?: $visibility[] = 'public';
        !$method->isProtected() ?: $visibility[] = 'protected';
        !$method->isPrivate()   ?: $visibility[] = 'private';
        !$method->isStatic()    ?: $visibility[] = 'static';
        return $visibility;
    }

    /**
     * Get an array containing the applied visibility of a passed property
     * @param  ReflectionProperty $property The property of interest
     * @return array
     */
    protected static function getPropertyVisibility($property){
        $visibility = [];
        !$property->isDefault()   ?: $visibility[] = 'default';
        !$property->isPublic()    ?: $visibility[] = 'public';
        !$property->isProtected() ?: $visibility[] = 'protected';
        !$property->isPrivate()   ?: $visibility[] = 'private';
        !$property->isStatic()    ?: $visibility[] = 'static';
        return $visibility;
    }
}
