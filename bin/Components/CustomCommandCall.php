<?php


namespace Bin\Components;


use RuntimeException;

/**
 * Class CustomCommandCall
 * @package Bin\Components
 */
abstract class  CustomCommandCall
{

    /**
     * @var array $signals
     */
    private array $signals;


    /**
     * CustomCommandCall constructor.
     */
    public function __construct()
    {
        if (!empty(func_get_args())) {
            $this->parseParameters(func_get_args());
        }

    }

    /**
     * @param $parameter
     */
    private function parseParameters($parameter): void
    {

        [$params, $count] = $parameter;

        if ($count < 3) {
            throw  new RuntimeException('too few parameters !!!');
        }
        $this->signals = array_slice($params, 1);
    }

    /**
     * @return mixed
     *
     */
    final public function run(): void
    {
        $commandClass = $this->getCommands($this->signals[0]);
        if (class_exists($commandClass)) {
            (new $commandClass())->handle(array_slice($this->signals, 1));
        } else {
            echo 'Command Not found';
        }

    }

    /**
     * @param null $key
     * @return mixed
     */
    private function getCommands($key = null)
    {
        if ($key === null) {
            return $this->commands;
        }
        return $this->commands[$key] ?? null;
    }


}