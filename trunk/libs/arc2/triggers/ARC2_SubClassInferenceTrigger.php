<?php

ARC2::inc('Class');

class ARC2_SubClassInferenceTrigger extends ARC2_Class {

  function __construct($a = '', &$caller) {/* caller is a store */
    parent::__construct($a, $caller);
  }
  
  function ARC2_SubClassInferenceTrigger($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->store = $this->caller;
  }

  function go() { /* automatically called by store or endpoint */
    $a = $this->a;
    $graph = $a['query_infos']['query']['target_graphs'][0];

	// BROWSER_GRAPH is set in startup.php
	// inferencing is only used in BROWSER_GRAPH
	if($graph == BROWSER_GRAPH) {
    	$q = "
    	INSERT INTO <$graph> CONSTRUCT {
    	    ?s ?top ?o .
    	} WHERE {
    	    GRAPH <$graph> {
    	        ?s ?prop ?o .
    	    } 
    	    ?prop rdfs:subClassOf ?top .
    	}";
		$this->store->query($q);
	}
  }

} 
