<?php

  namespace Xparse\RecursivePagination;


  use Fiv\Parser\Grabber;

  /**
   *
   * @package Xparse\RecursivePagination
   */
  class RecursivePagination {

    /**
     * @var array
     */
    protected $queue = [];

    /**
     * @var Grabber|null
     */
    protected $grabber = null;

    /**
     * @var array
     */
    protected $defaultXpath = [];


    /**
     * @param Grabber $grabber
     * @param array $xpath
     * @throws \Exception
     */
    public function __construct(Grabber $grabber, $xpath = []) {
      $this->grabber = $grabber;
      if (!is_string($xpath) && !is_array($xpath)) {
        throw new \Exception('xPath should be an array or a string');
      }
      $xpath = (array) $xpath;
      foreach ($xpath as $path) {
        if (!is_string($path)) {
          throw new \Exception('Incorrect xPath, should be an array or a string');
        }
        $this->defaultXpath[] = $path;
      }
    }


    /**
     * @param $link
     * @param bool $state
     * @return $this
     */
    public function addToQueue($link, $state = false) {
      $this->queue[$link] = $state;
      return $this;
    }


    /**
     * @return \Fiv\Parser\Dom\ElementFinder|null
     */
    public function getNextPage() {
      $page = $this->grabber->getLastPage();
      if (!empty($page)) {
        foreach ($this->defaultXpath as $xPath) {
          $queueLinks = $page->attribute($xPath)->getItems();

          if (!empty($queueLinks)) {
            $queueLinks = array_combine($queueLinks, array_fill(0, count($queueLinks), false));
            $this->queue = array_merge($queueLinks, $this->queue);
          }
        }
      }

      $link = array_search(false, $this->queue, true);

      if (empty($link)) {
        return null;
      }

      $this->queue[$link] = true;
      return $this->grabber->getHtml($link);
    }
  }