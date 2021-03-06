<?php namespace Mistletoe\Runners;
use Mistletoe\Command;
use Mistletoe\Contracts\TaskRunnerInterface;
use Mistletoe\Contracts\RunnableInterface;
use Mistletoe\TaskBag;

/**
 * Class Generic
 * @package Mistletoe\Application\Commands
 */

class GenericTaskRunner extends AbstractTaskRunner implements TaskRunnerInterface
{
    /**
     * 
     * This is NOT done in a try/catch block. This should be RUN inside a try/catch block!
     * @param array $tasks
     * @return mixed
     */
    protected function executeTasks(array $tasks)
    {
        if ($this->testing) {
            return $tasks;
        }

        $processes = [];

        /** @var TaskBag $task */
        foreach ($tasks as $task) {
            $processes[] = $task->getTask();
            $processes = array_merge($processes, $task->getFollowedBy());
        }
        
        foreach ($processes as $process) {
            // Create a Process instance
            if (is_string($process) && class_exists($process)) {
                $process = new $process();
            }

            // Try to Run that Process
            if (is_callable($process)) {
                $process();

            } elseif ($process instanceof Command) {
                exec($process->getCommand());

            } elseif ($process instanceof RunnableInterface) {
                /** @var \Mistletoe\Contracts\RunnableInterface $obj */
                $obj = new $process();
                $obj->run();
            }
        }
        
        return true;
    }
}
