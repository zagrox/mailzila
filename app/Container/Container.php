<?php

namespace App\Container;

class Container {
    private $bindings = [];
    private $instances = [];
    
    public function bind($abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = $concrete;
    }
    
    public function singleton($abstract, $concrete = null) {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        
        $this->bind($abstract, function() use ($concrete) {
            if (!isset($this->instances[$concrete])) {
                $this->instances[$concrete] = $this->make($concrete);
            }
            return $this->instances[$concrete];
        });
    }
    
    public function make($abstract) {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        if (!isset($this->bindings[$abstract])) {
            return $this->build($abstract);
        }
        
        $concrete = $this->bindings[$abstract];
        
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }
        
        return $this->build($concrete);
    }
    
    private function build($concrete) {
        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\ReflectionException $e) {
            throw new \Exception("Target class [$concrete] does not exist.");
        }
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }
        
        $constructor = $reflector->getConstructor();
        
        if (is_null($constructor)) {
            return new $concrete;
        }
        
        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies);
        
        return $reflector->newInstanceArgs($instances);
    }
    
    private function resolveDependencies(array $dependencies) {
        $results = [];
        
        foreach ($dependencies as $dependency) {
            $results[] = $this->resolveDependency($dependency);
        }
        
        return $results;
    }
    
    private function resolveDependency($parameter) {
        $class = $parameter->getClass();
        
        if ($class) {
            return $this->make($class->name);
        }
        
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        
        throw new \Exception("Unresolvable dependency \${$parameter->name}");
    }
    
    public function has($abstract) {
        return isset($this->bindings[$abstract]);
    }
    
    public function forget($abstract) {
        unset($this->bindings[$abstract], $this->instances[$abstract]);
    }
    
    public function flush() {
        $this->bindings = [];
        $this->instances = [];
    }
} 