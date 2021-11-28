<?php

namespace Laurentvw\Scrapher;

use Laurentvw\Scrapher\Selectors\Selector;

class Matcher
{
    /**
     * @var array
     */
    protected $matches = array();

    /**
     * Logs.
     *
     * @var array
     */
    protected $logs = array();

    /**
     * @var Selector
     */
    protected $selector = null;

    /**
     * @var \Closure
     */
    protected $filter = null;

    /**
     * Create a new Matcher instance.
     *
     * @param Selector $selector
     * @param \Closure $filter
     */
    public function __construct(Selector $selector, $filter = null)
    {
        $this->setSelector($selector);
        $this->setFilter($filter);
    }

    /**
     * Set the selector.
     *
     * @param Selector $selector
     *
     * @return Matcher
     */
    public function setSelector(Selector $selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * Get the selector.
     *
     * @return Selector
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * Set the filter to be applied to the matches.
     *
     * @param \Closure $filter
     *
     * @return Matcher
     */
    public function setFilter($filter = null)
    {
        $this->filter = is_callable($filter) ? $filter : null;

        return $this;
    }

    /**
     * Get detailed logs of the scraping.
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add a log message.
     *
     * @param string $msg
     */
    public function addLog($msg)
    {
        $this->logs[] = $msg;
    }

    /**
     * @param $content
     *
     * @param $sourceKey
     * @return array
     */
    public function getMatches($content, $sourceKey)
    {
        $filteredResults = array();

        $this->getSelector()->setContent($content);
        $this->getSelector()->setSourceKey($sourceKey);

        $matches = $this->getSelector()->getMatches();

        if ($matches) {
            foreach ($matches as $matchLine) {
                $filteredResult = $this->fetch($matchLine);

                if ($filteredResult) {
                    $filteredResults[] = $filteredResult;
                }
            }
        } else {
            $this->addLog('The HTML or Selector expression is broken');
        }

        return $filteredResults;
    }

    /**
     * Fetch the values from a match.
     *
     * @param array $matchLine
     *
     * @return array
     */
    private function fetch(array $matchLine)
    {
        $result = array();

        foreach ($this->getSelector()->getConfig() as $match) {
            // Get the match value, optionally apply a function to it
            if (isset($match['apply'])) {
                $result[$match['name']] = $match['apply']($matchLine[$match['name']], $this->getSelector()->getSourceKey());
            } else {
                $result[$match['name']] = $matchLine[$match['name']];
            }

            // Validate this match
            if (isset($match['validate'])) {
                if (!$match['validate']($result[$match['name']], $this->getSelector()->getSourceKey())) {
                    $this->addLog('Skipping match because validation failed for '.$match['name'].': '.$result[$match['name']]);

                    return false;
                }
            }
        }

        // Filter the data
        if ($this->filter && !call_user_func($this->filter, $result)) {
            $this->addLog('Filtering out match: '.var_export($result, true));

            return false;
        }

        return $result;
    }
}
