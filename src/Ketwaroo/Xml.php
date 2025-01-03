<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ketwaroo;

/**
 * Description of Xml
 *
 * @author Yaasir Ketwaroo
 */
class Xml extends \SimpleDOM {

    /**
     * 
     * @param int $level
     * @return static|boolean
     */
    public function getParentNode(int $level = 1): static|bool {
        $test = $this->xpath(trim(str_repeat('/..', $level), '/'));

        return empty($test) ? false : $test[0];

    }

    /**
     * 
     * @param string $xpath
     * @param int $level
     * @param bool $flatten
     * @return static[];
     */
    public function findParentOfX(string $xpath, int $level = 1, bool $flatten = false): array {
        $found = [];
        foreach (($this->rootPart->xpath($xpath)) as $x) {
            if (false !== ( $test = $x->getParentNode($level))) {
                $found[] = $flatten ? $test->flatten() : $test;
            }
        }

        return $found;

    }

    public function appendNode(Xml $node, ?string $namespace = null): static {
        /* @var $c Xml */
        $c = $this->addChild($node->getName(), null, $namespace);
        foreach ($node->getAttributes() as $k => $v) {
            $c->addAttribute($k, $v, $namespace);
        }

        $c->insertXML($node->innerXML());

        return $this;

    }

    public function setAttribute(string $name, $value, ?string $namespace = null): static {
        if (isset($this[$name])) {
            $this[$name] = $value;
        }
        else {
            $this->addAttribute($name, $value);
        }
        return $this;

    }

    /**
     * Get all attributes of current element as associative array.
     * @return array 
     */
    public function getAttributes(?string $namespaceOrPrefix = null, bool $isPrefix = false): array {
        $tmp = (array) $this->attributes($namespaceOrPrefix, $isPrefix);
        return empty($tmp['@attributes']) ? [] : $tmp['@attributes'];

    }

    /**
     * 
     * @param atring|array $paths list of paths
     * @return array
     */
    public function matchXPathFirst(string|array $paths): array {

        foreach ((array) $paths as $path) {
            $test = $this->xpath($path);

            if (empty($test)) {
                continue;
            }

            return $test; // return first non empty
        }


        return [];

    }

    /**
     * must match all supplied xpaths.
     * 
     * @param array|string $paths
     * @return array
     */
    public function matchXPathAll(array|string $paths): array {
        $path = '(' . implode(') and (', (array) $paths) . ')';

        return $this->xpath($path);

    }

    /**
     * randomly picks one or more matched items.
     * Warning; will return single instance if num entires = 1 and array of entries if greater.
     * 
     * @param string|array $path
     * @param int $numEntries number of entries to pick
     * @return Xml|array
     */
    public function matchXPathRandom(string $path, int $numEntries = 1): static {
        $nodes = $this->matchXPathAll($path);

        return array_rand($nodes, $numEntries);

    }

    /**
     * If current node was matched by xpath, this function flattens it and removes
     * references to the parent xml document so further xpath can per performed
     * without matching parent elements
     * 
     * @return SimplerDOM
     */
    public function flatten(): static {
        return new static($this->asXML());

    }

    /**
     * 
     * @param string $filename
     * @param int $options
     * @param string $ns
     * @param boolean $is_prefix
     * @return Xml
     */
    public static function loadXmlFile($filename, $options = 0, $ns = '', $is_prefix = false): static {
        return new static(file_get_contents($filename), $options, $ns, $is_prefix);

    }

    /**
     * Add new CDATA child and return new node.
     * @param string $name
     * @param string $value
     * @param string $namespace
     * @return static
     */
    public function addChildCdata($name, $value = null, $namespace = null): static {
        /* @var $c Xml */
        $c = $this->addChild($name, NULL, $namespace);
        $c->insertCDATA(strval($value));
        return $c;

    }

    public function asText(): string {
        return strip_tags($this->asXML());

    }

    /**
     * Converts to sort of an array representation
     * @return array
     */
    public function toArray(bool $includeAttributes = true): array {
        $out     = [];
        if ($includeAttributes
                && ($attribs = $this->getAttributes())
                && !empty($attribs)) {

            $out['@attributes'] = $attribs;
        }
        if ($this->count()) {
            foreach ($this->children() as $c) {
                $out[$c->getName()][] = $c->toArray();
            }
        }
        else {
            $out[] = strval($this);
        }
        return $out;

    }
}
