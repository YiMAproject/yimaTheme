<?php
namespace yimaTheme\Resolvers;

use Countable;
use IteratorAggregate;
use Zend\Stdlib\PriorityQueue;

class Aggregate implements
    ResolverInterface,
	Countable,
	IteratorAggregate
{
	/**
	 * @var PriorityQueue
	 */
	protected $queue;
	
	/**
	 * Last Detector found in Quee
	 * 
	 * @var ResolverInterface
	 */
	protected $lastStrategyFound;

	/**
	 * Constructor
	 * - Instantiate the internal priority queue
	 */
	public function __construct()
	{
		$this->queue = new PriorityQueue();
	}

    /**
	 * Attain To Name based on strategy found in class
	 * 
	 * @return string|false
	 */
	public function getName()
	{
		if (0 === count($this->queue)) {
			return false;
		}

		foreach ($this->queue as $detector) {
			$name = $detector->getName();
			if (empty($name) && $name !== '0') {
				// No resource found; try next resolver
				continue;
			}

			// Resource found; return it
			$this->lastStrategyFound = $detector;
			return $name;
		}

		return false;
	}
	
	// Inside Class Usage ..........................................................................................

	/**
	 * Return count of attached resolvers
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->queue->count();
	}
	
	/**
	 * IteratorAggregate: return internal iterator
	 *
	 * @return PriorityQueue
	 */
	public function getIterator()
	{
		return $this->queue;
	}
	
	/**
	 * Attach a name resolver strategy
	 *
	 */
	public function attach(ResolverInterface $detector, $priority = 1)
	{
		$this->queue->insert($detector, $priority);
		return $this;
	}
        
    public function dettach(ResolverInterface $detector)
    {
        $this->queue->remove($detector);

        return $this;
    }
        
    public function dettachAll()
    {
        foreach($this->queue as $detector) {
            $this->dettach($detector);
        }
            
        return $this;
    }

	public function getLastStrategyFound()
	{
		return $this->lastStrategyFound;
	}
}