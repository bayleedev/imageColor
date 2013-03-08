<?php

namespace imageColor\core;

/**
 * Base Object class
 */
abstract class Object
{
    /**
     * Holds an array of values that should be processed on initialization.
     *
     * @var array
     */
    protected $_autoConfig = array();

    /**
     * Constructor calls the auto configuration.
     *
     * @param array $options Overwrite the default values listed in autoConfig.
     */
    public function __construct(array $options = array())
    {
        $this->autoConfig($options);
    }

    /**
     * Will update the configurable options.
     *
     * @param array $options Overwrite the default values listed in autoConfig.
     * @return void
     */
    public function autoConfig($options)
    {
        foreach ($this->_autoConfig as $flag) {
            if (isset($options[$flag])) {
                $this->{"_$flag"} = $options[$flag];
            }
        }

        return;
    }

}
