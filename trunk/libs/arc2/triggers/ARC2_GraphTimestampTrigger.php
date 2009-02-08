<?php

ARC2::inc('Class');

class ARC2_GraphTimestampTrigger extends ARC2_Class {

  function __construct($a = '', &$caller) {/* caller is a store */
    parent::__construct($a, $caller);
  }
  
  function ARC2_GraphTimestampTrigger($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {
    parent::__init();
    $this->store = $this->caller;
  }

  function go() { /* automatically called by store or endpoint */
    $a = $this->a;
	if(isset($a['query_infos']['query']['target_graphs']))
    	$graph = $a['query_infos']['query']['target_graphs'][0];
	else if(isset($a['query_infos']['query']['target_graph']))
		$graph = $a['query_infos']['query']['target_graph'];

	// IDENTITY_GRAPH is set in startup.php
	// inferencing is only used in BROWSER_GRAPH
	if($graph == IDENTITY_GRAPH) {
		$now = date('c');
		$prefix = "PREFIX dct: <http://purl.org/dc/terms/> .";

		// delete old triple
		$q = $prefix . "DELETE FROM <$graph> { <$graph> dct:created ?date . }";
    	$this->store->query($q);

		// insert new triple
    	$q = $prefix . "INSERT INTO <$graph> { <$graph> dct:created '$now' . }";
    	$this->store->query($q);
	}
  }

} 
