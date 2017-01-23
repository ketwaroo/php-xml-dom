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
class Xml extends \SimpleDOM
{

    /**
     * 
     * @param int $level
     * @return static|boolean
     */
    public function getParentNode($level = 1)
    {
        $test = $this->xpath(trim(str_repeat('/..', $level), '/'));

        return empty($test) ? false : $test[0];
    }

    /**
     * 
     * @param string $xpath
     * @param integer $level
     * @param boolean $flatten
     * @return static[];
     */
    public function findParentOfX($xpath, $level = 1, $flatten = false)
    {
        $found = [];
        foreach (($this->rootPart->xpath($xpath)) as $x)
        {
            if (false !== ( $test = $x->getParentNode($level)))
            {
                $found[] = $flatten ? $test->flatten() : $test;
            }
        }

        return $found;
    }

    public function appendNode(Xml $node, $namespace = null)
    {
        /* @var $c Xml */
        $c = $this->addChild($node->getName(), null, $namespace);
        foreach ($node->getAttributes() as $k => $v)
        {
            $c->addAttribute($k, $v, $namespace);
        }

        $c->insertXML($node->innerXML());



        return $this;
    }

    public function setAttribute($name, $value, $namespace = null)
    {
        if (isset($this[$name]))
        {
            $this[$name] = $value;
        }
        else
        {
            $this->addAttribute($name, $value);
        }
        return $this;
    }

    /**
     * Get all attributes of current element as associative array.
     * @return array 
     */
    public function getAttributes(?string $namespaceOrPrefix=null, bool $isPrefix=false)
    {
        $tmp = (array) $this->attributes($namespaceOrPrefix, $isPrefix);
        return empty($tmp['@attributes']) ? array() : $tmp['@attributes'];
    }

    /**
     * 
     * @param type $paths
     * @return type
     */
    public function matchXPathFirst($paths)
    {

        foreach ((array) $paths as $path)
        {
            $test = $this->xpath($path);

            if (empty($test))
            {
                continue;
            }

            return $test; // return first non empty
        }


        return array();
    }

    public function matchXPathAll($paths)
    {
        $path = '(' . implode(') and (', (array) $paths) . ')';

        return $this->xpath($path);
    }

    /**
     * randomly picks one or more matched items.
     * Warning; will return single instance if num entires = 1 and array of entries if greater.
     * 
     * @param string|array $path
     * @param int $numEntries number of entries to pick
     * @return SimplerDOM|array
     */
    public function matchXPathRandom($path, $numEntries = 1)
    {
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
    public function flatten()
    {
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
    public static function loadXmlFile($filename, $options = 0, $ns = '', $is_prefix = false)
    {
        return new static(file_get_contents($filename), $options, $ns, $is_prefix);
    }

    /**
     * Add new CDATA child and return new node.
     * @param string $name
     * @param string $value
     * @param string $namespace
     * @return static
     */
    public function addChildCdata($name, $value=null, $namespace=null)
    {
        /* @var $c Xml */
        $c = $this->addChild($name, NULL, $namespace);
        $c->insertCDATA(strval($value));
        return $c;
    }

    /**
     * Converts to sort of an array representation
     * @return array
     */
    public function toArray($includeAttributes = true)
    {
        $out     = [];
        if ($includeAttributes
            && ($attribs = $this->getAttributes())
            && !empty($attribs))
        {

            $out['@attributes'] = $attribs;
        }
        if ($this->count())
        {
            foreach ($this->children() as $c)
            {
                $out[$c->getName()][] = $c->toArray();
            }
        }
        else
        {
            $out[] = strval($this);
        }
        return $out;
    }

}
