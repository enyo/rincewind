<?php

/**
 * Calls a static method on an object. Useful for simplifying call_user_func_array
 * This takes advantage of the fact php static objects can exist as instances
 * and uses call_user_func_array to create a static call based on the class
 * This is especially useful when working with mocked static objects, where
 * the name of the class is unknown
 * @param $obj an instance of an object
 **/
function SNAP_callStatic($obj, $method, $params = array()) {
    
    $params = (is_array($params)) ? $params : array($params);
    $obj_name = (is_object($obj)) ? get_class($obj) : strval($obj);
    
    if (!class_exists($obj_name)) {
        throw new Snap_Exception('Static call on '.$obj_name.' when it is not an object.');
    }

    return call_user_func_array(array($obj_name, $method), $params);
}

/**
 * MockObject Base Class
 * Can create mock objects and assign expectations to them
 */
class Snap_MockObject {
    
    protected $mocked_class;
    protected $interface_names;
    protected $has_constructor;
    protected $requires_magic_methods;
    protected $requires_static_methods;
    protected $requires_inheritance;
    protected $use_extends;
    
    protected $methods;
    protected $signatures;
    protected $counters;
    protected $constructed_object;
    
    public $constructor_args;
    public $mock_output;

    /**
    * Begin defining a mock object, and setting its expectations
    * if $test is set, it will be the parent test object (tenative? is this good?)
    * @access public
    * @param string $class_name class name to create a mock of
    * @param UnitTest a unit test object that calls it (tenative)
    */
    public function __construct($class_name) {
        // $this->test = $test;
        $this->requires_inheritance = FALSE;
        $this->requires_magic_methods = FALSE;
        $this->requires_static_methods = FALSE;
        $this->use_extends = FALSE;
        $this->has_constructor = FALSE;
        $this->interface_names = array();
        $this->methods = array();
        $this->signatures = array();
        $this->constructor_args = array();
        $this->counters = array();
        $this->mocked_class = $class_name;
        $this->constructed_object = null;
        
        // do some quick reflection on the class
        $reflected_class = new ReflectionClass($this->mocked_class);
        if ($reflected_class->isInterface()) {
            $this->interface_names[] = $class_name;
        }
        else {
            $this->use_extends = TRUE;
        }
        
        if (count($reflected_class->getInterfaces()) > 0) {
            foreach($reflected_class->getInterfaces() as $k => $interface) {
                $this->interface_names[] = $interface->getName();
            }
        }
    }
    
    /**
     * Specify this mock object requires inheritance (mocks protected methods, copies private)
     * @return MockObject the mock setup object
     */
    public function requiresInheritance() {
        $this->requires_inheritance = TRUE;
        return $this;
    }
    
    /**
     * Get the inheritance required state of the mock object
     * @return boolean TRUE if object requires inheritance
     */
    public function isInherited() {
        return $this->requires_inheritance;
    }
    
    /**
     * Specify this mock object requires a constructor
     * @return MockObject the mock setup object
     **/
    public function requiresConstructor() {
        $this->has_constructor = TRUE;
        return $this;
    }
    
    /**
     * Returns if this mock object has a constructor
     * @return boolean TRUE if object has a constructor
     **/
    public function hasConstructor() {
        return $this->has_constructor;
    }
    
    /**
     * Specify this mock object requires static methods
     * @return MockObject the mock setup object
     **/
    public function requiresStaticMethods() {
        $this->requires_static_methods = TRUE;
        return $this;
    }
    
    /**
     * Returns if the mock object has static methods
     * @return boolean TRUE if object has static methods
     **/
    public function hasStaticMethods() {
        return $this->requires_static_methods;
    }
    
    /**
     * Specify this mock object requires magic methods (has a __call)
     * @return MockObject the mock setup object
     **/
    public function requiresMagicMethods() {
        $this->requires_magic_methods = TRUE;
        return $this;
    }
    
    /**
     * Get the magic method required state of the mock object
     * @return boolean TRUE if the object requires magic methods
     **/
    public function hasMagicMethods() {
        return $this->requires_magic_methods;
    }
    
    /**
     * Specify an interface that this mock object should have
     * @param string $iface the name of an interface to implement
     * @return MockObject the mock setup object
     **/
    public function requiresInterface($iface) {
        $this->interface_names[] = $iface;
        return $this;
    }
    
    /**
     * Get the interfaces for this mock
     * @return array A collection of strings that are all interfaces this mock implements
     **/
    public function getInterfaces() {
        return $this->interface_names;
    }
    
    /**
     * Specify an original class that this is to use / extend
     * @return boolean TRUE if we are mocking a class and therefore need extends
     **/
    public function useExtends() {
        return $this->use_extends;
    }
    
    /**
     * Set the return value for a method call of the specified params
     * @param string $method_name name of method to call
     * @param mixed $return_value the value to return when the method is called
     * @param array $method_params the method parameters to match for this to trigger
     * @return MockObject $this
     */
    public function setReturnValue($method_name, $return_value, $method_params = array()) {
        return $this->setReturnValueAt($method_name, 'default', $return_value, $method_params);
    }
    
    /**
     * Set the return value at a given occurance of $method_name with $method_params
     * @see MockObject::setReturnValue
     * @param int $call_order the order the call is made
     */
    public function setReturnValueAt($method_name, $call_order, $return_value, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        $this->logMethodSignature($method_name, $method_signature, $method_params);
        $this->methods[$method_signature]['returns'][$call_order] = $return_value;
        return $this;
    }
    
    /**
     * Tell the mock object to listen on a given set of params.  This enables tally options
     * any method that has also been tagged with a setReturnValue gets the listener as
     * well.  Use this primarily during setup to prepare to test Expectations
     */
    public function listenTo($method_name, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        $this->logMethodSignature($method_name, $method_signature, $method_params);
        return $this;
    }
    
    /**
     * Generate the method's signature based on its params
     * @param string $method_name the name of the method
     * @param array $method_params the paramters for the method
     */
    public function getMethodSignature($method_name, $method_params) {
        $method_signature = $method_name . ' ' . md5(strtolower(serialize($method_params)));
        return $method_signature;
    }
    
    
    /**
     * Get the tally for a specified method name and signature
     * returns the total times a signature was called
     * @param string $method_name the name of the method to tally
     * @param array $method_params the parameters to check against
     */
    public function getTally($method_name, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        return $this->mockGetTallyCount($method_signature);
    }
    
    /**
     * Builds the mock object using eval, and calls the constructor on it.
     * the class signature is unique to the sum of expectations, so an idential object
     * will share the same class signature for its public methods
     * @return Object
     * @throws Snap_UnitTestException
     */
    public function construct() {
        $mock_class = $this->generateClassName();
        
        $constructor_method_name = $this->class_signature.'_runConstructor';
        $setmock_method_name = $this->class_signature.'_injectMock';
        $this->constructor_args = func_get_args();
        
        // get the public methods
        $class_list = array_unique(array_merge(array($this->mocked_class), $this->interface_names));
        $method_list = $this->locateAllMethods($class_list);

        // listen to all methods. If there are protected methods, and we are inherited
        // listen to those
        foreach ($method_list as $method => $data) {
            $this->listenTo($method);
        }
        

        
        // sanity check. Make sure each logged method we put expectations on
        // is in our public or protected list. If not, setting up this object
        // has failed
        foreach ($this->signatures as $method_name => $signature_data) {
            // skip magic methods
            if (strpos($method_name, '__') === 0) {
                continue;
            }
            
            // if in public, private, or protected we are okay
            if (isset($method_list[$method_name])) {
                $scope = $method_list[$method_name]['scope'];
                if ($scope == 'public' || $scope == 'private' || $scope == 'protected') {
                    continue;
                }
            }
            
            // if magic methods are enabled for this class, we are okay
            // we also need to listen to it
            if ($this->hasMagicMethods()) {
                $this->listenTo($method_name);
                if (!isset($method_list[$method_name])) {
                    // add to the stack
                    $method_list[$method_name] = array(
                        'scope' => 'public',
                        'class' => $this->mocked_class,
                    );
                }
                continue;
            }

            // now we're in trouble. We throw an exception, as they
            // called on something that is not mockable
            throw new Snap_UnitTestException('setup_invalid_method', $this->mocked_class.'::'.$method_name.' cannot have expects or return values. It might be of type "final".');
        }
        
        // if the class exists with everything intact, no need to eval from here on out
        if (class_exists($mock_class)) {
            return $this->buildClassInstantiation($mock_class, $setmock_method_name, $constructor_method_name);
        }
        
        // for each public method found, build it's code block
        // take the func_get_args of it, create a signature via
        // the reflector.  If there's an exact match, add one to
        // it's call count, then try/catch the method, returning
        // it's value
        $p_methods = '';
        foreach ($method_list as $method_name => $data) {
            if ($data['scope'] == 'public' || $data['scope'] == 'protected' || $data['scope'] == 'private') {
                $p_methods .= $this->buildMethod($data['class'], $method_name, $data['scope']);
                continue;
            }
        }
        
        // start building the class
        $endl = "\n";
        $output  = '';
        
        // class header
        $class_header = 'class '.$mock_class;
        if ($this->useExtends()) {
            $class_header .= ' extends '.$this->mocked_class;
        }
        if (count($this->getInterfaces()) > 0) {
            $class_header .= ' implements '.implode(', ', $this->getInterfaces());
        }
        $class_header .= ' {'.$endl;
        
        // attach header to the output
        $output .= $class_header;
        $output .= 'public $mock;'.$endl;
        $output .= 'public static $mock_static;'.$endl;
        
        // special mock setter method
        $output .= 'public function '.$setmock_method_name.'($mock) {'.$endl;
        $output .= '    $this->mock = $mock;'.$endl;
        $output .= '}'.$endl;
        
        // special static mock setter method
        $output .= 'public static function '.$setmock_method_name.'_static($mock) {'.$endl;
        $output .= '    self::$mock_static = $mock;'.$endl;
        $output .= '}'.$endl;

        // add a runConstructor call if this is refection+extension
        if ($this->isInherited() || $this->hasConstructor()) {
            $get_mock = '$this->'.$this->class_signature.'_getMock';
            
            $output .= 'public function '.$constructor_method_name.'() {'.$endl;
            $output .= '    $mock = '.$get_mock.'();'.$endl;
            $output .= '    return $mock->invokeMethod(\'__construct\', $mock->constructor_args);'.$endl;
            $output .= '}'.$endl;
        }
        
        // build the getmock methods
        $output .= 'public static function '.$this->class_signature.'_getMock_static() {'.$endl;
        $output .= '    return self::$mock_static;'.$endl;
        $output .= '}'.$endl;
        
        $output .= 'public function '.$this->class_signature.'_getMock() {'.$endl;
        $output .= '    return $this->mock;'.$endl;
        $output .= '}'.$endl;
        
        // add all public and protected methods
        $output .= $p_methods.$endl;
        
        // ending } for class
        $output .= '}'.$endl;
        
        eval($output);
        $this->mock_output = $output;
        
        // create the ready class
        return $this->buildClassInstantiation($mock_class, $setmock_method_name, $constructor_method_name);
    }
    
    /**
     * invokes a method on the mock object, tallying and intercepting for return values
     * this method is called usually from the mock object itself, asking the mock
     * that created it to provide the return value for the method invocation
     * @param string $method_name the name of the method to invoke
     * @param array $method_params the parameters to pass to the method
     * @return mixed
     **/
    public function invokeMethod($method_name, $method_params) {
        // get all matching signatures
        $sigs = $this->mockFindSignatures($method_name, $method_params);
        $sigs_default = $this->mockFindDefaultSignature($method_name);

        // tally all methods
        foreach ($sigs as $sig) {
            $this->mockTallyMethod($sig);
        }
        $this->mockTallyMethod($sigs_default);
        
        // we've got a lot of possible sigs, do any of them have return values @ call count?
        $returns_at_call_count = array();
        $returns_at_default = array();
        foreach ($sigs as $sig) {
            if (isset($this->methods[$sig]['returns'][$this->mockGetTallyCount($sig)])) {
                $returns_at_call_count[] = $sig;
            }
            if (isset($this->methods[$sig]['returns']['default'])) {
                $returns_at_default[] = $sig;
            }
        }

        // > 1 return is an exception
        if (count($returns_at_call_count) > 1) {
            // error here
            throw new Snap_UnitTestException('setup_ambiguous_return', $this->mocked_class.'::'.$method_name.' has ambiguous return values.');
        }
        
        // exactly one, that's our match
        if (count($returns_at_call_count) == 1) {
            return $returns_at_call_count[0];
        }
        
        // > 1 defaults is an exception
        if (count($returns_at_default) > 1) {
            // error here
            throw new Snap_UnitTestException('setup_ambiguous_return', $this->mocked_class.'::'.$method_name.' has ambiguous default return values.');
        }
        
        // exactly one is a match
        if (count($returns_at_default) == 1) {
            return $this->methods[$returns_at_default[0]]['returns']['default'];
        }
        
        // no specialized returns. Check now, for a call count at the default
        // if that exists, use it
        if (isset($this->methods[$sigs_default]['returns'][$this->mockGetTallyCount($sigs_default)])) {
            return $this->methods[$sigs_default]['returns'][$this->mockGetTallyCount($sigs_default)];
        }

        // no call count default look for a really default
        if (isset($this->methods[$sigs_default]['returns']['default'])) {
            return $this->methods[$sigs_default]['returns']['default'];
        }

        // no default. If it is inherited, fall to original
        if ($this->isInherited()) {
            $method_call = $this->class_signature.'_'.$method_name.'_original';
            if ($this->hasStaticMethods()) {
                if (method_exists(get_class($this->constructed_object), $method_call)) {
                    return call_user_func_array(array(get_class($this->constructed_object), $method_call), $method_params);
                }
            }
            else {
                if (method_exists($this->constructed_object, $method_call)) {
                    return call_user_func_array(array($this->constructed_object, $method_call), $method_params);
                }
            }
        }
        
        return NULL;
    }
    
    /**
     * Record the method name, signature, and expectations
     * @param string $method_name the name of the method to record a signature for
     * @param string $method_signature the signature of the method
     * @param array $method_params the array of expectations that make up this signature
     * @return void
     */
    protected function logMethodSignature($method_name, $method_signature, $method_params) {
        if (!isset($this->signatures[$method_name])) {
            $this->signatures[$method_name] = array();
        }
        
        $this->methods[$method_signature]['count'] = 0;
        $this->methods[$method_signature]['exec_count'] = 0;
        
        $this->signatures[$method_name][$method_signature] = array(
            'params'    => $method_params,
        );
    }
    
    /**
     * Check parameter list, and wrap parameter in a MockObject_Expectation class if necessary
     * @access protected
     * @param array $method_params the method arguments
     * @return array the processed parameter list
     */
    protected function handleMethodParameters($method_params) {
        foreach ($method_params as $idx => $param) {
            if (is_object($param) && ($param instanceof Snap_Expectation)) {
                continue;
            }
        
            if ((substr($param, 0, 1) == '/') && (substr($param, -1, 1) == '/')) {
                $method_params[$idx] = new Snap_Regex_Expectation($param);
            }
            
            $method_params[$idx] = new Snap_Equals_Expectation($param);
        }
        return $method_params;
    }
    
    /**
     * Finds a signature for a method name and param list
     * this is the reverse of the logSignature() method. Instead of recoding
     * the signature, this instead finds a parameter match and then tests
     * every expectation to see if the signature is a match.
     * All matching signatures are returned.
     * @param string $method_name the name of the method to search
     * @param array $method_params an array of parameters that came from the invocation
     * @return array all matching signatures
     **/
    protected function mockFindSignatures($method_name, $method_params = array()) {
        if (!is_array($method_params)) {
            $method_params = array();
        }
        
        if (!isset($this->signatures[$method_name]) || count($method_params) == 0) {
            return array();
        }
    
        $sigs = array();
        foreach ($this->signatures[$method_name] as $signature => $details) {
            $params = $details['params'];
            
            if (count($params) == 0) {
                // default, move on
                continue;
            }
            
            // look at every param submitted versus the params in the signature
            // once we have a non-match, we fail out the test.
            // if we get all the way through then add the signature to our
            // match list
            $param_match = TRUE;
            foreach ($params as $idx => $param) {
                // more params in sig than sent to us, and this wasn't an anything expectation
                if (!in_array($idx, array_keys($method_params)) && is_object($param) && strtolower(get_class($param)) != 'snap_anything_expectation') {
                    $param_match = FALSE;
                    break;
                }
                
                // if this is an anything expectation, and there was no param (optional),
                // make the method param anything we want.
                if (strtolower(get_class($param)) == 'snap_anything_expectation' && !isset($method_params[$idx])) {
                    $method_param = NULL;
                }
                else {
                    $method_param = $method_params[$idx];
                }
                
                // run a match, if it fails, it is a non match
                if (!$param->match($method_param)) {
                    $param_match = FALSE;
                    break;
                }
            }
            
            // if we match, it's good
            if ($param_match) {
                $sigs[] = $signature;
            }
        }
        
        return $sigs;
    }

    /**
     * Finds the default signature for a supplied method name
     * similar in concept to the mockFindSignatures() method, this call
     * instead looks for the default registered signature for a method. This
     * method has 0 parameters in its match stack.
     * The matching signature is returned.
     * @param string $method_name the method name to search
     * @return string the matching signature, or NULL if no method found
     **/
    protected function mockFindDefaultSignature($method_name) {
        if (!isset($this->signatures[$method_name])) {
            return NULL;
        }

        foreach ($this->signatures[$method_name] as $signature => $details) {
            if (count($details['params']) == 0) {
                return $signature;
            }
        }
    }
    
    /**
     * Adds to the tally of a method signature call
     * @param string $method_signature a method signature to tally
     **/
    protected function mockTallyMethod($method_signature) {
        if (!isset($this->methods[$method_signature]['count'])) {
            $this->methods[$method_signature]['count'] = 0;
        }
        $this->methods[$method_signature]['count']++;
    }
    
    /**
     * Gets the tally of a method signature
     * @param string $method_signature the method signature
     * @return int the number of times a signature has been called
     **/
    protected function mockGetTallyCount($method_signature) {
        return (isset($this->methods[$method_signature]['count'])) ? $this->methods[$method_signature]['count'] : 0;
    }
    
    /**
     * Build an output block for a public method
     * calls the invokeMethod call for that public method
     * @param string $class_name the class name to get the method from
     * @param string $method_name the method name to build
     * @param string $scope the scope to build the method in
     * @return string php eval ready output of a method
     */
    protected function buildMethod($class_name, $method_name, $scope) {
        $output  = '';
        $endl = "\n";
        
        // magic method code!
        // if the method doesn't exist, and this class has magic methods, we have to
        // assume this was a magic method.
        // __call, __set, and __get are only available on an instance method
        // and cannot be static
        if (!method_exists($class_name, $method_name) && $this->hasMagicMethods()) {
            $get_mock = '$this->'.$this->class_signature.'_getMock';
            
            $output .= $scope.' function '.$method_name.'() {'.$endl;
            $output .= '    $args = func_get_args();'.$endl;
            $output .= '    return '.$get_mock.'()->invokeMethod(\''.$method_name.'\', $args);'.$endl;
            $output .= '}'.$endl;
            return $output;
        }
        
        // this is considered a normal method, we can use reflection to build it to
        // specification.
        $method = new ReflectionMethod($class_name, $method_name);
        
        // is this a static method
        $is_static = $method->isStatic();
        
        // build the mock call
        $get_mock = (($is_static) ? 'self::'.$this->class_signature : '$this->'.$this->class_signature).'_getMock'.(($is_static) ? '_static' : '');

        // build a param string
        $param_string = ''; // with defaults
        $original_param_string = ''; // without
        foreach ($method->getParameters() as $i => $param) {
            $default_value = ($param->isOptional()) ? '=' . var_export($param->getDefaultValue(), TRUE) : '';
            $type = ($param->getClass()) ? $param->getClass()->getName().' ' : '';

            $ref = ($param->isPassedByReference()) ? '&' : '';

            $param_string .= $type . $ref . '$'.$param->getName().$default_value.',';
            $original_param_string .= $type . $ref . '$'.$param->getName().',';
        }
        $param_string = trim($param_string, ',');
        $original_param_string = trim($original_param_string, ',');
        
        // build the output for a normal object, if it isn't a constructor
        // if it is a constructor, use a special global for setting it
        $output .= $scope.(($is_static) ? ' static' : '').' function '.$method_name.'('.$param_string.') {'.$endl;
        if (!$method->isConstructor() && strtolower($method->getName()) != '__construct') {
            $output .= '    $args = func_get_args();'.$endl;
            $output .= '    return '.$get_mock.'()->invokeMethod(\''.$method_name.'\', $args);'.$endl;
        }
        else {
            // constructor takes the mock in question and loads it
            $output .= '    global $SNAP_MockObject;'.$endl;
            $output .= '    $this->mock = $SNAP_MockObject;'.$endl;
            $output .= '    self::$mock_static = $SNAP_MockObject;'.$endl;
        }
        $output .= '}'.$endl;
        
        // if this is static, AND we need the original methods, copy them
        // please replace with late static bindings once PHP 5.3 becomes
        // a baseline
        if ($this->isInherited()) {            
            if ($is_static) {
                $output .= 'public static function '.$this->class_signature.'_'.$method_name.'_original('.$param_string.') {'.$endl;
                $output .= $this->extractMethodBody($method).$endl;
                $output .= '}'.$endl;
            }
            else {
                $output .= 'public function '.$this->class_signature.'_'.$method_name.'_original('.$param_string.') {'.$endl;
                $output .= '    return parent::'.$method_name.'('.$original_param_string.');'.$endl;
                $output .= '}'.$endl;
            }
        }
        
        return $output;
    }
    
    /**
     * A utility method to extract the method body from a ReflectionMethod
     * @param ReflectionMethod $method the method to copy
     * @return string the method's contents re-scoped
     **/
    protected function extractMethodBody($method) {
        $contents = file($method->getFileName());
        $method_name = $method->getName();
        $start_line = $method->getStartLine();
        $end_line = $method->getEndLine();
        $contents = implode("\n", array_slice($contents, $start_line - 1, $end_line - $start_line + 1));
    
        $matches = array();
        preg_match('/.*?function[\s]+'.$method_name.'.*?\{([\s\S]*)\}/i', $contents, $matches);
    
        // no matches, this was an interface
        if (!is_array($matches) || !isset($matches[1])) {
            $matches = array('1' => '');
        }
    
        // map self:: and parent:: to proper things
        $replaces = array(
            // self is implied, since it's in the new class, it resolves correctly
            'parent::' => get_parent_class($this->mocked_class).'::',
        );
    
        $contents = trim(str_replace(array_keys($replaces), array_values($replaces), $matches[1]));
        
        return $contents;
    }
    
    /**
     * Generates a unique class name based on the method signatures supplied.
     * Uses a basic md5() with class_exists checks to create a unique class
     * name for every instance of an object. It additionally attaches
     * "helper" tags to the end (_ri _if) to help with debugging purposes.
     * @return string the class name for this mock object
     **/
    protected function generateClassName() {
        $keys = array_keys($this->methods);
        sort($keys);
        $this->class_signature = 'c'.md5(strtolower(serialize($keys)));
        
        $mock_class = 'mock_'.$this->mocked_class.'_'.$this->class_signature;
        
        // add suffixes if there is inheritance / interface
        if ($this->isInherited()) {
            $mock_class .= '_ri';
        }
        if (count($this->getInterfaces()) > 0) {
            $mock_class .= '_if';
        }
        
        // add iterations until we get a unique name for mock_class
        $mock_class_test = $mock_class;
        $class_counter = 1;
        while (class_exists($mock_class_test)) {
            $mock_class_test = $mock_class . '_' . $class_counter;
            $class_counter++;
        }
        
        return $mock_class_test;
    }
    
    /**
     * Finds all methods for all supplied classes, favoring their concrete location
     * this loops through all methods in all classes, building out a structured
     * array of class / method / scope.
     * In order to ensure the most concrete class is used, interface references
     * are replaced by the mock_class name's instances.
     * @param array $class_list an array of classes
     * @return array a multi-dimensional array of methods and their properties
     **/
    protected function locateAllMethods($class_list) {
        $methods = array();

        foreach ($class_list as $class_name) {
            // reflect the class
            $reflected_class = new ReflectionClass($class_name);
            foreach ($reflected_class->getMethods() as $method) {
                
                // if we have already set this method, and the current class is an interface
                // do not process this method
                if (isset($methods[$method->getName()]) && $reflected_class->isInterface()) {
                    continue;
                }
                
                if ($method->isConstructor() || strtolower($method->getName()) == '__construct') {
                    // special constructor stuff here
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'public',
                    );
                    $this->requiresConstructor();
                    continue;
                }

                // __call magic method
                if (strtolower($method->getName()) == '__call') {
                    $this->requiresMagicMethods();
                }

                // skip all other magic methods
                if (strpos($method->getName(), '__') === 0) {
                    continue;
                }
            
                // skip all final methods
                if ($method->isFinal()) {
                    // cannot be overridden
                    continue;
                }
            
                // if static methods are required, add a flag
                if ($method->isStatic()) {
                    $this->requiresStaticMethods();
                }
            
                if ($method->isPublic()) {
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'public',
                    );
                }
                if ($method->isProtected()) {
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'protected',
                    );
                }
                if ($method->isPrivate()) {
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'private',
                    );
                }
            }
        }

        return $methods;
    }
    
    /**
     * Builds and instantiates a named mock class
     * In addition to instantiating the mock class, it injects the mock object
     * and runs the constructor if required
     * @return Object the mocked object, ready for use
     * @param string $mock_class the mock class name
     * @param string $setmock_method the method to call for setting the mock object
     * @param string $constructor_method the constructor to call if required
     **/
    protected function buildClassInstantiation($mock_class, $setmock_method, $constructor_method) {
        global $SNAP_MockObject;
        $SNAP_MockObject = $this;
        $setmock_method_static = $setmock_method . '_static';
        
        // make the arguments for the ready class
        $ready_class = '';
        if (count($this->constructor_args) > 0) {
            $arg_output = "";
            
            foreach ($this->constructor_args as $idx => $arg) {
                $arg_output .= '$this->constructor_args['.$idx.'],';
            }
            $arg_output = trim($arg_output, ',');
            
            $ready_class = 'return new '.$mock_class.'('.$arg_output.');';
        }
        else {
            $ready_class = 'return new '.$mock_class.'();';
        }

        $ready_class = eval($ready_class);
        
        // inject the mock class
        $ready_class->$setmock_method($this);
        
        if ($this->hasStaticMethods()) {
            call_user_func_array(array(get_class($ready_class), $setmock_method_static), array($this));
        }
        
        $this->constructed_object = $ready_class;

        // call a real constructor if required
        if ($this->isInherited() || $this->hasConstructor()) {
            $ready_class->$constructor_method();        
        }
        
        // clean up that global
        unset($SNAP_MockObject);
        
        // return the ready class
        return $ready_class;
    }
    
}


