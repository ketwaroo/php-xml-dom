<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ketwaroo;

/**
 * Description of Dom
 *
 * @author Yaasir Ketwaroo
 */
class Dom extends \DOMDocument
{
    /**
     *
     * @var \DOMXPath
     */
    protected $domXPath;
    /**
     * performs xpath query on document.
     * 
     * @param string $path
     * @param \DOMNode $context
     * @return \DOMNode[]
     */
    public function xpath($path, \DOMNode $context=null)
    {
 
        $return = [];
        
        $result = $this->getDomXPath()
            ->query($path, $context);
        
        if (empty($result) || 0 === $result->length)
        {
            return $return;
        }

        foreach ($result as $r)
        {
            $return[] = $r;
        }
        return $return;
    }
    
    /**
     * 
     * @return \DOMXPath
     */
    public function getDomXPath()
    {
        if(!($this->domXPath instanceof \DOMXPath)){
            $this->domXPath = new \DOMXPath($this);
        }
        return $this->domXPath;
    }

}
